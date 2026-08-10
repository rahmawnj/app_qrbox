   <div class="menu-header">Navigasi Admin</div>
                <div class="menu-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-tachometer-alt"></i></div>
                        <div class="menu-text">Dashboard</div>
                    </a>
                </div>
                {{-- <div class="menu-item {{ Request::is('admin/accounts') ? 'active' : '' }}">
                    <a href="{{ route('admin.accounts.all') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-user"></i></div>
                        <div class="menu-text">Akun</div>
                    </a>
                </div> --}}



                <div class="menu-header">Master Data</div>
                {{-- Menu Pengguna --}}


                {{-- Menu Master Data --}}
                <div
                    class="menu-item has-sub {{ Request::is('admin/outlets*') || Request::is('admin/devices*') || Request::is('admin/service_types*') || Request::is('admin/services*') || Request::is('admin/addons*') ? 'active' : '' }}">
                    <a href="javascript:;" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-database"></i></div>
                        <div class="menu-text">Master Data</div>
                        <div class="menu-caret"></div>
                    </a>
                    <div class="menu-submenu">
                         <div class="menu-item {{ Request::is('admin/owners*') ? 'active' : '' }}">
                            <a href="{{ route('admin.owners.index') }}" class="menu-link">
                                <div class="menu-text">Pemilik (Partner)</div>
                            </a>
                        </div>
                        <div class="menu-item {{ Request::is('admin/outlets*') ? 'active' : '' }}">
                            <a href="{{ route('admin.outlets.index') }}" class="menu-link">
                                <div class="menu-text">Outlet</div>
                            </a>
                        </div>
                        <div class="menu-item {{ Request::is('admin/devices*') ? 'active' : '' }}">
                            <a href="{{ route('admin.devices.index') }}" class="menu-link">
                                <div class="menu-text">Perangkat</div>
                            </a>
                        </div>
                        <div class="menu-item {{ Request::is('admin/service_types*') ? 'active' : '' }}">
                            <a href="{{ route('admin.service_types.index') }}" class="menu-link">
                                <div class="menu-text">Jenis Layanan</div>
                            </a>
                        </div>
                        {{-- <div class="menu-item {{ Request::is('admin/services*') ? 'active' : '' }}">
                            <a href="{{ route('admin.services.index') }}" class="menu-link">
                                <div class="menu-text">Layanan</div>
                            </a>
                        </div>
                        <div class="menu-item {{ Request::is('admin/addons*') ? 'active' : '' }}">
                            <a href="{{ route('admin.addons.index') }}" class="menu-link">
                                <div class="menu-text">Add-ons</div>
                            </a>
                        </div> --}}
                    </div>
                </div>

                <div class="menu-header">Transaksi & Keuangan</div>
                {{-- <div
                    class="menu-item has-sub {{ Request::routeIs('admin.transactions.*') || Request::routeIs('admin.payments.*') ? 'active' : '' }}">
                    <a href="javascript:;" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-receipt"></i></div>
                        <div class="menu-text">Transaksi</div>
                        <div class="menu-caret"></div>
                    </a>
                    <div class="menu-submenu">
                        <div class="menu-item {{ Request::routeIs('admin.transactions.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.transactions.index') }}" class="menu-link">
                                <div class="menu-text">Semua Transaksi</div>
                            </a>
                        </div>
                        <div
                            class="menu-item has-sub {{ Request::routeIs('admin.transactions.self-service.*') ? 'active' : '' }}">
                            <a href="javascript:;" class="menu-link">
                                <div class="menu-text">Self-Service</div>
                                <div class="menu-caret"></div>
                            </a>
                            <div class="menu-submenu">
                                <div
                                    class="menu-item {{ Request::routeIs('admin.transactions.self-service.member') ? 'active' : '' }}">
                                    <a href="{{ route('admin.transactions.self-service.member') }}"
                                        class="menu-link">
                                        <div class="menu-text">Member</div>
                                    </a>
                                </div>
                                <div
                                    class="menu-item {{ Request::routeIs('admin.transactions.self-service.non-member') ? 'active' : '' }}">
                                    <a href="{{ route('admin.transactions.self-service.non-member') }}"
                                        class="menu-link">
                                        <div class="menu-text">Non-Member</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div
                            class="menu-item has-sub {{ Request::routeIs('admin.transactions.drop-off.*') ? 'active' : '' }}">
                            <a href="javascript:;" class="menu-link">
                                <div class="menu-text">Drop-Off</div>
                                <div class="menu-caret"></div>
                            </a>
                            <div class="menu-submenu">
                                <div
                                    class="menu-item {{ Request::routeIs('admin.transactions.drop-off.member') ? 'active' : '' }}">
                                    <a href="{{ route('admin.transactions.drop-off.member') }}" class="menu-link">
                                        <div class="menu-text">Member</div>
                                    </a>
                                </div>
                                <div
                                    class="menu-item {{ Request::routeIs('admin.transactions.drop-off.non-member') ? 'active' : '' }}">
                                    <a href="{{ route('admin.transactions.drop-off.non-member') }}"
                                        class="menu-link">
                                        <div class="menu-text">Non-Member</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="menu-item {{ Request::routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.transactions.index') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-receipt"></i></div>
                        <div class="menu-text">Transaksi</div>
                    </a>
                </div>
                <div class="menu-item {{ Request::routeIs('admin.payments.history') ? 'active' : '' }}">
                    <a href="{{ route('admin.payments.history') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-history"></i></div>
                        <div class="menu-text">Riwayat Pembayaran</div>
                    </a>
                </div>

                {{-- <div class="menu-item {{ Request::routeIs('admin.topup.histories') ? 'active' : '' }}">
                    <a href="{{ route('admin.topup.histories') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-wallet"></i></div>
                        <div class="menu-text">Riwayat Topup</div>
                    </a>
                </div> --}}

                @php
                    $adminWithdrawalRequest = App\Models\Withdrawal::with('owner.user')
                        ->count();
                        $totalWithdrawNotif = $adminWithdrawalRequest;
                @endphp
                <div class="menu-item has-sub {{ Request::is('admin/withdrawal*') ? 'active' : '' }}">
                    <a href="javascript:;" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-coins"></i></div>
                        <div class="menu-text">
    Penarikan Dana {!! $totalWithdrawNotif > 0 ? '<span class="menu-label">'.$adminWithdrawalRequest.'</span>' : '' !!}
</div>

                        <div class="menu-caret"></div>
                    </a>
                    <div class="menu-submenu">
                        <div class="menu-item {{ Request::routeIs('admin.withdrawal.list') ? 'active' : '' }}">
                            <a href="{{ route('admin.withdrawal.list') }}" class="menu-link">
                                <div class="menu-text">Permintaan Penarikan</div>
                                @if ($adminWithdrawalRequest > 0)
                                    <div class="menu-badge">{{ $adminWithdrawalRequest }}</div>
                                @endif
                            </a>
                        </div>
                        <div class="menu-item {{ Request::routeIs('admin.withdrawal.histories') ? 'active' : '' }}">
                            <a href="{{ route('admin.withdrawal.histories') }}" class="menu-link">
                                <div class="menu-text">Riwayat Penarikan</div>
                            </a>
                        </div>
                        {{-- <div class="menu-item {{ Request::routeIs('admin.qris.history') ? 'active' : '' }}">
                            <a href="{{ route('admin.qris.history') }}" class="menu-link">
                                <div class="menu-text">Riwayat QRIS</div>
                            </a>
                        </div> --}}
                    </div>
                </div>

                <div class="menu-header">Pengaturan Sistem & Log</div>
                {{-- <div class="menu-item {{ Request::is('admin/setting') ? 'active' : '' }}">
                    <a href="{{ route('admin.setting.form') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-cogs"></i></div>
                        <div class="menu-text">Pengaturan Sistem</div>
                    </a>
                </div> --}}
                <div class="menu-item {{ Request::is('admin/bypass/logs') ? 'active' : '' }}">
                    <a href="{{ route('admin.bypass.logs') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-file-alt"></i></div>
                        <div class="menu-text">Log Bypass</div>
                    </a>
                </div>
