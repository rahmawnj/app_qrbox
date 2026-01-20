@props([
    'items' => ['Partner', 'Topup Member'],
    'title' => 'Topup Member',
    'subtitle' => 'Input nominal topup untuk member dari outlet tertentu',
])

@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/select-picker/dist/picker.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />

    <style>
        /* Override Select2 default styling for better integration */
        .select2-container .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
            /* Match Bootstrap form-control height */
            border-radius: .25rem;
            /* Match Bootstrap form-control border-radius */
            border: 1px solid #ced4da;
            /* Default Bootstrap border color */
            padding: .375rem .75rem;
            /* Match Bootstrap form-control padding */
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + .75rem + 2px);
            /* Vertically align text */
            padding-left: 0;
            /* Remove default padding */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem + 2px);
            /* Match height */
            right: 5px;
            /* Adjust arrow position */
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
            /* Match Bootstrap placeholder color */
        }

        /* Autocomplete styling for better look */
        .ui-autocomplete {
            max-height: 250px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1051;
            /* Pastikan di atas modal atau elemen lain */
            border: 1px solid #ddd;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            list-style: none;
            padding: 0;
            margin: 0;
            border-radius: .25rem;
        }

        .ui-autocomplete .ui-menu-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .ui-autocomplete .ui-menu-item:last-child {
            border-bottom: none;
        }

        .ui-autocomplete .ui-menu-item.ui-state-active,
        .ui-autocomplete .ui-menu-item:hover {
            background-color: #f8f9fa;
            /* Light background on hover */
            color: #007bff;
            /* Primary color on hover */
        }

        /* Custom styling for POS-like form */
        .pos-form-section {
            background-color: #f8f9fa;
            /* Light background for sections */
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .pos-info-box {
            background-color: #e9ecef;
            /* Slightly darker for info boxes */
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 1.1em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pos-info-box label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0;
        }

        .pos-info-box .value {
            font-size: 1.3em;
            font-weight: bold;
            color: #212529;
        }

        .pos-nominal-input {
            font-size: 2.5em;
            /* Large font size for nominal input */
            height: auto;
            /* Auto height */
            text-align: right;
            padding: 15px 20px;
            font-weight: bold;
            color: #007bff;
            /* Highlight primary color */
            border: 2px solid #007bff;
            background-color: #fff;
        }

        .pos-nominal-input::placeholder {
            color: #adb5bd;
            font-weight: normal;
        }

        .pos-total-display {
            background-color: #d4edda;
            /* Light green for success/total */
            color: #155724;
            /* Dark green text */
            padding: 20px 25px;
            border-radius: 8px;
            font-size: 1.8em;
            /* Large font for total */
            font-weight: bold;
            text-align: center;
            margin-top: 25px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
            /* Subtle shadow for total */
        }

        .pos-total-display label {
            display: block;
            font-size: 0.7em;
            font-weight: normal;
            margin-bottom: 5px;
            color: #212529;
        }

        .form-label {
            font-weight: 600;
            color: #343a40;
        }

        /* Ensure Select2 dropdown respects panel z-index */
        .select2-container--open {
            z-index: 1050;
            /* Adjust as needed to be above other elements */
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Form Topup Member</h4> {{-- Ubah judul panel agar lebih spesifik --}}
        </div>
        <div class="panel-body">
            {{-- Tampilkan pesan error atau success jika ada --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success mb-3">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('partner.topup.store', ['out' => request()->get('out')]) }}" method="POST"
                id="topupForm">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="pos-form-section">
                            <h5 class="mb-4 text-primary"><i class="fas fa-user-tag me-2"></i> Detail Transaksi</h5>

                            <div class="mb-3">
                                <label for="cashier_name" class="form-label">Nama Kasir</label>
                                <input type="text" class="form-control rounded-lg" id="cashier_name" name="cashier_name"
                                    placeholder="Masukkan nama kasir" readonly value="{{ Auth::user()->name }}">
                            </div>

                            <div class="mb-3">
                                <label for="outletSelect" class="form-label">Pilih Outlet</label>
                                <select class="form-control" id="outletSelect" name="out">
                                    <option value="">-- Pilih Outlet --</option>
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->code }}"
                                            {{ request()->get('out') == $outlet->code ? 'selected' : '' }}>
                                            {{ $outlet->outlet_name }} - ({{ $outlet->code }}) - {{ $outlet->timezone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="member-search" class="form-label">Pilih Member</label>
                                <select class="selectpicker form-control" id="member-search" name="member_id"
                                    data-live-search="true">
                                    <option value="">-- Pilih Member --</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}"
                                            data-subs-amount="{{ $member->pivot->amount }}">
                                            {{ $member->user->name }} | {{ $member->user->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="pos-form-section h-100 d-flex flex-column">
                            <h5 class="mb-4 text-primary"><i class="fas fa-wallet me-2"></i> Informasi Saldo & Topup</h5>

                            {{-- Member Details Info Box --}}
                            <div id="memberDetails" class="pos-info-box" style="display: none;">
                                <div>
                                    <label>Nama Member:</label>
                                    <span id="memberName" class="value"></span>
                                </div>
                                <div>
                                    <label>Saldo Awal:</label>
                                    <span id="currentBalance" class="value"></span>
                                </div>
                            </div>

                            <div class="mb-3 flex-grow-1 d-flex flex-column justify-content-center"> {{-- Menempatkan input nominal di tengah --}}
                                <label for="nominal" class="form-label text-center">Nominal Topup</label>
                                <input type="number" class="form-control pos-nominal-input" id="nominal" name="nominal"
                                    placeholder="0" disabled>
                            </div>

                            {{-- Total Calculation Display --}}
                            <div id="calculation" class="pos-total-display" style="display:none;">
                                <label>Total Saldo Baru:</label>
                                <span id="calculationText" class="d-block"></span>
                            </div>

                            <div class="mt-4 text-center">
                                <button type="submit" class="btn btn-primary btn-lg w-75" id="submitBtn" disabled>
                                    <i class="fas fa-money-check-alt me-2"></i> Proses Topup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/select-picker/dist/picker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $(".selectpicker").select2({
                placeholder: "-- Pilih Member --",
                allowClear: true // Opsi untuk menghapus pilihan yang sudah ada
            });

            // Fungsi untuk mengaktifkan/menonaktifkan tombol submit
            function toggleSubmit() {
                let memberSelected = $('#member-search').val() !== "";
                let nominalValue = parseFloat($('#nominal').val()) || 0;
                let nominalFilledAndPositive = nominalValue > 0;

                if (memberSelected && nominalFilledAndPositive) {
                    $('#submitBtn').prop('disabled', false);
                } else {
                    $('#submitBtn').prop('disabled', true);
                }
            }

            // Fungsi untuk memperbarui perhitungan total
            function updateCalculation() {
                let selectedOption = $('#member-search').find(':selected');
                let subsAmount = selectedOption.data('subs-amount') || 0;
                subsAmount = parseFloat(subsAmount) || 0;
                let nominal = parseFloat($('#nominal').val()) || 0;
                let total = subsAmount + nominal;

                if (nominal > 0 || selectedOption.val() !==
                    "") { // Tampilkan total jika ada nominal atau member terpilih
                    $('#calculationText').text("Rp " + total.toLocaleString('id-ID'));
                    $('#calculation').show();
                } else {
                    $('#calculation').hide();
                }
            }

            // Event listener untuk perubahan pada select member
            $('#member-search').on('change', function() {
                console.log("Perubahan terdeteksi pada member:", $(this).val());
                let selectedOption = $(this).find(':selected');
                if ($(this).val() !== "") {
                    $('#memberDetails').show();
                    // Pastikan teks member name diambil dari teks option, bukan dari value (id)
                    // Ambil teks setelah memilih, atau simpan di data attribute
                    $('#memberName').text(selectedOption.text().split('(')[0].trim()); // Ambil nama saja
                    let subsAmount = selectedOption.data('subs-amount') || 0;
                    $('#currentBalance').text("Rp " + parseFloat(subsAmount).toLocaleString('id-ID'));
                    $('#nominal').prop('disabled', false).focus(); // Auto-focus ke nominal input
                } else {
                    $('#memberDetails').hide();
                    $('#nominal').val('').prop('disabled', true); // Kosongkan dan nonaktifkan nominal
                    $('#calculation').hide();
                }
                toggleSubmit();
                updateCalculation();
            });

            // Event listener untuk perubahan pada input nominal
            $('#nominal').on('input', function() {
                console.log("Nominal diubah:", $(this).val());
                toggleSubmit();
                updateCalculation();
            });

            // Event listener untuk perubahan pada select outlet
            $('#outletSelect').on('change', function() {
                let selectedOutletCode = $(this).val();
                let currentUrl = new URL(window.location.href);
                if (selectedOutletCode) {
                    currentUrl.searchParams.set('out', selectedOutletCode);
                } else {
                    currentUrl.searchParams.delete('out');
                }
                window.location.href = currentUrl
            .toString(); // Refresh halaman dengan parameter outlet baru
            });


            // Inisialisasi awal saat halaman dimuat
            // Ini akan memastikan form berada pada state yang benar berdasarkan pilihan default atau URL
            // Jika ada outlet yang dipilih dari URL, panggil trigger change untuk Select2
            if ($('#outletSelect').val()) {
                // Member-search dan nominal akan di-handle setelah outlet dipilih,
                // jadi trigger change di #member-search setelahnya jika ada member terpilih.
                // Jika tidak ada member yang terpilih secara default, cukup panggil toggleSubmit/updateCalculation
                // untuk inisialisasi state awal form (misal: tombol submit disabled)
            }

            // Trigger change pada member-search saat load jika ada member yang sudah terpilih (misal dari old() input)
            if ($('#member-search').val() !== "") {
                $('#member-search').trigger('change');
            } else {
                // Jika tidak ada member terpilih di awal, pastikan nominal dan submit button disabled
                $('#nominal').prop('disabled', true);
                $('#submitBtn').prop('disabled', true);
                $('#memberDetails').hide();
                $('#calculation').hide();
            }

            toggleSubmit(); // Panggil di awal untuk memastikan tombol submit disabled jika form kosong
            updateCalculation(); // Panggil di awal untuk menyembunyikan perhitungan jika tidak ada nominal
        });
    </script>
@endpush
