<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">

    <!-- Brand Logo Light -->
    <a href="{{ route('any', 'index') }}" class="logo logo-light">
        <span class="logo-lg">
            <img src="/images/logo.png" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="/images/logo-sm.png" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="{{ route('any', 'index') }}" class="logo logo-dark">
        <span class="logo-lg">
            <img src="/images/logo-dark.png" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="/images/logo-sm.png" alt="small logo">
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <!-- Full Sidebar Menu Close Button -->
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!-- Leftbar User -->
        <div class="leftbar-user">
            <a href="{{ route('second', ['pages', 'profile']) }}">
                <img src="/images/users/avatar-1.jpg" alt="user-image" height="42" class="rounded-circle shadow-sm">
                <span class="leftbar-user-name mt-2">Tosha Minner</span>
            </a>
        </div>

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title">Analytics</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarDashboards" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                    <i class="ri-dashboard-line"></i>
                    <span> Dashboard </span>
                </a>
                <div class="collapse" id="sidebarDashboards">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('any', 'analytics') }}">Analytics</a>
                        </li>
                        <li>
                            <a href="{{ route('any', 'index') }}">Index</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title">E Commerce</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarMultiLevel" aria-expanded="false" aria-controls="sidebarMultiLevel" class="side-nav-link">
                    <i class="bi bi-stripe"></i>
                    <span> Shopee </span>
                    <span class="menu-arrow"></span>
                  </a>
                <div class="collapse" id="sidebarMultiLevel">
                    <ul class="side-nav-second-level">
                        <li class="side-nav-item">
                            <a data-bs-toggle="collapse" href="#sidebarBrandPortal" aria-expanded="false" aria-controls="sidebarSecondLevel">
                                <span> Brand Portal </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarBrandPortal">
                                <ul class="side-nav-third-level">
                                    <li>
                                        <a href="{{ route('second', ['shopee', 'brand-portal-shop']) }}">Shop</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('second', ['shopee', 'brand-portal-ads']) }}">Ads</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="side-nav-item">
                            <a data-bs-toggle="collapse" href="#sidebarSellerCenter" aria-expanded="false" aria-controls="sidebarSecondLevel">
                                <span> Seller Center </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarSellerCenter">
                                <ul class="side-nav-third-level">
                                    <li>
                                        <a href="{{ route('second', ['shopee', 'brand-portal-shop']) }}">Live Streaming</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('second', ['shopee', 'brand-portal-ads']) }}">Voucher</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('second', ['shopee', 'brand-portal-ads']) }}">Coin</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarTiktok" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                    <i class="ri-tiktok-line"></i>
                    <span> Tiktok </span>
                </a>
                <div class="collapse" id="sidebarTiktok">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">GMV</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">Live Streaming</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">Product Card</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">Product Analysis</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">Promotion</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">Video</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">LSA</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">PSA</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['test', 'test']) }}">VSA</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarDashboards" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                    <i class="ri-shopping-cart-line"></i>
                    <span> Tokopedia </span>
                </a>
                <div class="collapse" id="sidebarDashboards">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">Statistic</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">Products</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">TopAds</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">Tokopedia Play</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">Trafic Statistic</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">Operational Statistic</a>
                        </li>
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">Buyer Statistic</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarMeta" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                    <i class="ri-meta-line"></i>
                    <span> Meta </span>
                </a>
                <div class="collapse" id="sidebarMeta">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">CPAS</a>
                        </li>
                    </ul>
                </div>
            </li>


            <li class="side-nav-title">System</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarRawData" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                    <i class="ri-database-2-line"></i>
                    <span> Raw Data </span>
                </a>
                <div class="collapse" id="sidebarRawData">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('second', ['raw-data', 'index']) }}">Data</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarLog" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                    <i class="ri-bill-line"></i>
                    <span> Log </span>
                </a>
                <div class="collapse" id="sidebarLog">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('second', ['raw-data', 'test']) }}">Monitoring</a>
                        </li>
                    </ul>
                </div>
            </li>

            

        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
