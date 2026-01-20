@php
    // Get the currently logged-in user
    $user = auth()->user();

    // Initialize status variables
    $isMemberRole = false;
    $isVerifiedMember = false;
    $isPendingMember = false;
    $isEligibleToRegister = false;
    $isOtherUser = false;
    $isGuest = true; // New variable to handle guests

    // Check if a user is logged in
    if ($user) {
        $isGuest = false;
        // Check if the user's role is 'member'
        if ($user->role === 'member') {
            $isMemberRole = true;

            // Check if the user is already registered for this brand
            $subscription = $brand->members()->where('member_id', $user->member->id)->first();

            // dd($user->member->id);
            if ($subscription) {
                // User is registered, check verification status
                if ($subscription->pivot->is_verified) {
                    $isVerifiedMember = true;
                } else {
                    $isPendingMember = true;
                }
            } else {
                // User has 'member' role but isn't registered for this brand
                $isEligibleToRegister = true;
            }
        } else {
            // User is logged in, but their role is not 'member'
            $isOtherUser = true;
        }
    }
@endphp

{{-- Button conditions based on user status --}}

@if ($isVerifiedMember)
    {{-- 1. User is registered and verified --}}
    <button class="fixed-bottom-button btn btn-success" disabled>
        <i class="ri-checkbox-circle-line me-2"></i>
        <span>Sudah Terdaftar</span>
    </button>
@elseif ($isPendingMember)
    {{-- 2. User is registered but pending verification --}}
    <button class="fixed-bottom-button btn btn-warning" disabled>
        <i class="ri-time-line me-2"></i>
        <span>Menunggu Verifikasi</span>
    </button>
@elseif ($isEligibleToRegister)
    {{-- 3. User with 'member' role who is not yet registered --}}
    <button type="button" class="fixed-bottom-button btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerMemberModal">
        <i class="ri-user-add-line me-2"></i>
        <span>Daftar Member</span>
    </button>
@elseif ($isGuest)
    {{-- 4. User is not logged in (guest) --}}
    <a href="{{ route('login') }}" class="fixed-bottom-button btn btn-primary">
        <i class="ri-login-box-line me-2"></i>
        <span>Login / Daftar</span>
    </a>
@else
    {{-- 5. Logged-in user, but their role is not 'member' --}}
    <button class="fixed-bottom-button btn btn-secondary" disabled>
        <i class="ri-lock-line me-2"></i>
        <span>Non-Member</span>
    </button>
@endif

{{-- Registration modal is only displayed if the user is eligible to register --}}
@if ($isEligibleToRegister)
<div class="modal fade" id="registerMemberModal" tabindex="-1" aria-labelledby="registerMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerMemberModalLabel">Konfirmasi Pendaftaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="ri-question-line text-warning display-4 mb-3"></i>
                <p>Apakah Anda yakin ingin mendaftar menjadi member brand <strong>{{ $brand->brand_name }}</strong>?</p>
                <small class="text-muted">Dengan menjadi member, Anda akan mendapatkan informasi dan penawaran khusus dari brand ini.</small>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="register-form" action="{{ route('home.member.register.member.brand', $brand->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Ya, Daftar Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    .fixed-bottom-button {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 1050;
        padding: 0.75rem 1.5rem;
        border-radius: 50px; /* Pill shape for a modern look */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        font-size: 1rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    .fixed-bottom-button:hover:not([disabled]) {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    }
    .fixed-bottom-button i {
        font-size: 1.25rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2500
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
            });
        @endif
    });
</script>
@endpush
