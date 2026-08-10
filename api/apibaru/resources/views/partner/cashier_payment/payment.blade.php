@props([
    'items' => ['Partner', 'Kasir', 'Pembayaran Kasir'],
    'title' => 'Pembayaran Kasir',
    'subtitle' => 'Proses pembayaran langsung oleh kasir',
])
@php
    $feature = getData();
@endphp

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Pembayaran Kasir</h4>
        </div>
        <div class="panel-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('partner.cashier.payment.store') }}" method="POST" id="paymentForm">
                @csrf
                <div class="row">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                            @if (session('new_transaction'))
                                <x-print-invoice :transaction="session('new_transaction')" />
                            @endif
                        </div>
                    @endif
                </div>
                <div class="row">
                    {{-- Left Column: Outlet, Service, and Addon Selection --}}
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label for="cashier_name" class="form-label">Nama Kasir</label>
                            <input type="text" class="form-control rounded-lg" id="cashier_name" name="cashier_name"
                                placeholder="Masukkan nama kasir" readonly value="{{ Auth::user()->name }}">
                        </div>

                        {{-- Pickup Method Selection (NEW PLACEMENT) --}}
                       <div class="mb-3">
    <label class="form-label">Metode Pengambilan <span style="color:red">*</span></label>
    <div class="d-flex flex-wrap gap-3" id="pickupMethodRadios">
        <div
            class="form-check form-check-inline pickup-method-card border rounded-lg p-3 shadow-sm bg-white cursor-pointer {{ old('pickup_method', 'pickup') == 'pickup' ? 'border-primary shadow-lg' : '' }}">
            <input class="form-check-input" type="radio" name="pickup_method"
                id="pickupMethodOutlet" value="pickup"
                {{ old('pickup_method', 'pickup') == 'pickup' ? 'checked' : '' }} required>
            <label class="form-check-label ms-2" for="pickupMethodOutlet">
                <i class="fas fa-store me-1"></i> Ambil di Outlet
            </label>
        </div>
        <div
            class="form-check form-check-inline pickup-method-card border rounded-lg p-3 shadow-sm bg-white cursor-pointer {{ old('pickup_method') == 'delivery' ? 'border-primary shadow-lg' : '' }}">
            <input class="form-check-input" type="radio" name="pickup_method"
                id="pickupMethodDelivery" value="delivery"
                {{ old('pickup_method') == 'delivery' ? 'checked' : '' }} required>
            <label class="form-check-label ms-2" for="pickupMethodDelivery">
                <i class="fas fa-shipping-fast me-1"></i> Delivery
            </label>
        </div>
    </div>
</div>

                        <div class="mb-3">
                            <label for="outletSelect" class="form-label">Pilih Outlet <span
                                    style="color:red">*</span></label>
                            <select class="form-control rounded-lg" id="outletSelect" name="outlet_id" required>
                                <option value="">-- Pilih Outlet --</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}"
                                        {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->outlet_name }} ({{ $outlet->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Container for Service Selection --}}
                        <div class="mb-3" id="serviceSelectionContainer">
                            <label class="form-label">Pilih Layanan <span style="color:red">*</span></label>
                            <div id="serviceRadios" class="row">
                                <div class="col-12 text-center text-muted py-5" id="servicePlaceholder">
                                    Pilih Outlet terlebih dahulu untuk melihat layanan.
                                </div>
                            </div>
                        </div>

                        {{-- Container for Addon Selection (NEW) --}}
                        <div class="mb-3" id="addonSelectionContainer">
                            <label class="form-label">Pilih Add-on (opsional)</label>
                            <div id="addonCheckboxes" class="row">
                                <div class="col-12 text-center text-muted py-5" id="addonPlaceholder">
                                    Pilih Outlet terlebih dahulu untuk melihat add-on.
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Right Column: Device, Customer Details, Order Summary and Payment Details --}}
                    <div class="col-md-5">
                        {{-- Device Selection (NEW) --}}
                        <div class="mb-3">
                            <label for="deviceSelect" class="form-label">Pilih Perangkat <span
                                    style="color:red">*</span></label>
                            <select class="form-control rounded-lg" id="deviceSelect" name="device_id" required disabled>
                                <option value="">-- Pilih Layanan terlebih dahulu --</option>
                            </select>
                            <div class="text-center text-muted py-2" id="devicePlaceholder">
                                Pilih layanan terlebih dahulu untuk melihat perangkat.
                            </div>
                        </div>

                        {{-- Unit Quantity Input (NEW PLACEMENT) --}}
                        <div class="mb-3" id="unitInputContainer" style="display: none;">
                            <label for="unitInput" class="form-label" id="unitInputLabel">Jumlah</label>
                            <div class="input-group">
                                <input type="number" class="form-control rounded-lg" id="unitInput" name="unit" min="1" value="1" required>
                                <span class="input-group-text rounded-r-lg" id="unitInputLabelText">Unit</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="estimated_completion_at" class="form-label">Estimasi Selesai <span
                                    style="color:red">*</span></label>
                            <input type="text" class="form-control rounded-lg" id="estimated_completion_at"
                                name="estimated_completion_at" placeholder="Pilih tanggal & jam"
                                value="{{ old('estimated_completion_at') }}" required>
                        </div>

                        {{-- Member / Non-Member Selection --}}
                        <div class="mb-3">
                            <label class="form-label">Tipe Pelanggan <span style="color:red">*</span></label>
                            <div class="d-flex flex-wrap gap-3" id="customerTypeRadios">
                                <div
                                    class="form-check form-check-inline customer-type-card border rounded-lg p-3 shadow-sm bg-white cursor-pointer">
                                    <input class="form-check-input" type="radio" name="customer_type"
                                        id="customerTypeNonMember" value="non_member"
                                        {{ old('customer_type', 'non_member') == 'non_member' ? 'checked' : '' }} required>
                                    <label class="form-check-label ms-2" for="customerTypeNonMember">
                                        <i class="fas fa-user me-1"></i> Non-Member
                                    </label>
                                </div>
                                <div
                                    class="form-check form-check-inline customer-type-card border rounded-lg p-3 shadow-sm bg-white cursor-pointer">
                                    <input class="form-check-input" type="radio" name="customer_type"
                                        id="customerTypeMember" value="member"
                                        {{ old('customer_type') == 'member' ? 'checked' : '' }}
                                        {{ $members->isEmpty() ? 'disabled' : '' }} required>
                                    <label class="form-check-label ms-2" for="customerTypeMember">
                                        <i class="fas fa-users me-1"></i> Member
                                        @if ($members->isEmpty())
                                            <small class="text-danger">(Tidak ada member)</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Customer Details (for Non-Member) --}}
                        <div id="nonMemberCustomerDetails">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Nama Pelanggan</label>
                                <input type="text" class="form-control rounded-lg" id="customer_name"
                                    name="customer_name" value="{{ old('customer_name') }}"
                                    placeholder="Masukkan nama pelanggan">
                            </div>
                            <div class="mb-3">
                                <label for="customer_phone_number" class="form-label">Nomor HP Pelanggan</label>
                                <input type="text" class="form-control rounded-lg" id="customer_phone_number"
                                    name="customer_phone_number" value="{{ old('customer_phone_number') }}"
                                    placeholder="08xxxx atau +62">
                            </div>
                        </div>

                        {{-- Member Selection (for Member) --}}
                        <div id="memberSelectionDetails" style="display: none;">
                            <div class="mb-3">
                                <label for="memberSelect" class="form-label">Pilih Member <span
                                        style="color:red">*</span></label>
                                <select class="form-control rounded-lg" id="memberSelect" name="member_id">
                                    <option value="">-- Pilih Member --</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}" data-name="{{ $member->user->name ?? '' }}"
                                            data-phone="{{ $member->phone_number ?? '' }}"
                                            {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->user->name ?? 'N/A' }} ({{ $member->user->email ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Member:</label>
                                <p id="displayMemberName" class="form-control-static">
                                    {{ old('member_id') ? $members->find(old('member_id'))->user->name ?? '-' : '-' }}
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor HP Member:</label>
                                <p id="displayMemberPhone" class="form-control-static">
                                    {{ old('member_id') ? $members->find(old('member_id'))->phone_number ?? '-' : '-' }}
                                </p>
                            </div>
                        </div>



                        {{-- Payment Method --}}
                        <div class="mb-3">

                            {{-- Hidden input for final payment_method (member/non_member) --}}
                            <input type="hidden" name="payment_method" id="finalPaymentMethodInput"
                                value="{{ old('payment_method', 'non_member') }}">
                            {{-- Hidden input for final payment_type (cash/non_cash) --}}
                            <input type="hidden" name="payment_type" id="finalPaymentTypeInput"
                                value="{{ old('payment_type', 'cash') }}">


                            {{-- Ini adalah bagian yang sudah ada sebelumnya --}}
                            <div id="nonMemberPaymentOptions" class="row mt-2"
                                style="{{ old('customer_type', 'non_member') == 'member' ? 'display: none;' : '' }}">
                                <div class="col-md-6 mb-3">
                                    <div
                                        class="form-check p-3 border rounded-lg shadow-sm bg-white payment-method-card {{ old('payment_type', 'cash') == 'cash' ? 'border-primary shadow-lg' : '' }}">
                                        <input class="form-check-input temp-payment-type-radio" type="radio"
                                            name="temp_payment_type" id="paymentTypeCash" value="cash"
                                            {{ old('payment_type', 'cash') == 'cash' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 fw-bold" for="paymentTypeCash">
                                            <i class="fas fa-money-bill-wave me-2 " style="color: #169828"></i> Tunai
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div
                                        class="form-check p-3 border rounded-lg shadow-sm bg-white payment-method-card {{ old('payment_type') == 'non_cash' ? 'border-primary shadow-lg' : '' }}">
                                        <input class="form-check-input temp-payment-type-radio" type="radio"
                                            name="temp_payment_type" id="paymentTypeNonCash" value="non_cash"
                                            {{ old('payment_type') == 'non_cash' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 fw-bold" for="paymentTypeNonCash">
                                            <i class="fas fa-credit-card me-2" style="color: #1c5088"></i> Non-Tunai
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Tambahkan blok HTML ini UNTUK INPUT KODE BAYAR MEMBER --}}
                            <div id="memberPaymentCodeInput" class="mt-3"
                                style="{{ old('customer_type', 'non_member') == 'non_member' ? 'display: none;' : '' }}">
                                <label for="member_payment_code" class="form-label fw-bold">Kode Bayar Member</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-qrcode"></i></span>
                                    <input type="text" class="form-control" id="member_payment_code"
                                        placeholder="Masukkan kode bayar member"
                                        value="{{ old('member_payment_code') }}">
                                </div>
                                <div class="form-text text-muted">
                                    Masukkan kode unik yang diberikan oleh member untuk pembayaran.
                                </div>
                            </div>

                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control rounded-lg" id="notes" name="notes" placeholder="Masukkan catatan"
                                rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <div class="card rounded-lg shadow-sm mb-4">
                            <div class="card-header bg-primary text-white rounded-t-lg">
                                <h5 class="mb-0">Detail Transaksi</h5>
                            </div>
                            <div class="card-body">
                                <div id="orderSummary" class="min-h-[100px] flex items-center justify-center text-muted">
                                    Tidak ada layanan terpilih.
                                </div>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Total Pembayaran:</strong>
                                    <span class="fs-4 text-primary" id="displayAmount">Rp 0</span>
                                </div>
                                <input type="hidden" id="hiddenAmount" name="amount">
                                <input type="hidden" id="selectedServiceId" name="service_id">
                                <input type="hidden" id="selectedAddonIds" name="addon_ids">
                                <input type="hidden" id="selectedUnit" name="unit">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-3 rounded-lg shadow">
                            <i class="fas fa-cash-register me-2"></i> Proses Pembayaran
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css') }}" rel="stylesheet" />
    <style>
        /* General Form Styling */
        .form-control,
        .form-select {
            border-radius: 0.5rem;
            /* Rounded corners for inputs */
        }

        /* Custom styles for service cards */
        .service-card,
        .addon-card {
            /* Apply to both service and addon cards */
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .service-card:hover,
        .addon-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .service-card.border-primary,
        .addon-card.border-primary,
        .pickup-method-card.border-primary {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25) !important;
        }

        .service-card .form-check-input,
        .addon-card .form-check-input {
            float: right;
            /* Position the radio/checkbox button to the right */
            margin-left: 0;
            /* Remove default left margin */
            margin-top: 0.25rem;
            /* Adjust vertical alignment */
        }

        .service-card .form-check-label,
        .addon-card .form-check-label {
            display: block;
            /* Make label take full width */
            margin-left: 0;
            /* Remove default left margin */
        }

        /* Payment Method Cards */
        .payment-method-card,
        .customer-type-card,
        .pickup-method-card {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            flex: 1 1 auto;
            /* Allow cards to grow and shrink */
            min-width: 140px;
            /* Minimum width before wrapping */
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-method-card:hover,
        .customer-type-card:hover,
        .pickup-method-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .payment-method-card.border-primary,
        .customer-type-card.border-primary {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25) !important;
        }

        .payment-method-card .form-check-input,
        .customer-type-card .form-check-input,
        .pickup-method-card .form-check-input {
            position: absolute;
            /* Hide actual radio button visually */
            opacity: 0;
            cursor: pointer;
        }

        .payment-method-card label,
        .customer-type-card label,
        .pickup-method-card label {
            width: 100%;
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-method-card label i,
        .customer-type-card label i,
        .pickup-method-card label i {
            font-size: 1.25rem;
            /* Icon size */
            margin-right: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    {{-- <script src="{{ asset('assets/plugins/jquery/dist/jquery.min.js') }}"></script> --}}
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js') }}"></script>

    <script>
        $('#estimated_completion_at').datepicker({
            startDate: '0d' // Disables all dates before today
        });
    </script>

    <script>
        $(document).ready(function() {
            const outletServicesData = @json($outletServicesData ?? new stdClass());
            const outletAddonsData = @json($outletAddonsData ?? new stdClass());
            const outletsFullData = @json($outlets->keyBy('id'));
            const membersData = @json($members->keyBy('id'));

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }

            function getSelectedServicePrice() {
                const selectedServiceRadio = $(".service-radio:checked");
                if (selectedServiceRadio.length === 0) {
                    return 0;
                }

                const customerType = $('input[name="customer_type"]:checked').val();
                let price;
                if (customerType === 'member') {
                    price = parseFloat(selectedServiceRadio.data('member-price'));
                } else {
                    price = parseFloat(selectedServiceRadio.data('non-member-price'));
                }
                return price;
            }

            function calculateTotalAmount() {
                let totalAmount = 0;
                const selectedServicePrice = getSelectedServicePrice();
                const unitInput = $('#unitInput');
                const unitValue = unitInput.length > 0 ? parseFloat(unitInput.val()) : 1;

                if (selectedServicePrice > 0 && unitValue > 0) {
                    totalAmount += selectedServicePrice * unitValue;
                }

                $('.addon-checkbox:checked').each(function() {
                    totalAmount += parseFloat($(this).data('price'));
                });
                return totalAmount;
            }

            function updateOrderSummary() {
                let summaryHtml = '';
                let totalAmount = 0;

                const selectedServiceRadio = $(".service-radio:checked");
                const customerType = $('input[name="customer_type"]:checked').val();
                const priceLabel = customerType === 'member' ? '(Harga Member)' : '(Harga Non-Member)';

                if (selectedServiceRadio.length > 0) {
                    const serviceName = selectedServiceRadio.data('name');
                    const servicePrice = getSelectedServicePrice();
                    const unitValue = parseFloat($('#unitInput').val()) || 0;
                    const unitType = selectedServiceRadio.data('unit');

                    summaryHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Layanan: <strong>${serviceName}</strong> (${unitValue} ${unitType})</span>
                            <span>${formatRupiah(servicePrice * unitValue)}</span>
                        </div>
                    `;
                    totalAmount += servicePrice * unitValue;
                } else {
                    summaryHtml += '<p class="text-muted text-center">Tidak ada layanan terpilih.</p>';
                }

                const selectedAddons = [];
                $('.addon-checkbox:checked').each(function() {
                    const addonName = $(this).data('name');
                    const addonPrice = parseFloat($(this).data('price'));
                    selectedAddons.push({
                        name: addonName,
                        price: addonPrice
                    });
                    totalAmount += addonPrice;
                });

                if (selectedAddons.length > 0) {
                    summaryHtml += `<div class="mt-2"><strong>Add-on:</strong></div>`;
                    selectedAddons.forEach(addon => {
                        summaryHtml += `
                            <div class="d-flex justify-content-between align-items-center text-sm">
                                <span>- ${addon.name}</span>
                                <span>${formatRupiah(addon.price)}</span>
                            </div>
                        `;
                    });
                }

                $("#orderSummary").html(summaryHtml);
                $("#displayAmount").text(formatRupiah(totalAmount));
                $("#hiddenAmount").val(totalAmount);

                const selectedAddonIds = $('.addon-checkbox:checked').map(function() {
                    return $(this).val();
                }).get().join(',');
                $("#selectedAddonIds").val(selectedAddonIds);
            }

            function updateDeviceSelection(serviceHasOptions) {
                const $deviceSelect = $("#deviceSelect");
                const $devicePlaceholder = $("#devicePlaceholder");
                const selectedOutletId = $("#outletSelect").val();
                const outletData = outletsFullData[selectedOutletId];

                // Clear and disable by default
                $deviceSelect.empty().prop('disabled', true);

                // --- Perubahan Teks Placeholder di sini ---
                $devicePlaceholder.show().text('Pilih layanan terlebih dahulu.');

                // Only proceed if an outlet is selected and the service requires a device
                if (selectedOutletId && serviceHasOptions) {
                    if (outletData && outletData.devices && outletData.devices.length > 0) {
                        $devicePlaceholder.hide();
                        $deviceSelect.prop('disabled', false);
                        $deviceSelect.empty().append(
                            '<option value="">-- Pilih Perangkat --</option>'
                        );
                        $.each(outletData.devices, function(index, deviceData) {
                            $deviceSelect.append(
                                `<option value="${deviceData.id}">${deviceData.name} (${deviceData.code})</option>`
                            );
                        });
                        const oldDeviceId = "{{ old('device_id') }}";
                        if (oldDeviceId) {
                            $deviceSelect.val(oldDeviceId);
                        }
                    } else {
                        // If outlet is selected and service needs a device, but no devices exist
                        $devicePlaceholder.text('Tidak ada perangkat yang tersedia untuk outlet ini.').show();
                        $deviceSelect.empty().append('<option value="">Tidak ada perangkat</option>');
                    }
                } else if (selectedOutletId && !serviceHasOptions) {
                    // If outlet is selected but service doesn't require a device
                    // --- Perubahan Teks Placeholder di sini ---
                    $devicePlaceholder.text('Layanan yang dipilih tidak memerlukan perangkat.').show();
                    $deviceSelect.empty().append('<option value="">Tidak ada perangkat</option>');
                }
            }


            function updateAllSelections() {
                var selectedOutletId = $("#outletSelect").val();
                var $serviceRadios = $("#serviceRadios");
                var $addonCheckboxes = $("#addonCheckboxes");
                var $servicePlaceholder = $("#servicePlaceholder");
                var $addonPlaceholder = $("#addonPlaceholder");

                // Reset all selections and placeholders
                $serviceRadios.empty();
                $addonCheckboxes.empty();

                $("#orderSummary").html('<p class="text-muted text-center">Tidak ada layanan terpilih.</p>');
                $("#displayAmount").text(formatRupiah(0));
                $("#hiddenAmount").val('');
                $("#selectedServiceId").val('');
                $("#selectedAddonIds").val('');
                $("#selectedUnit").val('');
                $("#unitInputContainer").hide(); // Hide unit input on reset

                updateDeviceSelection(false);

                $servicePlaceholder.show();
                $addonPlaceholder.show();

                if (selectedOutletId) {
                    const servicesForOutlet = outletServicesData[selectedOutletId];
                    if (servicesForOutlet && Object.keys(servicesForOutlet).length > 0) {
                        $servicePlaceholder.hide();
                        $.each(servicesForOutlet, function(serviceId, serviceData) {
                            let serviceOptionsHtml = '';
                            let hasServiceOptions = false;
                            if (serviceData.service_options && serviceData.service_options.length > 0) {
                                hasServiceOptions = true;
                                serviceOptionsHtml = '<div class="text-sm text-gray-600 mt-1">';
                                serviceData.service_options.forEach((option, index) => {
                                    serviceOptionsHtml +=
                                        `<span>${option.name}</span>${index < serviceData.service_options.length - 1 ? ', ' : ''}`;
                                });
                                serviceOptionsHtml += '</div>';
                            } else {
                                serviceOptionsHtml =
                                    '<div class="text-sm text-gray-600 mt-1">Tidak ada opsi layanan.</div>';
                            }

                            // Tampilkan kedua harga di sini
                            const priceHtml = `
                                <div class="mt-2">
                                    <span class="d-block text-md text-green-600">Member: ${formatRupiah(serviceData.member_price)}</span>
                                    <span class="d-block text-md text-gray-500">Non-Member: ${formatRupiah(serviceData.non_member_price)}</span>
                                </div>
                            `;

                            var radioHtml = `
                                <div class="col-md-6 mb-3">
                                    <div class="form-check p-3 border rounded-lg shadow-sm bg-white service-card">
                                        <input class="form-check-input service-radio" type="radio" name="temp_service_id" id="service_${serviceId}" value="${serviceId}" data-member-price="${serviceData.member_price}" data-non-member-price="${serviceData.non_member_price}" data-name="${serviceData.name}" data-unit="${serviceData.unit}" data-has-options="${hasServiceOptions ? 'true' : 'false'}" required>
                                        <label class="form-check-label w-100" for="service_${serviceId}">
                                            <strong class="text-lg text-blue-700">${serviceData.name}</strong>
                                            <small class="text-muted d-block">Unit: ${serviceData.unit}</small>
                                            ${priceHtml}
                                            ${serviceOptionsHtml}
                                        </label>
                                    </div>
                                </div>
                            `;
                            $serviceRadios.append(radioHtml);
                        });
                    } else {
                        $servicePlaceholder.text('Tidak ada layanan yang tersedia untuk outlet ini.').show();
                    }

                    // Populate Addons
                    const addonsForOutlet = outletAddonsData[selectedOutletId];
                    if (addonsForOutlet && Object.keys(addonsForOutlet).length > 0) {
                        $addonPlaceholder.hide();
                        $.each(addonsForOutlet, function(addonId, addonData) {
                            var checkboxHtml = `
                                <div class="col-md-6 mb-3">
                                    <div class="form-check p-3 border rounded-lg shadow-sm bg-white addon-card">
                                        <input class="form-check-input addon-checkbox" type="checkbox" name="temp_addon_ids[]" id="addon_${addonId}" value="${addonId}" data-price="${addonData.price}" data-name="${addonData.name}">
                                        <label class="form-check-label w-100" for="addon_${addonId}">
                                            <strong class="text-lg text-purple-700">${addonData.name}</strong>
                                            <span class="d-block text-md text-green-600">${formatRupiah(addonData.price)}</span>
                                            ${addonData.category ? `<div class="text-sm text-gray-600 mt-1">Kategori: ${addonData.category}</div>` : ''}
                                            ${addonData.description ? `<div class="text-xs text-gray-500 mt-1">${addonData.description}</div>` : ''}
                                        </label>
                                    </div>
                                </div>
                            `;
                            $addonCheckboxes.append(checkboxHtml);
                        });
                    } else {
                        $addonPlaceholder.text('Tidak ada add-on yang tersedia untuk outlet ini.').show();
                    }

                    // Attach change listeners for newly added radios and checkboxes
                    $(".service-radio").on("change", function() {
                        $("#selectedServiceId").val($(this).val());
                        $("#selectedUnit").val(1);
                        updateOrderSummary();
                        $('.service-card').removeClass('border-primary shadow-lg');
                        $(this).closest('.service-card').addClass('border-primary shadow-lg');

                        const serviceHasOptions = $(this).data('has-options') === true;
                        updateDeviceSelection(serviceHasOptions);

                        // Update and show the unit input in the right column
                        const unitType = $(this).data('unit');
                        $('#unitInputContainer').show();
                        $('#unitInputLabel').text(`Jumlah ${unitType}`);
                        $('#unitInputLabelText').text(unitType);

                        // Attach listener to the new unit input
                        $('#unitInput').on('change keyup', function() {
                             $("#selectedUnit").val($(this).val());
                             updateOrderSummary();
                        });

                    });

                    $(".addon-checkbox").on("change", function() {
                        updateOrderSummary();
                        $(this).closest('.addon-card').toggleClass('border-primary shadow-lg', $(this).is(
                            ':checked'));
                    });

                    // Set initial selection for service and addons if old input exists
                    const oldServiceId = "{{ old('service_id') }}";
                    if (oldServiceId) {
                        const $oldServiceRadio = $(`#service_${oldServiceId}`);
                        $oldServiceRadio.prop('checked', true);

                        if ($oldServiceRadio.length > 0) {
                            const serviceHasOptions = $oldServiceRadio.data('has-options') === true;
                            updateDeviceSelection(serviceHasOptions);
                            $oldServiceRadio.closest('.service-card').addClass('border-primary shadow-lg');
                        }
                    }

                    const oldAddonIds = "{{ old('addon_ids') }}";
                    if (oldAddonIds) {
                        oldAddonIds.split(',').forEach(function(addonId) {
                            $(`#addon_${addonId}`).prop('checked', true).trigger('change');
                        });
                    }
                    updateOrderSummary(); // Trigger initial summary update
                } else {
                    $servicePlaceholder.text('Pilih Outlet terlebih dahulu untuk melihat layanan.').show();
                    $addonPlaceholder.text('Pilih Outlet terlebih dahulu untuk melihat add-on.').show();
                    updateDeviceSelection(false);
                    $('#unitInputContainer').hide();
                }
            }

            $("#outletSelect").on("change", updateAllSelections);

            if ($("#outletSelect").val()) {
                updateAllSelections();
            }

            // --- Customer Type and Payment Method Logic ---
            function handleCustomerTypeChange() {
                const selectedCustomerType = $('input[name="customer_type"]:checked').val();

                $('#memberSelect').val('');
                $('#displayMemberName').text('-');
                $('#displayMemberPhone').text('-');
                $('#customer_name').val('');
                $('#customer_phone_number').val('');
                $('#finalPaymentTypeInput').val('');
                $('#member_payment_code').val('');

                if (selectedCustomerType === 'non_member') {
                    $('#nonMemberCustomerDetails').show();
                    $('#memberSelectionDetails').hide();
                    $('#nonMemberPaymentOptions').show();
                    $('#memberPaymentCodeInput').hide();

                    $('#finalPaymentMethodInput').val('non_member');

                    const defaultPaymentType = $('input[name="temp_payment_type"]:checked').val() || 'cash';
                    $('#finalPaymentTypeInput').val(defaultPaymentType);
                    $('input[name="temp_payment_type"]').prop('disabled', false);
                    $(`#paymentType${defaultPaymentType.charAt(0).toUpperCase() + defaultPaymentType.slice(1)}`)
                        .prop('checked', true).trigger('change');

                    $('#memberSelect').prop('name', '');
                    $('#member_payment_code').prop('name', '');
                    $('#customer_name').prop('name', 'customer_name');
                    $('#customer_phone_number').prop('name', 'customer_phone_number');
                } else if (selectedCustomerType === 'member') {
                    $('#nonMemberCustomerDetails').hide();
                    $('#memberSelectionDetails').show();
                    $('#nonMemberPaymentOptions').hide();
                    $('#memberPaymentCodeInput').show();

                    $('#finalPaymentMethodInput').val('member');
                    $('#finalPaymentTypeInput').val('');

                    $('input[name="temp_payment_type"]').prop('disabled', true);
                    $('input[name="temp_payment_type"]').prop('checked', false);

                    $('#memberSelect').prop('name', 'member_id');
                    $('#member_payment_code').prop('name', 'member_payment_code');
                    $('#customer_name').prop('name', '');
                    $('#customer_phone_number').prop('name', '');

                    const oldMemberId = "{{ old('member_id') }}";
                    if (oldMemberId) {
                        $('#memberSelect').val(oldMemberId).trigger('change');
                    }
                    const oldMemberPaymentCode = "{{ old('member_payment_code') }}";
                    if (oldMemberPaymentCode) {
                        $('#member_payment_code').val(oldMemberPaymentCode);
                    }
                }
                $('.customer-type-card').removeClass('border-primary shadow-lg');
                $('input[name="customer_type"]:checked').closest('.customer-type-card').addClass(
                    'border-primary shadow-lg');

                // PENTING: Panggil updateOrderSummary() setelah tipe pelanggan berubah
                updateOrderSummary();
            }

            $('#memberSelect').on('change', function() {
                const selectedMemberId = $(this).val();
                if (selectedMemberId) {
                    const member = membersData[selectedMemberId];
                    $('#displayMemberName').text(member.user.name || '-');
                    $('#displayMemberPhone').text(member.phone_number || '-');
                } else {
                    $('#displayMemberName').text('-');
                    $('#displayMemberPhone').text('-');
                }
            });

            $('input[name="temp_payment_type"]').on('change', function() {
                $('#finalPaymentTypeInput').val($(this).val());
                $('.payment-method-card').removeClass('border-primary shadow-lg');
                $(this).closest('.payment-method-card').addClass('border-primary shadow-lg');
            });

            // Pickup Method card click handler
            $('.pickup-method-card').on('click', function() {
                 $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
            });

            $('input[name="pickup_method"]').on('change', function() {
                $('.pickup-method-card').removeClass('border-primary shadow-lg');
                $(this).closest('.pickup-method-card').addClass('border-primary shadow-lg');
            });


            $('input[name="customer_type"]').on('change', handleCustomerTypeChange);

            handleCustomerTypeChange(); // Call on page load to set initial state


            $("#paymentForm input").on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                }
            });

            $("#paymentForm").on("submit", function(e) {
                e.preventDefault();

                var selectedServiceId = $("#selectedServiceId").val();
                if (selectedServiceId === "") {
                    swal("Peringatan", "Mohon pilih setidaknya satu layanan.", "warning");
                    return false;
                }

                var selectedDeviceId = $("#deviceSelect").val();
                const selectedServiceRadio = $(".service-radio:checked");
                const serviceHasOptions = selectedServiceRadio.data('has-options') === true;

                // Check for device selection only if the service requires it and the dropdown is not disabled
                if (serviceHasOptions && !$("#deviceSelect").prop('disabled') && selectedDeviceId === "") {
                    swal("Peringatan", "Mohon pilih perangkat.", "warning");
                    return false;
                }


                var unitInput = $('#unitInput');
                if (unitInput.length > 0 && (parseFloat(unitInput.val()) <= 0 || !unitInput.val())) {
                    swal("Peringatan", `Mohon masukkan jumlah unit yang valid.`, "warning");
                    return false;
                }

                const selectedCustomerType = $('input[name="customer_type"]:checked').val();
                if (selectedCustomerType === 'member') {
                    if (!$('#memberSelect').val()) {
                        swal("Peringatan", "Mohon pilih member.", "warning");
                        return false;
                    }
                    if (!$('#member_payment_code').val()) {
                        swal("Peringatan", "Mohon masukkan kode bayar member.", "warning");
                        return false;
                    }
                } else {
                    if (!$('#customer_name').val()) {
                        swal("Peringatan", "Mohon masukkan nama pelanggan.", "warning");
                        return false;
                    }
                    if (!$('#customer_phone_number').val()) {
                        swal("Peringatan", "Mohon masukkan nomor HP pelanggan.", "warning");
                        return false;
                    }
                }

                swal({
                    title: 'Konfirmasi',
                    text: "Apakah Anda yakin ingin memproses pembayaran kasir?",
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: "Batal",
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Ya, proses!",
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((willSubmit) => {
                    if (willSubmit) {
                        if (selectedCustomerType === 'member') {
                            $('#customer_name').prop('name', '');
                            $('#customer_phone_number').prop('name', '');
                            $('#memberSelect').prop('name', 'member_id');
                            $('#member_payment_code').prop('name', 'member_payment_code');
                            $('#finalPaymentTypeInput').prop('name', '');
                        } else {
                            $('#customer_name').prop('name', 'customer_name');
                            $('#customer_phone_number').prop('name', 'customer_phone_number');
                            $('#memberSelect').prop('name', '');
                            $('#member_payment_code').prop('name', '');
                            $('#finalPaymentTypeInput').prop('name', 'payment_type');
                        }

                        $('input[name="temp_service_id"]:checked').prop('name', 'service_id');
                        $('input[name="temp_addon_ids[]"]').prop('name', 'addon_ids[]');


                        $("#paymentForm").off("submit").submit();
                    }
                });
            });

            @if (session('success'))
                swal({
                    title: 'Sukses!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
            @if (session('error'))
                swal({
                    title: 'Error!',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endpush
