<?php

namespace App\Http\Controllers;

use App\Models\TikTokShopIntegration;
use App\Models\TikTokShop;
use App\Models\TikTokShopSeller;
use App\Models\User;
use App\Services\TikTokShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class TeamTikTokShopController extends Controller
{
    protected $tikTokShopService;

    public function __construct(TikTokShopService $tikTokShopService)
    {
        $this->tikTokShopService = $tikTokShopService;
    }

    /**
     * Display TikTok Shop connection page for team admin
     */
    public function index()
    {
        $team = Auth::user()->team;
        $integrations = TikTokShopIntegration::where('team_id', $team->id)->with('shops.sellers.user')->get();
        $shops = collect();

        // Get all shops from all integrations with integration relationship
        foreach ($integrations as $integration) {
            $integrationShops = $integration->shops;
            // Load integration relationship for each shop
            foreach ($integrationShops as $shop) {
                $shop->setRelation('integration', $integration);
            }
            $shops = $shops->merge($integrationShops);
        }

        // Get team members with seller role for assignment (will be filtered per shop by market in view)
        $teamMembers = User::where('team_id', $team->id)
            ->where('is_system_user', false)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'seller');
            })
            ->with('tiktokMarkets')
            ->get();

        return view('team.tiktok-shop.index', compact('integrations', 'team', 'shops', 'teamMembers'));
    }

    /**
     * Show create integration form
     */
    public function createIntegration()
    {
        $team = Auth::user()->team;
        return view('team.tiktok-shop.create-integration', compact('team'));
    }

    /**
     * Store new integration
     */
    public function storeIntegration(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'market' => 'required|in:US,UK',
        ]);

        $team = Auth::user()->team;

        try {
            TikTokShopIntegration::create([
                'team_id' => $team->id,
                'name' => $request->name,
                'market' => $request->market,
                'status' => 'pending',
            ]);

            return redirect()->route('team.tiktok-shop.index')
                ->with('success', 'Tạo tích hợp TikTok Shop "' . $request->name . '" thành công! Bây giờ bạn có thể kết nối.');
        } catch (Exception $e) {
            Log::error('Create Integration Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Start OAuth authorization process
     */
    public function connect($integration_id)
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $integration_id)
            ->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        if ($integration->status !== 'pending') {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Tích hợp TikTok Shop đã được kết nối hoặc có lỗi.');
        }

        try {
            $authUrl = $integration->getAuthorizationUrl();
            return redirect($authUrl);
        } catch (Exception $e) {
            Log::error('Team TikTok Shop Authorization Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle OAuth callback for team admin
     */
    public function callback(Request $request)
    {
        try {
            $code = $request->get('code');
            $state = $request->get('state');
            $error = $request->get('error');

            if ($error) {
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Ủy quyền bị từ chối: ' . $error);
            }

            if (!$code || !$state) {
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Thiếu thông tin ủy quyền.');
            }

            // Decode state - có thể là team_id trực tiếp hoặc JSON encoded
            $teamId = null;
            if (is_numeric($state)) {
                // State là team_id trực tiếp
                $teamId = (int)$state;
            } else {
                // State là JSON encoded
                try {
                    $stateData = json_decode(base64_decode($state), true);
                    if ($stateData && isset($stateData['team_id'])) {
                        $teamId = $stateData['team_id'];
                    }
                } catch (\Exception $e) {
                    Log::error('Invalid state parameter in callback: ' . $e->getMessage());
                }
            }

            if (!$teamId) {
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Thông tin state không hợp lệ.');
            }

            $team = Auth::user()->team;
            if ($teamId != $team->id) {
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Không có quyền truy cập tích hợp này.');
            }

            $integration = TikTokShopIntegration::where('team_id', $team->id)->first();
            if (!$integration) {
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
            }

            // Get access token
            $result = $this->tikTokShopService->getAccessToken($integration, $code);

            if ($result['success']) {
                $integration->updateTokens($result['data']);

                // Get authorized shops
                $shopsResult = $this->tikTokShopService->getAuthorizedShops($integration);

                if ($shopsResult['success'] && isset($shopsResult['data']['shop_list'])) {
                    Log::info('Processing shop list from TikTok API', [
                        'total_shops' => count($shopsResult['data']['shop_list']),
                        'sample_shop' => $shopsResult['data']['shop_list'][0] ?? null
                    ]);

                    foreach ($shopsResult['data']['shop_list'] as $shopData) {
                        Log::info('Processing individual shop', [
                            'shop_id' => $shopData['shop_id'] ?? 'unknown',
                            'cipher' => $shopData['cipher'] ?? 'not_found',
                            'shop_cipher' => $shopData['shop_cipher'] ?? 'not_found',
                            'region' => $shopData['region'] ?? 'unknown',
                            'seller_type' => $shopData['seller_type'] ?? 'unknown'
                        ]);
                        TikTokShop::updateOrCreate(
                            [
                                'shop_id' => $shopData['shop_id'],
                                'tiktok_shop_integration_id' => $integration->id,
                            ],
                            [
                                'team_id' => $team->id,
                                'shop_name' => $shopData['shop_name'] ?? 'Unknown Shop',
                                'seller_name' => $shopData['seller_name'] ?? 'Unknown Seller',
                                'seller_region' => $shopData['seller_region'] ?? 'Unknown',
                                'open_id' => $shopData['open_id'] ?? '',
                                'status' => 'active',
                                'shop_data' => $shopData,
                            ]
                        );
                    }
                }

                return redirect()->route('team.tiktok-shop.index')
                    ->with('success', 'Kết nối TikTok Shop thành công! Các shop đã được đồng bộ.');
            } else {
                $integration->markAsError($result['error']);

                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Lỗi ủy quyền: ' . $result['error']);
            }
        } catch (Exception $e) {
            Log::error('Team TikTok Shop Callback Error: ' . $e->getMessage());
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Có lỗi xảy ra trong quá trình ủy quyền: ' . $e->getMessage());
        }
    }

    /**
     * Handle manual authorization code input
     */
    public function processAuthCode(Request $request)
    {
        Log::info('=== START PROCESSING AUTH CODE ===');
        Log::info('Request data:', $request->only(['auth_code']));

        $request->validate([
            'auth_code' => 'required|string|min:10',
            'integration_id' => 'required|exists:tiktok_shop_integrations,id',
        ]);

        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $request->integration_id)
            ->first();

        Log::info('Team info:', [
            'team_id' => $team->id,
            'team_name' => $team->name
        ]);

        if (!$integration) {
            Log::error('No TikTok Shop integration found for team');
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        Log::info('Integration found:', [
            'integration_id' => $integration->id,
            'app_key' => $integration->getAppKey(),
            'status' => $integration->status
        ]);

        if ($integration->status !== 'pending') {
            Log::warning('Integration status is not pending', ['status' => $integration->status]);
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Tích hợp TikTok Shop đã được kết nối hoặc có lỗi.');
        }

        try {
            Log::info('Calling TikTokShopService->getAccessToken');

            // Get access token using the provided auth code
            $result = $this->tikTokShopService->getAccessToken($integration, $request->auth_code);

            Log::info('getAccessToken result:', [
                'success' => $result['success'],
                'error' => $result['error'] ?? null,
                'has_data' => isset($result['data'])
            ]);

            if ($result['success']) {
                Log::info('Access token obtained successfully, updating tokens');

                $integration->updateTokens($result['data']);

                Log::info('Tokens updated, calling getAuthorizedShops');

                // Get authorized shops using new API
                $shopsResult = $this->tikTokShopService->getAuthorizedShops($integration);

                Log::info('getAuthorizedShops result:', [
                    'success' => $shopsResult['success'],
                    'error' => $shopsResult['error'] ?? null,
                    'has_data' => isset($shopsResult['data']),
                    'shops_count' => isset($shopsResult['data']['shops']) ? count($shopsResult['data']['shops']) : 0
                ]);

                if ($shopsResult['success'] && isset($shopsResult['data']['shops'])) {
                    Log::info('Processing shops data');

                    foreach ($shopsResult['data']['shops'] as $index => $shopData) {
                        Log::info("Processing shop {$index}:", [
                            'shop_id' => $shopData['id'] ?? 'unknown',
                            'shop_name' => $shopData['name'] ?? 'unknown',
                            'region' => $shopData['region'] ?? 'unknown',
                            'cipher' => $shopData['cipher'] ?? 'not_found',
                            'code' => $shopData['code'] ?? 'not_found',
                            'seller_type' => $shopData['seller_type'] ?? 'not_found'
                        ]);

                        TikTokShop::updateOrCreate(
                            [
                                'shop_id' => $shopData['id'],
                                'tiktok_shop_integration_id' => $integration->id,
                            ],
                            [
                                'team_id' => $team->id,
                                'shop_name' => $shopData['name'] ?? 'Unknown Shop',
                                'seller_name' => $shopData['seller_type'] ?? 'Unknown Seller',
                                'seller_region' => $shopData['region'] ?? 'Unknown',
                                'open_id' => $shopData['code'] ?? '',
                                'cipher' => $shopData['cipher'] ?? '',
                                'status' => 'active',
                                'shop_data' => $shopData,
                            ]
                        );
                    }

                    $shopCount = count($shopsResult['data']['shops']);
                    Log::info("Successfully processed {$shopCount} shops");

                    return redirect()->route('team.tiktok-shop.index')
                        ->with('success', "Kết nối TikTok Shop thành công! Đã đồng bộ {$shopCount} shop.");
                } else {
                    // Thử xử lý dữ liệu từ authorization code trực tiếp
                    Log::info('Trying to process authorization code data directly');

                    // Giả sử dữ liệu shop được trả về trong response
                    $shopData = $result['data']['shop'] ?? null;

                    if ($shopData) {
                        Log::info('Processing shop data from auth code response:', [
                            'id' => $shopData['id'] ?? 'unknown',
                            'name' => $shopData['name'] ?? 'unknown',
                            'region' => $shopData['region'] ?? 'unknown',
                            'cipher' => $shopData['cipher'] ?? 'not_found',
                            'code' => $shopData['code'] ?? 'not_found',
                            'seller_type' => $shopData['seller_type'] ?? 'not_found'
                        ]);

                        TikTokShop::updateOrCreate(
                            [
                                'shop_id' => $shopData['id'],
                                'tiktok_shop_integration_id' => $integration->id,
                            ],
                            [
                                'team_id' => $team->id,
                                'shop_name' => $shopData['name'] ?? 'Unknown Shop',
                                'seller_name' => $shopData['seller_type'] ?? 'Unknown Seller',
                                'seller_region' => $shopData['region'] ?? 'Unknown',
                                'open_id' => $shopData['code'] ?? '',
                                'cipher' => $shopData['cipher'] ?? '',
                                'status' => 'active',
                                'shop_data' => $shopData,
                            ]
                        );

                        return redirect()->route('team.tiktok-shop.index')
                            ->with('success', 'Kết nối TikTok Shop thành công! Shop đã được lưu.');
                    } else {
                        // Nếu không lấy được shops, vẫn cập nhật integration thành công
                        Log::warning('Could not get shops, but integration is successful');
                        return redirect()->route('team.tiktok-shop.index')
                            ->with('success', 'Kết nối TikTok Shop thành công! Access token đã được lưu.');
                    }
                }
            } else {
                Log::error('Failed to get access token', ['error' => $result['error']]);
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Lỗi xử lý authorization code: ' . $result['error']);
            }
        } catch (Exception $e) {
            Log::error('Team TikTok Shop Process Auth Code Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        Log::info('=== END PROCESSING AUTH CODE ===');
    }

    /**
     * Handle shop data from authorization code response
     */
    public function processShopData(Request $request)
    {
        Log::info('=== START PROCESSING SHOP DATA ===');
        Log::info('Request data:', $request->all());

        $request->validate([
            'shop_data' => 'required|json',
            'integration_id' => 'required|exists:tiktok_shop_integrations,id',
        ]);

        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $request->integration_id)
            ->first();

        if (!$integration) {
            Log::error('No TikTok Shop integration found for team');
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        try {
            // Parse JSON data
            $shopData = json_decode($request->shop_data, true);

            if (!$shopData) {
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Dữ liệu shop không hợp lệ.');
            }

            Log::info('Processing shop data:', [
                'id' => $shopData['id'] ?? 'unknown',
                'name' => $shopData['name'] ?? 'unknown',
                'region' => $shopData['region'] ?? 'unknown',
                'cipher' => $shopData['cipher'] ?? 'not_found',
                'code' => $shopData['code'] ?? 'not_found',
                'seller_type' => $shopData['seller_type'] ?? 'not_found'
            ]);

            // Lưu shop vào database
            $shop = TikTokShop::updateOrCreate(
                [
                    'shop_id' => $shopData['id'],
                    'tiktok_shop_integration_id' => $integration->id,
                ],
                [
                    'team_id' => $team->id,
                    'shop_name' => $shopData['name'] ?? 'Unknown Shop',
                    'seller_name' => $shopData['seller_type'] ?? 'Unknown Seller',
                    'seller_region' => $shopData['region'] ?? 'Unknown',
                    'open_id' => $shopData['code'] ?? '',
                    'cipher' => $shopData['cipher'] ?? '',
                    'status' => 'active',
                    'shop_data' => $shopData,
                ]
            );

            Log::info('Shop saved successfully:', [
                'shop_id' => $shop->id,
                'tiktok_shop_id' => $shop->shop_id,
                'shop_name' => $shop->shop_name
            ]);

            return redirect()->route('team.tiktok-shop.index')
                ->with('success', 'Kết nối TikTok Shop thành công! Shop "' . $shop->shop_name . '" đã được lưu.');
        } catch (Exception $e) {
            Log::error('Process Shop Data Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        Log::info('=== END PROCESSING SHOP DATA ===');
    }

    /**
     * Show manual authorization form
     */
    public function showManualAuth($integration_id)
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $integration_id)
            ->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        if ($integration->status !== 'pending') {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Tích hợp TikTok Shop đã được kết nối hoặc có lỗi.');
        }

        return view('team.tiktok-shop.manual-auth', compact('integration', 'team'));
    }

    /**
     * Disconnect TikTok Shop integration
     */
    public function disconnect($integration_id)
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $integration_id)
            ->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        try {
            // Clear tokens but keep the integration
            $integration->update([
                'access_token' => null,
                'refresh_token' => null,
                'access_token_expires_at' => null,
                'refresh_token_expires_at' => null,
                'status' => 'pending',
                'error_message' => null,
            ]);

            // Deactivate all shops
            $integration->shops()->update(['status' => 'inactive']);

            return redirect()->route('team.tiktok-shop.index')
                ->with('success', 'Đã ngắt kết nối TikTok Shop. Bạn có thể kết nối lại bất cứ lúc nào.');
        } catch (Exception $e) {
            Log::error('Team TikTok Shop Disconnect Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        if (!$integration->isActive()) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Tích hợp TikTok Shop chưa được kết nối hoặc đã hết hạn.');
        }

        try {
            $result = $this->tikTokShopService->getAuthorizedShops($integration);

            if ($result['success']) {
                $shopCount = isset($result['data']['shop_list']) ? count($result['data']['shop_list']) : 0;
                return redirect()->route('team.tiktok-shop.index')
                    ->with('success', "Kết nối API thành công! Tìm thấy {$shopCount} shop.");
            } else {
                return redirect()->route('team.tiktok-shop.index')
                    ->with('error', 'Lỗi kết nối API: ' . $result['error']);
            }
        } catch (Exception $e) {
            Log::error('Team TikTok Shop Test Connection Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Assign seller to shop
     */
    public function assignSeller(Request $request, TikTokShop $shop)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,manager,viewer',
            'permissions' => 'nullable|array',
        ]);

        // Check if user belongs to the same team
        $user = User::with('tiktokMarkets')->find($request->user_id);
        if ($user->team_id !== Auth::user()->team_id) {
            return back()->with('error', 'Người dùng không thuộc team này.');
        }

        // Get shop's market from integration
        $shopIntegration = $shop->integration;
        if (!$shopIntegration) {
            return back()->with('error', 'Không tìm thấy thông tin tích hợp của shop.');
        }

        $shopMarket = $shopIntegration->market;
        
        // Check if user has access to the shop's market
        if (!$user->hasTikTokMarket($shopMarket)) {
            return back()->with('error', 'Không thể phân quyền: User ' . $user->name . ' không có quyền truy cập thị trường ' . $shopMarket . '. Shop này thuộc thị trường ' . $shopMarket . ', nhưng user chỉ có quyền truy cập: ' . implode(', ', $user->getTikTokMarkets()) . '.');
        }

        try {
            TikTokShopSeller::updateOrCreate(
                [
                    'tiktok_shop_id' => $shop->id,
                    'user_id' => $request->user_id,
                ],
                [
                    'role' => $request->role,
                    'permissions' => $request->permissions,
                    'is_active' => true,
                ]
            );

            return back()->with('success', 'Đã phân quyền seller thành công.');
        } catch (Exception $e) {
            Log::error('Assign Seller Error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Remove seller from shop
     */
    public function removeSeller(TikTokShop $shop, TikTokShopSeller $seller)
    {
        try {
            $seller->update(['is_active' => false]);
            return back()->with('success', 'Đã xóa quyền seller thành công.');
        } catch (Exception $e) {
            Log::error('Remove Seller Error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Show edit integration form
     */
    public function editIntegration($integration_id)
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $integration_id)
            ->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        return view('team.tiktok-shop.edit-integration', compact('integration', 'team'));
    }

    /**
     * Update integration
     */
    public function updateIntegration(Request $request, $integration_id)
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $integration_id)
            ->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $integration->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('team.tiktok-shop.index')
                ->with('success', 'Cập nhật tích hợp TikTok Shop thành công!');
        } catch (Exception $e) {
            Log::error('Update Integration Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete integration
     */
    public function deleteIntegration($integration_id)
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('id', $integration_id)
            ->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Không tìm thấy tích hợp TikTok Shop.');
        }

        try {
            // Xóa tất cả shops liên quan
            $integration->shops()->delete();

            // Xóa integration
            $integration->delete();

            return redirect()->route('team.tiktok-shop.index')
                ->with('success', 'Xóa tích hợp TikTok Shop thành công!');
        } catch (Exception $e) {
            Log::error('Delete Integration Error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Generate authorization link for customer
     */
    public function generateAuthLink()
    {
        $team = Auth::user()->team;
        $integration = TikTokShopIntegration::where('team_id', $team->id)
            ->where('status', 'pending')
            ->first();

        if (!$integration) {
            return redirect()->route('team.tiktok-shop.index')
                ->with('error', 'Vui lòng tạo tích hợp TikTok Shop trước.');
        }

        // Generate unique token for this authorization request
        $authToken = Str::random(32);

        // Store auth token in session with expiration
        session([
            'tiktok_auth_token' => $authToken,
            'tiktok_auth_team_id' => $team->id,
            'tiktok_auth_expires' => now()->addHours(1)->timestamp
        ]);

        // Create authorization URL with custom callback
        $authUrl = $this->createCustomerAuthUrl($integration, $authToken);

        return view('team.tiktok-shop.auth-link', compact('authUrl', 'authToken', 'team'));
    }

    /**
     * Create authorization URL for customer
     */
    private function createCustomerAuthUrl(TikTokShopIntegration $integration, string $authToken): string
    {
        $params = [
            'app_key' => $integration->getAppKey(),
            'state' => base64_encode(json_encode([
                'team_id' => $integration->team_id,
                'auth_token' => $authToken,
                'type' => 'customer_auth'
            ])),
            'redirect_uri' => route('public.customer-callback'),
            'scope' => config('tiktok-shop.oauth.scope'),
        ];

        return 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Handle customer authorization callback
     */
    public function customerCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            $state = $request->get('state');
            $error = $request->get('error');

            if ($error) {
                return view('team.tiktok-shop.auth-result', [
                    'success' => false,
                    'message' => 'Ủy quyền bị từ chối: ' . $error,
                    'authCode' => null
                ]);
            }

            if (!$code) {
                return view('team.tiktok-shop.auth-result', [
                    'success' => false,
                    'message' => 'Thiếu authorization code từ TikTok.',
                    'authCode' => null
                ]);
            }

            // Log all parameters for debugging
            Log::info('TikTok Callback Parameters', [
                'code' => $code,
                'state' => $state,
                'app_key' => $request->get('app_key'),
                'locale' => $request->get('locale'),
                'shop_region' => $request->get('shop_region')
            ]);

            // Xử lý state parameter (không bắt buộc)
            $stateData = null;
            $teamId = null;

            if ($state) {
                if (is_numeric($state)) {
                    // State là team_id trực tiếp
                    $teamId = (int)$state;
                    $stateData = ['team_id' => $teamId];
                } else {
                    // State là JSON encoded
                    try {
                        $stateData = json_decode(base64_decode($state), true);
                        if ($stateData && isset($stateData['team_id'])) {
                            $teamId = $stateData['team_id'];
                        }
                    } catch (\Exception $e) {
                        Log::error('Invalid state parameter: ' . $e->getMessage());
                    }
                }
            }

            // Nếu không có state, sử dụng team_id mặc định hoặc bỏ qua validation
            if (!$teamId) {
                $teamId = 7; // Team ID mặc định
                $stateData = ['team_id' => $teamId];
            }

            // Tìm integration theo team_id (tìm integration mới nhất) - không bắt buộc
            $integration = TikTokShopIntegration::where('team_id', $teamId)
                ->orderBy('id', 'desc')
                ->first();

            // Nếu không có integration, vẫn hiển thị code
            if (!$integration) {
                return view('team.tiktok-shop.auth-result', [
                    'success' => true,
                    'message' => 'Lấy authorization code thành công!',
                    'authCode' => $code,
                    'teamId' => $teamId,
                    'integrationId' => null,
                    'appKey' => $request->get('app_key'),
                    'locale' => $request->get('locale'),
                    'shopRegion' => $request->get('shop_region')
                ]);
            }

            // Kiểm tra auth token trong integration (chỉ khi state là JSON encoded)
            if (isset($stateData['auth_token'])) {
                $integrationAuthToken = $integration->additional_data['auth_token'] ?? null;
                $integrationAuthExpires = $integration->additional_data['auth_token_expires'] ?? null;

                if (!$integrationAuthToken || $integrationAuthToken !== $stateData['auth_token']) {
                    return view('team.tiktok-shop.auth-result', [
                        'success' => false,
                        'message' => 'Session không hợp lệ. Vui lòng tạo link authorization mới.',
                        'authCode' => null
                    ]);
                }

                if (!$integrationAuthExpires || now()->timestamp > $integrationAuthExpires) {
                    return view('team.tiktok-shop.auth-result', [
                        'success' => false,
                        'message' => 'Link authorization đã hết hạn. Vui lòng tạo link mới.',
                        'authCode' => null
                    ]);
                }
            }

            // Clear auth token từ integration (chỉ khi có auth token)
            if (isset($stateData['auth_token'])) {
                $integration->update([
                    'additional_data' => array_merge($integration->additional_data ?? [], [
                        'auth_token' => null,
                        'auth_token_expires' => null
                    ])
                ]);
            }

            // Chỉ hiển thị authorization code, không tự động kết nối
            return view('team.tiktok-shop.auth-result', [
                'success' => true,
                'message' => 'Lấy authorization code thành công!',
                'authCode' => $code,
                'teamId' => $teamId,
                'integrationId' => $integration->id,
                'appKey' => $request->get('app_key'),
                'locale' => $request->get('locale'),
                'shopRegion' => $request->get('shop_region')
            ]);
        } catch (Exception $e) {
            Log::error('Customer Auth Callback Error: ' . $e->getMessage());
            return view('team.tiktok-shop.auth-result', [
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                'authCode' => null
            ]);
        }
    }
}
