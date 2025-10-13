<?php

namespace App\Http\Controllers;

use App\Models\TikTokShopIntegration;
use App\Services\TikTokShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class TikTokShopController extends Controller
{
    protected $tikTokShopService;

    public function __construct(TikTokShopService $tikTokShopService)
    {
        $this->middleware('role:system-admin');
        $this->tikTokShopService = $tikTokShopService;
    }

    /**
     * Display TikTok Shop integration dashboard
     */
    public function index()
    {
        $integrations = TikTokShopIntegration::with(['team', 'activeShops'])->get();

        return view('tiktok-shop.index', compact('integrations'));
    }

    /**
     * Show the form for creating a new integration
     */
    public function create()
    {
        $teams = \App\Models\Team::whereDoesntHave('tiktokShopIntegration')->get();

        return view('tiktok-shop.create', compact('teams'));
    }

    /**
     * Store a newly created integration
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'app_key' => 'required|string|max:255',
            'app_secret' => 'required|string|max:255',
        ]);

        // Check if integration already exists for this team
        $existingIntegration = TikTokShopIntegration::where('team_id', $request->team_id)->first();
        if ($existingIntegration) {
            return redirect()->route('tiktok-shop.index')
                ->with('error', 'Team đã có tích hợp TikTok Shop.');
        }

        try {
            // Validate credentials format (simple validation)
            $validationResult = $this->tikTokShopService->simpleValidateCredentials(
                $request->app_key,
                $request->app_secret
            );

            if (!$validationResult['success']) {
                return back()->withErrors(['app_key' => $validationResult['error']]);
            }

            // Create integration
            $integration = TikTokShopIntegration::create([
                'team_id' => $request->team_id,
                'app_key' => $request->app_key,
                'app_secret' => $request->app_secret,
                'status' => 'pending',
            ]);

            return redirect()->route('tiktok-shop.index')
                ->with('success', 'Tích hợp TikTok Shop đã được tạo thành công cho team. Vui lòng hoàn tất quá trình ủy quyền.');
        } catch (Exception $e) {
            Log::error('TikTok Shop Integration Create Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the integration
     */
    public function edit(TikTokShopIntegration $integration)
    {
        return view('tiktok-shop.edit', compact('integration'));
    }

    /**
     * Update the integration
     */
    public function update(Request $request, TikTokShopIntegration $integration)
    {
        $request->validate([
            'app_key' => 'required|string|max:255',
            'app_secret' => 'required|string|max:255',
        ]);

        try {
            // Validate credentials format (simple validation)
            $validationResult = $this->tikTokShopService->simpleValidateCredentials(
                $request->app_key,
                $request->app_secret
            );

            if (!$validationResult['success']) {
                return back()->withErrors(['app_key' => $validationResult['error']]);
            }

            // Update integration
            $integration->update([
                'app_key' => $request->app_key,
                'app_secret' => $request->app_secret,
                'status' => 'pending', // Reset status when credentials change
                'error_message' => null,
            ]);

            return redirect()->route('tiktok-shop.index')
                ->with('success', 'Thông tin tích hợp đã được cập nhật. Vui lòng hoàn tất quá trình ủy quyền.');
        } catch (Exception $e) {
            Log::error('TikTok Shop Integration Update Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the integration
     */
    public function destroy(TikTokShopIntegration $integration)
    {
        try {
            $integration->delete();
            return redirect()->route('tiktok-shop.index')
                ->with('success', 'Tích hợp TikTok Shop đã được xóa thành công.');
        } catch (Exception $e) {
            Log::error('TikTok Shop Integration Delete Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }



    /**
     * Refresh access token manually
     */
    public function refreshToken(TikTokShopIntegration $integration)
    {
        try {
            $result = $this->tikTokShopService->refreshAccessToken($integration);

            if ($result['success']) {
                $integration->updateTokens($result['data']);

                return redirect()->route('tiktok-shop.index')
                    ->with('success', 'Token đã được làm mới thành công!');
            } else {
                $integration->markAsError($result['error']);

                return redirect()->route('tiktok-shop.index')
                    ->with('error', 'Lỗi làm mới token: ' . $result['error']);
            }
        } catch (Exception $e) {
            Log::error('TikTok Shop Refresh Token Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(TikTokShopIntegration $integration)
    {
        try {
            $result = $this->tikTokShopService->getShopInfo($integration);

            if ($result['success']) {
                return redirect()->route('tiktok-shop.index')
                    ->with('success', 'Kết nối API thành công! Thông tin shop: ' . ($result['data']['shop_name'] ?? 'N/A'));
            } else {
                return redirect()->route('tiktok-shop.index')
                    ->with('error', 'Lỗi kết nối API: ' . $result['error']);
            }
        } catch (Exception $e) {
            Log::error('TikTok Shop Test Connection Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Debug OAuth flow information
     */
    public function debug(TikTokShopIntegration $integration)
    {
        $oauthInfo = $this->tikTokShopService->getOAuthFlowInfo($integration);

        return view('tiktok-shop.debug', compact('integration', 'oauthInfo'));
    }

    /**
     * Test credentials with API
     */
    public function testCredentials(TikTokShopIntegration $integration)
    {
        try {
            $result = $this->tikTokShopService->validateCredentials(
                $integration->app_key,
                $integration->app_secret
            );

            if ($result['success']) {
                return redirect()->route('tiktok-shop.index')
                    ->with('success', 'Thông tin ứng dụng hợp lệ! ' . $result['message']);
            } else {
                return redirect()->route('tiktok-shop.index')
                    ->with('error', 'Thông tin ứng dụng không hợp lệ: ' . $result['error']);
            }
        } catch (Exception $e) {
            return redirect()->route('tiktok-shop.index')
                ->with('error', 'Lỗi kiểm tra thông tin ứng dụng: ' . $e->getMessage());
        }
    }
}
