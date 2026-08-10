  <div class="menu-header">Navigasi Mitra</div>

    <div class="menu-item {{ Request::is('partner/dashboard') ? 'active' : '' }}">
        <a href="{{ route('partner.dashboard') }}" class="menu-link">
            <div class="menu-icon"><i class="fa fa-chart-line"></i></div>
            <div class="menu-text">Dashboard</div>
        </a>
    </div>

    @php
        // $unverifiedMembersCount = getBrand()->members()->wherePivot('is_verified', 0)->count();
        // $totalMemberManagement = $unverifiedMembersCount;
    @endphp



            {{-- @php
            $dropOffTransactions = App\Models\DropOffTransaction::query()
                        ->whereHas('transaction', function ($query) {
                            $query->where('status', 'pending');
                            $query->whereHas('payments', function ($q) {
                                $q->where('status', 'success');
                            });
                        })
                        ->with('transaction')
                        ->count();
                        $totalDropOff = $dropOffTransactions;
            @endphp --}}


    <div class="menu-header">Laporan & Aktivitas Mitra</div>
                <div class="menu-item {{ Request::routeIs('partner.transactions.*') ? 'active' : '' }}">
                    <a href="{{ route('partner.transactions.index') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-receipt"></i></div>
                        <div class="menu-text">Transaksi</div>
                    </a>
                </div>

    <div class="menu-item {{ Request::is('partner/payments/history') ? 'active' : '' }}">
        <a href="{{ route('partner.payments.history') }}" class="menu-link">
            <div class="menu-icon"><i class="fa fa-history"></i></div>
            <div class="menu-text">Riwayat Pembayaran</div>
        </a>
    </div>
    <div class="menu-item {{ Request::is('partner/bypass/logs') ? 'active' : '' }}">
        <a href="{{ route('partner.bypass.logs') }}" class="menu-link">
            <div class="menu-icon"><i class="fa fa-clipboard-list"></i></div>
            <div class="menu-text">Log Bypass</div>
        </a>
    </div>

    {{-- Menu Khusus untuk Pemilik --}}
    @if (Auth::user()->role === 'owner')
        <div class="menu-header">Pengaturan Khusus Pemilik</div>

        <div
            class="menu-item has-sub {{ Request::is('partner/withdrawal*') || Request::is('partner/payments/qris') ? 'active' : '' }}">
            <a href="javascript:;" class="menu-link">
                <div class="menu-icon"><i class="fa fa-wallet"></i></div>
                <div class="menu-text">Penarikan Dana</div>
                <div class="menu-caret"></div>
            </a>
            <div class="menu-submenu">
                <div class="menu-item {{ Request::is('partner/withdrawal') ? 'active' : '' }}">
                    <a href="{{ route('partner.withdrawal.request') }}" class="menu-link">
                        <div class="menu-text">Permintaan Penarikan</div>
                    </a>
                </div>
                <div class="menu-item {{ Request::is('partner/withdrawal/histories') ? 'active' : '' }}">
                    <a href="{{ route('partner.withdrawal.histories') }}" class="menu-link">
                        <div class="menu-text">Riwayat Penarikan</div>
                    </a>
                </div>
                {{-- <div class="menu-item {{ Request::is('partner/payments/qris') ? 'active' : '' }}">
                    <a href="{{ route('partner.qris.history') }}" class="menu-link">
                        <div class="menu-text">Riwayat QRIS</div>
                    </a>
                </div> --}}
            </div>
        </div>

        <div class="menu-item {{ Request::is('partner/outlets*') ? 'active' : '' }}">
            <a href="{{ route('partner.outlets.list') }}" class="menu-link">
                <div class="menu-icon"><i class="fa fa-store"></i></div>
                <div class="menu-text">Daftar Outlet</div>
            </a>
        </div>
        {{-- <div class="menu-item {{ Request::is('partner/cashiers*') ? 'active' : '' }}">
            <a href="{{ route('partner.cashiers.list') }}" class="menu-link">
                <div class="menu-icon"><i class="fa fa-cash-register"></i></div>
                <div class="menu-text">Daftar Kasir</div>
            </a>
        </div> --}}
        <div class="menu-item {{ Request::is('partner/devices*') ? 'active' : '' }}">
            <a href="{{ route('partner.device.list') }}" class="menu-link">
                <div class="menu-icon"><i class="fa fa-microchip"></i></div>
                <div class="menu-text">Daftar Perangkat</div>
            </a>
        </div>


        <div class="menu-item {{ Request::is('partner/brand/profile*') ? 'active' : '' }}">
            <a href="{{ route('partner.brand.profile.edit') }}" class="menu-link">
                <div class="menu-icon"><i class="fa fa-building"></i></div>
                <div class="menu-text">Profil Brand</div>
            </a>
        </div>
        <div class="menu-item {{ Request::is('partner/receipt-config*') ? 'active' : '' }}">
            <a href="{{ route('partner.receipt.config.edit') }}" class="menu-link">
                <div class="menu-icon"><i class="fa fa-file-invoice"></i></div>
                <div class="menu-text">Konfigurasi Struk</div>
            </a>
        </div>
    @endif
