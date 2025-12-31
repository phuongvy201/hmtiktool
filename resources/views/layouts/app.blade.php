<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TikTool - Partner Tool</title>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Navigation Styles - with fallbacks */
        .nav-link {
            color: #d1d5db;
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .nav-link:hover {
            color: #ffffff;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: #334155;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 50;
        }
        
        .group:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #d1d5db;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #475569;
            color: #ffffff;
        }
        
        .mobile-menu-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            color: #d1d5db;
            border-radius: 0.375rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        
        .mobile-menu-item:hover {
            background-color: #475569;
            color: #ffffff;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
        
        /* Ensure navigation is always visible */
        nav {
            background-color: #1e293b !important;
            border-bottom: 1px solid #475569 !important;
        }
        
        /* Logo styling */
        nav a[href="/"] {
            color: #ffffff !important;
            font-size: 1.25rem !important;
            font-weight: 700 !important;
        }
        
        nav a[href="/"]:hover {
            color: #93c5fd !important;
        }
    </style>
    @yield('head')
</head>

<body class="bg-slate-900 font-product-sans min-h-screen">
    <nav class="bg-slate-800 border-b border-slate-700">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-white hover:text-blue-300 transition-colors duration-200">
                        HMTik
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Guest Navigation -->
                    @guest
                        <a href="/" class="nav-link">
                            <i class="fas fa-house mr-2 text-sm w-4"></i>
                            Home
                        </a>
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="fas fa-right-to-bracket mr-2 text-sm w-4"></i>
                            Log in
                        </a>
                    @endguest
                    
                    @auth
                        <!-- Dashboard -->
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="fas fa-gauge mr-2 text-sm w-4"></i>
                            Dashboard
                        </a>

                        <!-- Management Dropdown - Only show if user has at least one visible item -->
                        @php
                            $hasManagementItems = false;
                            if (auth()->user()->can('view-users') && !auth()->user()->hasRole('seller')) {
                                $hasManagementItems = true;
                            }
                            if (auth()->user()->can('view-teams') && !auth()->user()->hasRole('seller')) {
                                $hasManagementItems = true;
                            }
                            if (auth()->user()->hasRole('system-admin')) {
                                $hasManagementItems = true;
                            }
                            if (auth()->user()->can('view-service-packages')) {
                                $hasManagementItems = true;
                            }
                        @endphp
                        
                        @if($hasManagementItems)
                        <div class="relative group">
                            <button class="nav-link">
                                <i class="fas fa-sliders mr-2 text-sm w-4"></i>
                                Management
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="dropdown-menu">
                                <div class="py-2">
                                    @can('view-users')
                                    @unless(auth()->user()->hasRole('seller'))
                                    <a href="{{ auth()->user()->hasRole('team-admin') ? route('team-admin.users.index') : route('users.index') }}" class="dropdown-item">
                                        <i class="fas fa-users mr-2 w-4 text-sm"></i>
                                        Users
                                    </a>
                                    @endunless
                                    @endcan
                                    
                                    @can('view-teams')
                                    @unless(auth()->user()->hasRole('seller') || auth()->user()->hasRole('team-admin'))
                                    <a href="{{ route('teams.index') }}" class="dropdown-item">
                                        <i class="fas fa-people-group mr-2 w-4 text-sm"></i>
                                        Teams
                                    </a>
                                    @endunless
                                    @endcan
                                    
                                    @if(auth()->user()->hasRole('system-admin'))
                                    <a href="{{ route('roles.index') }}" class="dropdown-item">
                                        <i class="fas fa-shield-halved mr-2 w-4 text-sm"></i>
                                        Roles
                                    </a>
                                    @endif
                                    
                                    @can('view-service-packages')
                                    <a href="{{ route('service-packages.index') }}" class="dropdown-item">
                                        <i class="fas fa-box-open mr-2 w-4 text-sm"></i>
                                        Service Packages
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Products Dropdown -->
                        @canany(['view-products', 'view-product-templates'])
                        <div class="relative group">
                            <button class="nav-link">
                                <i class="fas fa-boxes-stacked mr-2 text-sm w-4"></i>
                                Products
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="dropdown-menu">
                                <div class="py-2">
                                    @can('view-product-templates')
                                    <a href="{{ route('product-templates.index') }}" class="dropdown-item">
                                        <i class="fas fa-layer-group mr-2 w-4 text-indigo-400"></i>
                                        Templates
                                    </a>
                                    @endcan
                                    
                                    @can('view-products')
                                    <a href="{{ route('products.index') }}" class="dropdown-item">
                                        <i class="fas fa-box mr-2 w-4 text-blue-400"></i>
                                        Product List
                                    </a>
                                    @endcan
                                    
                                    @can('create-products')
                                    <a href="{{ route('products.create') }}" class="dropdown-item">
                                        <i class="fas fa-plus mr-2 w-4 text-green-400"></i>
                                        Create Product
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endcanany

                        <!-- TikTok Shop -->
                        @if(auth()->user()->hasRole('system-admin'))
                        <a href="{{ route('tiktok-shop.index') }}" class="nav-link">
                            <i class="fab fa-tiktok mr-2 text-sm w-4"></i>
                            TikTok Shop
                        </a>
                        @elseif(auth()->user()->hasRole('team-admin'))
                        <a href="{{ route('team.tiktok-shop.index') }}" class="nav-link">
                            <i class="fab fa-tiktok mr-2 text-sm w-4"></i>
                            Connect TikTok
                        </a>
                        @endif

                        <!-- TikTok Analytics Dropdown -->
                        <div class="relative group">
                            <button class="nav-link">
                                <i class="fas fa-chart-line mr-2 text-sm w-4"></i>
                                Analytics
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="dropdown-menu">
                                <div class="py-2">
                                    <a href="{{ route('tiktok.analytics.index') }}" class="dropdown-item">
                                        <i class="fas fa-chart-line mr-2 w-4 text-purple-400"></i>
                                        Shop Analytics
                                    </a>
                                    <a href="{{ route('tiktok.finance.index') }}" class="dropdown-item">
                                        <i class="fas fa-coins mr-2 w-4 text-yellow-400"></i>
                                        Finance
                                    </a>
                                    <a href="{{ route('tiktok.performance.index') }}" class="dropdown-item">
                                        <i class="fas fa-chart-area mr-2 w-4 text-pink-400"></i>
                                        Performance
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Orders -->
                        <a href="{{ route('tiktok.orders.index') }}" class="nav-link">
                            <i class="fas fa-cart-shopping mr-2 text-sm w-4"></i>
                            Orders
                        </a>
                    @endauth
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- User Profile Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center text-gray-300 hover:text-white transition-colors duration-200">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-2 overflow-hidden">
                                    @if(auth()->user()->hasAvatar())
                                        <img src="{{ auth()->user()->avatar_url }}" 
                                             alt="{{ auth()->user()->display_name }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-blue-500/20 flex items-center justify-center">
                                            <span class="text-blue-400 font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <span class="hidden md:block">{{ auth()->user()->display_name }}</span>
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute top-full right-0 mt-2 w-48 bg-slate-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="py-2">
                                    <div class="px-4 py-2 border-b border-slate-600">
                                        <p class="text-sm text-gray-300">{{ auth()->user()->email }}</p>
                                        <p class="text-xs text-gray-400">{{ auth()->user()->primary_role_name }}</p>
                                    </div>
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-300 hover:bg-slate-600 hover:text-white transition-colors duration-200">
                                        <i class="fas fa-user mr-2 w-4 text-sm"></i>
                                        Profile
                                    </a>
                                    @can('view-system-settings')
                                    <a href="{{ route('system.settings') }}" class="block px-4 py-2 text-gray-300 hover:bg-slate-600 hover:text-white transition-colors duration-200">
                                        <i class="fas fa-gear mr-2 w-4 text-sm"></i>
                                        System Settings
                                    </a>
                                    @endcan
                                    <div class="border-t border-slate-600 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-gray-300 hover:bg-slate-600 hover:text-white transition-colors duration-200">
                                            <i class="fas fa-right-from-bracket mr-2 w-4 text-sm"></i>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition-colors duration-200">Login</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">Register</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden bg-slate-700 border-t border-slate-600">
                <div class="px-4 py-3 space-y-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="mobile-menu-item">
                            <i class="fas fa-gauge mr-2 text-sm w-4"></i>
                            Dashboard
                        </a>

                        @php
                            $hasManagementItems = false;
                            if (auth()->user()->can('view-users') && !auth()->user()->hasRole('seller')) {
                                $hasManagementItems = true;
                            }
                            if (auth()->user()->can('view-teams') && !auth()->user()->hasRole('seller')) {
                                $hasManagementItems = true;
                            }
                            if (auth()->user()->hasRole('system-admin')) {
                                $hasManagementItems = true;
                            }
                            if (auth()->user()->can('view-service-packages')) {
                                $hasManagementItems = true;
                            }
                        @endphp
                        
                        @if($hasManagementItems)
                        <div class="border-t border-slate-600 pt-2 mt-2">
                            <div class="text-gray-400 text-xs uppercase tracking-wider mb-2">Management</div>
                            @can('view-users')
                            @unless(auth()->user()->hasRole('seller'))
                            <a href="{{ auth()->user()->hasRole('team-admin') ? route('team-admin.users.index') : route('users.index') }}" class="mobile-menu-item">
                                <i class="fas fa-users mr-2 w-4 text-sm"></i>
                                Users
                            </a>
                            @endunless
                            @endcan
                            
                            @can('view-teams')
                            @unless(auth()->user()->hasRole('seller'))
                            <a href="{{ route('teams.index') }}" class="mobile-menu-item">
                                <i class="fas fa-people-group mr-2 w-4 text-sm"></i>
                                Teams
                            </a>
                            @endunless
                            @endcan
                            
                            @if(auth()->user()->hasRole('system-admin'))
                            <a href="{{ route('roles.index') }}" class="mobile-menu-item">
                                <i class="fas fa-shield-halved mr-2 w-4 text-sm"></i>
                                Roles
                            </a>
                            @endif
                            
                            @can('view-service-packages')
                            <a href="{{ route('service-packages.index') }}" class="mobile-menu-item">
                                <i class="fas fa-box-open mr-2 w-4 text-sm"></i>
                                Service Packages
                            </a>
                            @endcan
                        </div>
                        @endif

                        @canany(['view-products', 'view-product-templates'])
                        <div class="border-t border-slate-600 pt-2 mt-2">
                            <div class="text-gray-400 text-xs uppercase tracking-wider mb-2">Products</div>
                            @can('view-product-templates')
                            <a href="{{ route('product-templates.index') }}" class="mobile-menu-item">
                                <i class="fas fa-layer-group mr-2 w-4 text-indigo-400"></i>
                                Templates
                            </a>
                            @endcan
                            
                            @can('view-products')
                            <a href="{{ route('products.index') }}" class="mobile-menu-item">
                                <i class="fas fa-box mr-2 w-4 text-blue-400"></i>
                                Products
                            </a>
                            @endcan
                            
                    
                        </div>
                        @endcanany

                        <div class="border-t border-slate-600 pt-2 mt-2">
                            <div class="text-gray-400 text-xs uppercase tracking-wider mb-2">TikTok</div>
                            @if(auth()->user()->hasRole('system-admin'))
                            <a href="{{ route('tiktok-shop.index') }}" class="mobile-menu-item">
                                <i class="fab fa-tiktok mr-2 w-4 text-sm"></i>
                                TikTok Shop
                            </a>
                            @elseif(auth()->user()->hasRole('team-admin'))
                            <a href="{{ route('team.tiktok-shop.index') }}" class="mobile-menu-item">
                                <i class="fab fa-tiktok mr-2 w-4 text-sm"></i>
                                Connect TikTok
                            </a>
                            @endif

                            <a href="{{ route('tiktok.analytics.index') }}" class="mobile-menu-item">
                                <i class="fas fa-chart-line mr-2 w-4 text-purple-400"></i>
                                Shop Analytics
                            </a>
                            <a href="{{ route('tiktok.finance.index') }}" class="mobile-menu-item">
                                <i class="fas fa-coins mr-2 w-4 text-yellow-400"></i>
                                Finance
                            </a>
                            <a href="{{ route('tiktok.performance.index') }}" class="mobile-menu-item">
                                <i class="fas fa-chart-area mr-2 w-4 text-pink-400"></i>
                                Performance
                            </a>
                            <a href="{{ route('tiktok.orders.index') }}" class="mobile-menu-item">
                                <i class="fas fa-cart-shopping mr-2 w-4 text-sm"></i>
                                Orders
                            </a>
                        </div>

                        <div class="border-t border-slate-600 pt-2 mt-2">
                            <div class="text-gray-400 text-xs uppercase tracking-wider mb-2">Account</div>
                            <a href="{{ route('profile.edit') }}" class="mobile-menu-item">
                                <i class="fas fa-user mr-2 w-4 text-sm"></i>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="mobile-menu-item w-full text-left">
                                    <i class="fas fa-right-from-bracket mr-2 w-4 text-sm"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="mobile-menu-item">
                            <i class="fas fa-right-to-bracket mr-2 text-sm w-4"></i>
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="mobile-menu-item bg-blue-600 text-white">
                            <i class="fas fa-user-plus mr-2 text-sm w-4"></i>
                            Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-[calc(100vh-4rem)]">
        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </div>
    </main>
    
    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    const isHidden = mobileMenu.classList.contains('hidden');
                    
                    if (isHidden) {
                        mobileMenu.classList.remove('hidden');
                        menuIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
                    } else {
                        mobileMenu.classList.add('hidden');
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    }
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('hidden');
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    }
                });
                
                // Close mobile menu when window is resized to desktop
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 768) {
                        mobileMenu.classList.add('hidden');
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>

</html> 