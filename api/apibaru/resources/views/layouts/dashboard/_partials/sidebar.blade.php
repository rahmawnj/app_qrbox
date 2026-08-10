<div id="sidebar" class="app-sidebar" style="background-color: rgb(227, 243, 255)">
    <!-- BEGIN scrollbar -->
    <div class="app-sidebar-content" data-scrollbar="true" data-height="100%">
        <!-- BEGIN menu -->
        <div class="menu">
            <div class="menu-profile">
                <a href="javascript:;" class="menu-profile-link" data-toggle="app-sidebar-profile"
                    data-target="#appSidebarProfileMenu">
                    <div class="menu-profile-cover with-shadow"></div>
                    <div class="menu-profile-image">
                        <img src="{{ asset(Auth::user()->image ?? 'assets/img/default-user.png') }}" alt="" />
                    </div>
                    <div class="menu-profile-info">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="menu-caret ms-auto"></div>
                        </div>
                        <small>
                            {{ Auth::user()->email }}
                        </small>
                    </div>
                </a>
            </div>

            <div id="appSidebarProfileMenu" class="collapse">
                <div class="menu-item pt-5px">
                    <a href="{{ route('profile.form') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-cog"></i></div>
                        <div class="menu-text">Profile</div>
                    </a>
                </div>

                {{-- <div class="menu-item pb-5px">
                    <a href="javascript:;" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-question-circle"></i></div>
                        <div class="menu-text"> Helps</div>
                    </a>
                </div> --}}

                <div class="menu-divider m-0"></div>
            </div>

          @php
    // Cek apakah user login di guard admin_config (Super Admin)
    $isAdminConfig = Auth::guard('admin_config')->check();

    // Cek apakah user login di guard web (Owner/Cashier/Member)
    $isWebUser = Auth::guard('web')->check();

    // Ambil data user dari guard yang sedang aktif
    $user = $isAdminConfig ? Auth::guard('admin_config')->user() : Auth::guard('web')->user();
@endphp

@if ($isAdminConfig)
    {{-- Menu Khusus Super Admin (Hardcoded/Config) --}}
    @include('layouts.dashboard._partials.menu_admin')

@elseif ($isWebUser)
    {{-- @if ($user->role === 'owner' || $user->role === 'cashier') --}}
        @include('layouts.dashboard._partials.menu_partner') {{-- Pastikan nama file partialnya beda jika menunya beda --}}
    {{-- @elseif ($user->role === 'member')
        @include('layouts.dashboard._partials.menu_member')
    @endif --}}
@endif
            <div class="menu-item d-flex">
                <a href="javascript:;" class="app-sidebar-minify-btn ms-auto" data-toggle="app-sidebar-minify"><i
                        class="fa fa-angle-double-left"></i></a>
            </div>
        </div>
    </div>
</div>
