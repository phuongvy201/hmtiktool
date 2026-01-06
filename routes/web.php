<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicePackageController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamAdminController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\TeamSubscriptionController;
use App\Http\Controllers\TikTokShopController;
use App\Http\Controllers\TeamTikTokShopController;
use App\Http\Controllers\ProductTemplateController;
use App\Http\Controllers\TikTokCategoryAttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TikTokOrderController;
use App\Http\Controllers\TikTokFinanceController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Public customer callback route (không cần authentication)
Route::get('/public/customer-callback', [TeamTikTokShopController::class, 'customerCallback'])->name('public.customer-callback');

// TikTok Webhook routes (không cần authentication)
Route::prefix('tiktok/webhook')->name('tiktok.webhook.')->group(function () {
    Route::post('/handle', [App\Http\Controllers\TikTokWebhookController::class, 'handleWebhook'])->name('handle');
    Route::post('/order-status', [App\Http\Controllers\TikTokWebhookController::class, 'handleOrderStatusChange'])->name('order-status');
    Route::get('/test', [App\Http\Controllers\TikTokWebhookController::class, 'testWebhook'])->name('test');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Email Verification Routes (custom implementation)
Route::get('/email/verify', [EmailVerificationController::class, 'showVerificationForm'])->name('verification.notice');
Route::post('/email/verify', [EmailVerificationController::class, 'sendVerificationEmail'])->name('verification.send');
Route::get('/email/verify/{id}/{token}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('email.verification.verify');
Route::get('/email/resend', [EmailVerificationController::class, 'showResendForm'])->name('verification.resend.form');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::post('/profile/send-verification-email', [ProfileController::class, 'sendVerificationEmail'])->name('profile.send-verification-email');
    Route::get('/profile/activity', [ProfileController::class, 'activity'])->name('profile.activity');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::post('/profile/two-factor', [ProfileController::class, 'toggleTwoFactor'])->name('profile.two-factor');
    Route::get('/profile/notifications', [ProfileController::class, 'notifications'])->name('profile.notifications');
    Route::post('/profile/notifications/{id}/read', [ProfileController::class, 'markNotificationAsRead'])->name('profile.notifications.read');
    Route::post('/profile/export', [ProfileController::class, 'exportData'])->name('profile.export');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User management routes
    Route::middleware('permission:view-users')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Team management routes
    Route::middleware('permission:view-teams')->group(function () {
        Route::resource('teams', TeamController::class);
    });

    // Role management routes
    Route::middleware('permission:view-roles')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Team Admin routes
    Route::middleware('role:team-admin')->prefix('team-admin')->name('team-admin.')->group(function () {
        Route::get('/dashboard', [TeamAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/roles', [TeamAdminController::class, 'teamRoles'])->name('roles.index');
        Route::resource('users', TeamAdminController::class);
    });

    // System level routes
    // System Settings routes
    Route::middleware('permission:view-system-settings')->prefix('system')->name('system.')->group(function () {
        Route::get('settings', [SystemSettingController::class, 'index'])->name('settings');
        Route::post('settings/update', [SystemSettingController::class, 'update'])->name('settings.update');
        Route::post('settings/reset', [SystemSettingController::class, 'reset'])->name('settings.reset');
        Route::get('settings/export', [SystemSettingController::class, 'export'])->name('settings.export');
        Route::post('settings/import', [SystemSettingController::class, 'import'])->name('settings.import');
        Route::get('settings/info', [SystemSettingController::class, 'systemInfo'])->name('settings.info');
    });

    // Service Packages & Backup routes
    Route::middleware('permission:view-service-packages')->group(function () {

        // Backup & Restore routes
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::get('/create', [BackupController::class, 'create'])->name('create');
            Route::post('/', [BackupController::class, 'store'])->name('store');
            Route::get('/{backup}', [BackupController::class, 'show'])->name('show');
            Route::get('/{backup}/download', [BackupController::class, 'download'])->name('download');
            Route::post('/{backup}/restore', [BackupController::class, 'restore'])->name('restore');
            Route::delete('/{backup}', [BackupController::class, 'destroy'])->name('destroy');
            Route::post('/cleanup', [BackupController::class, 'cleanup'])->name('cleanup');
            Route::post('/auto-backup', [BackupController::class, 'autoBackup'])->name('auto-backup');
            Route::get('/status', [BackupController::class, 'status'])->name('status');
            Route::get('/export', [BackupController::class, 'export'])->name('export');
        });
    });

    // Service Package management routes
    Route::middleware('role:system-admin|permission:view-service-packages')->group(function () {
        Route::resource('service-packages', ServicePackageController::class);
        Route::patch('/service-packages/{servicePackage}/toggle-active', [ServicePackageController::class, 'toggleActive'])
            ->name('service-packages.toggle-active');
        Route::patch('/service-packages/{servicePackage}/toggle-featured', [ServicePackageController::class, 'toggleFeatured'])
            ->name('service-packages.toggle-featured');
    });

    // Team Subscription management routes
    Route::resource('team-subscriptions', TeamSubscriptionController::class);
    Route::post('/teams/{team}/assign-package', [TeamSubscriptionController::class, 'assignToTeam'])
        ->name('team-subscriptions.assign-to-team');
    Route::get('/teams/{team}/subscriptions', [TeamSubscriptionController::class, 'teamSubscriptions'])
        ->name('team-subscriptions.team-subscriptions');

    // TikTok Shop Integration routes (System Admin)
    Route::middleware('role:system-admin')->prefix('admin/tiktok-shop')->name('tiktok-shop.')->group(function () {
        Route::get('/', [TikTokShopController::class, 'index'])->name('index');
        Route::get('/create', [TikTokShopController::class, 'create'])->name('create');
        Route::post('/', [TikTokShopController::class, 'store'])->name('store');
        Route::get('/{integration}/edit', [TikTokShopController::class, 'edit'])->name('edit');
        Route::put('/{integration}', [TikTokShopController::class, 'update'])->name('update');
        Route::delete('/{integration}', [TikTokShopController::class, 'destroy'])->name('destroy');
        Route::get('/{integration}/debug', [TikTokShopController::class, 'debug'])->name('debug');
        Route::post('/{integration}/test-credentials', [TikTokShopController::class, 'testCredentials'])->name('test-credentials');
        Route::post('/{integration}/refresh-token', [TikTokShopController::class, 'refreshToken'])->name('refresh-token');
        Route::post('/{integration}/test-connection', [TikTokShopController::class, 'testConnection'])->name('test-connection');
    });

    // Temporary test route for authorization code
    Route::get('/test-auth-code', function () {
        $appKey = '6h5b0bsgaonml';
        $appSecret = '55f4e32e0749bc3eb94bf8d422dd407fbffdbb69'; // Get from query parameter
        $authCode = 'GCP_iJnwHgAAAAC73m1XWW50tl9OYJfkdb3pd_P2MXTapUsmSDcPJXtDtmNKqtoBMsHzNTuSPVvIFukAatMjF9AihVBx18VmtdtWTlZTtrZbSE6aPzZ9fE5ekzcPnHXD3mBvu4HpXzN3Hqk';

        if (empty($appSecret)) {
            return response()->json([
                'error' => 'Vui lòng cung cấp app_secret trong query parameter',
                'example' => '/test-auth-code?app_secret=YOUR_APP_SECRET'
            ]);
        }

        $service = app(\App\Services\TikTokShopService::class);
        $result = $service->testAuthorizationCode($appKey, $appSecret, $authCode);

        return response()->json($result);
    });

    // TikTok Shop Connection routes (Team Admin)
    Route::middleware('role:team-admin')->prefix('team/tiktok-shop')->name('team.tiktok-shop.')->group(function () {
        Route::get('/', [TeamTikTokShopController::class, 'index'])->name('index');
        Route::get('/create-integration', [TeamTikTokShopController::class, 'createIntegration'])->name('create-integration');
        Route::post('/store-integration', [TeamTikTokShopController::class, 'storeIntegration'])->name('store-integration');
        Route::get('/connect/{integration_id}', [TeamTikTokShopController::class, 'connect'])->name('connect');
        Route::get('/callback', [TeamTikTokShopController::class, 'callback'])->name('callback');
        Route::get('/manual-auth/{integration_id}', [TeamTikTokShopController::class, 'showManualAuth'])->name('manual-auth');
        Route::post('/process-auth-code', [TeamTikTokShopController::class, 'processAuthCode'])->name('process-auth-code');
        Route::post('/process-shop-data', [TeamTikTokShopController::class, 'processShopData'])->name('process-shop-data');
        Route::post('/disconnect/{integration_id}', [TeamTikTokShopController::class, 'disconnect'])->name('disconnect');
        Route::post('/test-connection', [TeamTikTokShopController::class, 'testConnection'])->name('test-connection');

        Route::post('/shops/{shop}/assign-seller', [TeamTikTokShopController::class, 'assignSeller'])->name('assign-seller');

        // Integration management routes
        Route::get('/edit/{integration_id}', [TeamTikTokShopController::class, 'editIntegration'])->name('edit-integration');
        Route::post('/update/{integration_id}', [TeamTikTokShopController::class, 'updateIntegration'])->name('update-integration');
        Route::delete('/delete/{integration_id}', [TeamTikTokShopController::class, 'deleteIntegration'])->name('delete-integration');

        // Customer authorization routes
        Route::get('/generate-auth-link', [TeamTikTokShopController::class, 'generateAuthLink'])->name('generate-auth-link');
        Route::get('/customer-callback', [TeamTikTokShopController::class, 'customerCallback'])->name('customer-callback');

        // Test route for callback (remove in production)
        Route::get('/test-callback', function (Request $request) {
            return response()->json([
                'message' => 'Callback test successful',
                'params' => $request->all(),
                'session_data' => [
                    'tiktok_auth_token' => session('tiktok_auth_token'),
                    'tiktok_auth_team_id' => session('tiktok_auth_team_id'),
                    'tiktok_auth_expires' => session('tiktok_auth_expires')
                ]
            ]);
        })->name('test-callback');
        Route::put('/shops/{shop}/sellers/{seller}/remove', [TeamTikTokShopController::class, 'removeSeller'])->name('remove-seller');
    });

    // Product Template routes - Bỏ middleware để tránh lỗi 403
    // Define routes manually to avoid conflicts

    Route::get('/product-templates', [ProductTemplateController::class, 'index'])->name('product-templates.index');
    Route::get('/product-templates/create', [ProductTemplateController::class, 'create'])->name('product-templates.create');
    Route::get('/product-templates/search-categories', [ProductTemplateController::class, 'searchCategories'])->name('product-templates.search-categories');
    Route::get('/product-templates/{productTemplate}', [ProductTemplateController::class, 'show'])->name('product-templates.show');
    Route::get('/product-templates/{productTemplate}/edit', [ProductTemplateController::class, 'edit'])->name('product-templates.edit');
    Route::put('/product-templates/{productTemplate}', [ProductTemplateController::class, 'update'])->name('product-templates.update');
    Route::delete('/product-templates/{productTemplate}', [ProductTemplateController::class, 'destroy'])->name('product-templates.destroy');
    Route::post('/product-templates/{productTemplate}/duplicate', [ProductTemplateController::class, 'duplicate'])->name('product-templates.duplicate');

    // Store route
    Route::post('/product-templates', [ProductTemplateController::class, 'store'])
        ->name('product-templates.store');

    Route::post('/product-templates/{productTemplate}/update-variants', [ProductTemplateController::class, 'updateVariants'])->name('product-templates.update-variants');
    Route::post('/product-templates/{productTemplate}/set-bulk-price', [ProductTemplateController::class, 'setBulkPrice'])->name('product-templates.set-bulk-price');
    Route::post('/product-templates/{productTemplate}/delete-variant', [ProductTemplateController::class, 'deleteVariant'])->name('product-templates.delete-variant');
    Route::get('/product-templates/{productTemplate}/existing-attributes', [ProductTemplateController::class, 'getExistingAttributes'])->name('product-templates.existing-attributes');

    // Product routes - Đặt các route cụ thể TRƯỚC resource route để tránh conflict
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download-template');
    Route::get('/products/by-template', [ProductController::class, 'getByTemplate'])->name('products.by-template');
    Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::resource('products', ProductController::class);

    // TikTok Orders routes
    Route::prefix('tiktok/orders')->name('tiktok.orders.')->group(function () {
        Route::get('/', [TikTokOrderController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/export', [TikTokOrderController::class, 'export'])->name('export');
        Route::get('/{order}', [TikTokOrderController::class, 'show'])->name('show');
        Route::post('/sync', [TikTokOrderController::class, 'sync'])->name('sync');
        Route::get('/api/list', [TikTokOrderController::class, 'apiOrders'])->name('api');
    });

    // TikTok Finance routes
    Route::middleware(['auth', 'permission:view-financial-reports'])->prefix('tiktok/finance')->name('tiktok.finance.')->group(function () {
        Route::get('/', [TikTokFinanceController::class, 'index'])->name('index');
        Route::get('/export', [TikTokFinanceController::class, 'export'])->name('export');
    });

    // TikTok Performance routes
    Route::middleware(['auth', 'permission:view-financial-reports'])->prefix('tiktok/performance')->name('tiktok.performance.')->group(function () {
        Route::get('/', [App\Http\Controllers\TikTokPerformanceController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\TikTokPerformanceController::class, 'getPerformanceData'])->name('data');
        Route::post('/refresh', [App\Http\Controllers\TikTokPerformanceController::class, 'refresh'])->name('refresh');
    });

    // TikTok Shop Analytics routes
    Route::prefix('tiktok/analytics')->name('tiktok.analytics.')->group(function () {
        Route::get('/', [App\Http\Controllers\TikTokShopAnalyticsController::class, 'index'])->name('index');
        Route::get('/api/data', [App\Http\Controllers\TikTokShopAnalyticsController::class, 'getAnalyticsData'])->name('data');
        Route::get('/test/product-api', [App\Http\Controllers\TikTokShopAnalyticsController::class, 'testProductAPI'])->name('test.product');
    });

    // TikTok Shipping routes
    Route::prefix('tiktok/shipping')->name('tiktok.shipping.')->group(function () {
        Route::get('/orders/{orderId}/providers', [App\Http\Controllers\TikTokShippingController::class, 'getShippingProviders'])->name('providers');
        Route::get('/orders/{orderId}/info', [App\Http\Controllers\TikTokShippingController::class, 'getOrderShippingInfo'])->name('order.info');
        Route::post('/orders/{orderId}/mark-shipped', [App\Http\Controllers\TikTokShippingController::class, 'markAsShipped'])->name('mark.shipped');
    });
    Route::post('/products/{product}/upload-images-to-tiktok', [ProductController::class, 'uploadImagesToTikTok'])->name('products.upload-images-to-tiktok');
    Route::post('/products/bulk-upload-to-tiktok', [ProductController::class, 'bulkUploadToTikTok'])->name('products.bulk-upload-to-tiktok');
    Route::post('/products/upload-to-tiktok', [ProductController::class, 'uploadProductToTikTok'])->name('products.upload-to-tiktok');
    Route::post('/products/retry-upload/{historyId}', [ProductController::class, 'retryUpload'])->name('products.retry-upload');

    // Test route for products
    Route::get('/test-products-access', function () {
        $user = Auth::user();
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'team_id' => $user->team_id,
            'current_team' => $user->currentTeam ? $user->currentTeam->name : 'null',
            'roles' => $user->roles->pluck('name')->toArray(),
            'has_view_products_permission' => $user->hasPermissionTo('view-products'),
            'is_team_admin' => $user->hasRole('team-admin')
        ]);
    })->name('test.products.access');

    // Test route for TikTok Shop categories API
    Route::get('/test/tiktok-categories', function () {
        $tikTokService = app(\App\Services\TikTokShopService::class);
        $user = Auth::user();
        $teamId = $user->team->id;

        $integration = \App\Models\TikTokShopIntegration::where('team_id', $teamId)->first();

        if ($integration && $integration->access_token) {
            $result = $tikTokService->getCategories($integration);
            return response()->json($result);
        } else {
            $categories = $tikTokService->getCategoriesWithFallback();
            return response()->json([
                'success' => true,
                'message' => 'No TikTok Shop integration found, using default categories',
                'data' => $categories
            ]);
        }
    })->name('test.tiktok-categories');

    // Test route for product upload signature
    Route::get('/test/product-upload-signature', function () {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (!$team) {
            return response()->json(['error' => 'No team found'], 400);
        }

        $integration = $team->tiktokShopIntegration;
        if (!$integration) {
            return response()->json(['error' => 'No TikTok integration found'], 400);
        }

        $shop = $team->activeTikTokShops->first();
        if (!$shop) {
            return response()->json(['error' => 'No TikTok shop found'], 400);
        }

        $timestamp = time();
        $appKey = $integration->getAppKey();
        $appSecret = $integration->getAppSecret();

        // Test simple signature
        $params = [
            'app_key' => $appKey,
            'timestamp' => $timestamp
        ];
        ksort($params);
        $queryString = http_build_query($params);
        $signString = $queryString . '&app_secret=' . $appSecret;
        $simpleSignature = md5($signString);

        // Test TikTokSignatureService cho image upload
        $tikTokImageSignature = \App\Services\TikTokSignatureService::generateImageUploadSignature(
            $appKey,
            $appSecret,
            (string) $timestamp
        );

        // Test TikTokSignatureService cho product upload
        $sampleProductData = [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'category_id' => '1000001',
            'main_images' => [['uri' => 'test_uri']],
            'skus' => [['inventory' => ['warehouse_id' => 'UK_WAREHOUSE_001'], 'price' => ['currency' => 'USD', 'amount' => '10.00']]]
        ];

        $tikTokProductSignature = \App\Services\TikTokSignatureService::generateProductUploadSignature(
            $appKey,
            $appSecret,
            (string) $timestamp,
            $sampleProductData,
            $shop->cipher
        );

        // Test signature KHÔNG có shop_cipher trong generation
        $tikTokProductSignatureNoShop = \App\Services\TikTokSignatureService::generateProductUploadSignature(
            $appKey,
            $appSecret,
            (string) $timestamp,
            $sampleProductData
        );

        return response()->json([
            'timestamp' => $timestamp,
            'app_key' => $appKey,
            'app_secret_length' => strlen($appSecret),
            'shop_cipher' => $shop->cipher,
            'simple_signature' => $simpleSignature,
            'tiktok_image_signature' => $tikTokImageSignature,
            'tiktok_product_signature_with_shop' => $tikTokProductSignature,
            'tiktok_product_signature_no_shop' => $tikTokProductSignatureNoShop,
            'query_string' => $queryString,
            'sign_string' => $signString,
            'sample_product_data' => $sampleProductData,
            'note' => 'product_signature_no_shop should be used for actual API calls'
        ]);
    })->name('test.product-upload-signature');

    // Test route cho TikTok categories sync
    Route::get('/test/tiktok-categories-sync', function () {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            $team = $user->team;
            if (!$team) {
                return response()->json(['error' => 'User not in team'], 400);
            }

            $integration = $team->tiktokShopIntegration;
            if (!$integration) {
                return response()->json(['error' => 'No TikTok Shop integration found'], 400);
            }

            // Chạy sync command
            $output = Artisan::call('tiktok:sync-categories', [
                '--team-id' => $team->id,
                '--force' => true
            ]);

            // Lấy categories từ cache
            $cachedCategories = \App\Models\TikTokShopCategory::leafCategories()
                ->orderBy('category_name')
                ->get(['category_id', 'category_name', 'last_synced_at']);

            return response()->json([
                'message' => 'Categories synced successfully',
                'command_output' => $output,
                'cached_categories_count' => $cachedCategories->count(),
                'categories' => $cachedCategories->take(10)->toArray(),
                'last_synced' => $cachedCategories->max('last_synced_at')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->middleware('auth')->name('test.tiktok.categories.sync');

    // Test route for service packages
    Route::get('/test-service-packages', function () {
        return 'Service packages route is working!';
    })->name('test.service-packages');

    // Test route for service packages index
    Route::get('/test-service-packages-index', function () {
        try {
            $packages = \App\Models\ServicePackage::paginate(10);
            return view('service-packages.index', compact('packages'));
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    })->name('test.service-packages-index');

    // Simple test route
    Route::get('/test-simple', function () {
        return '<h1>Simple test works!</h1>';
    })->name('test.simple');

    // Test model route
    Route::get('/test-model', function () {
        try {
            $count = \App\Models\ServicePackage::count();
            return "ServicePackage count: {$count}";
        } catch (\Exception $e) {
            return 'Model Error: ' . $e->getMessage();
        }
    })->name('test.model');

    // Test view route
    Route::get('/test-view', function () {
        try {
            $packages = \App\Models\ServicePackage::all();
            return view('service-packages.index', compact('packages'));
        } catch (\Exception $e) {
            return 'View Error: ' . $e->getMessage();
        }
    })->name('test.view');

    // Test view without auth
    Route::get('/test-view-no-auth', function () {
        try {
            $packages = \App\Models\ServicePackage::all();
            return view('service-packages.index', compact('packages'));
        } catch (\Exception $e) {
            return 'View Error: ' . $e->getMessage();
        }
    })->name('test.view.no.auth');

    // Test simple view
    Route::get('/test-simple-view', function () {
        try {
            $packages = \App\Models\ServicePackage::all();
            return '<h1>Packages found: ' . $packages->count() . '</h1><ul>' .
                $packages->map(function ($p) {
                    return '<li>' . $p->name . '</li>';
                })->implode('') .
                '</ul>';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    })->name('test.simple.view');

    // Financial routes
    Route::middleware('permission:view-financial-reports')->group(function () {
        Route::get('/financial/reports', function () {
            return view('financial.reports');
        })->name('financial.reports');
    });

    // Fulfillment routes
    Route::middleware('permission:view-fulfillment')->group(function () {
        Route::get('/fulfillment', function () {
            return view('fulfillment.index');
        })->name('fulfillment.index');
    });

    // Sales routes
    Route::middleware('permission:view-sales')->group(function () {
        Route::get('/sales', function () {
            return view('sales.index');
        })->name('sales.index');
    });

    // TikTok Category Attributes routes
    Route::prefix('tik-tok-category-attributes')->name('tik-tok-category-attributes.')->group(function () {
        Route::get('/', [TikTokCategoryAttributeController::class, 'index'])->name('index');
        Route::post('/sync', [TikTokCategoryAttributeController::class, 'sync'])->name('sync');
        Route::get('/{id}', [TikTokCategoryAttributeController::class, 'show'])->name('show');
        Route::get('/api/attributes', [TikTokCategoryAttributeController::class, 'getAttributes'])->name('api.attributes');
        Route::get('/api/values', [TikTokCategoryAttributeController::class, 'getAttributeValues'])->name('api.values');
        Route::get('/api/check-sync-status', [TikTokCategoryAttributeController::class, 'checkSyncStatus'])->name('api.check-sync-status');
    });



    // S3 Image Upload API routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/upload-image', [\App\Http\Controllers\Api\ImageUploadController::class, 'uploadImage'])->name('upload-image');
        Route::post('/upload-multiple-images', [\App\Http\Controllers\Api\ImageUploadController::class, 'uploadMultipleImages'])->name('upload-multiple-images');
        Route::delete('/delete-image', [\App\Http\Controllers\Api\ImageUploadController::class, 'deleteImage'])->name('delete-image');
    });

    // TikTok File Upload Routes
    Route::prefix('api/tiktok-files')->group(function () {
        Route::post('/upload', [App\Http\Controllers\TikTokFileUploadController::class, 'uploadFile'])->name('tiktok.files.upload');
        Route::post('/upload-multiple', [App\Http\Controllers\TikTokFileUploadController::class, 'uploadMultipleFiles'])->name('tiktok.files.upload-multiple');
        Route::get('/product/{productId}', [App\Http\Controllers\TikTokFileUploadController::class, 'getProductFiles'])->name('tiktok.files.product');
        Route::get('/template/{templateId}', [App\Http\Controllers\TikTokFileUploadController::class, 'getTemplateFiles'])->name('tiktok.files.template');
        Route::get('/stats', [App\Http\Controllers\TikTokFileUploadController::class, 'getUploadStats'])->name('tiktok.files.stats');
        Route::delete('/{fileId}', [App\Http\Controllers\TikTokFileUploadController::class, 'deleteFile'])->name('tiktok.files.delete');
    });

    // Test route for debugging
    Route::get('/test-attributes/{categoryId}', function ($categoryId) {
        $attributes = App\Models\TikTokCategoryAttribute::where('category_id', $categoryId)
            ->orderBy('is_required', 'desc')
            ->orderBy('name')
            ->get();

        $groupedAttributes = [
            'required' => $attributes->where('is_required', true),
            'optional' => $attributes->where('is_required', false),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'grouped' => $groupedAttributes,
                'total' => $attributes->count(),
                'required_count' => $attributes->where('is_required', true)->count(),
                'optional_count' => $attributes->where('is_required', false)->count(),
            ]
        ]);
    });
});

require __DIR__ . '/auth.php';
