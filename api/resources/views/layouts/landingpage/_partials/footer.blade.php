<div id="footer" class="py-5 footer-custom bubble-overlay">
    <div class="container-xxl px-3 px-lg-5">
        <div class="row gx-lg-5 gx-3 gy-lg-4 gy-3">
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <div class="mb-3 fw-bold fs-28px text-white d-flex align-items-center">
                    <i class="fas fa-tshirt me-2"></i> Laundry App
                </div>
                <p class="mb-4 text-color-light">
                    Solusi terdepan untuk kebutuhan laundry Anda. Cepat, bersih, dan praktis, langsung dari genggaman Anda.
                </p>
                <h5 class="text-color-light mb-3">Ikuti Kami</h5>
                <div class="d-flex social-icons">
                    <a href="#" class="me-3"><i class="fab fa-lg fa-facebook-f fa-fw"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-lg fa-instagram fa-fw"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-lg fa-twitter fa-fw"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-lg fa-youtube fa-fw"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-lg fa-linkedin-in fa-fw"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h5 class="text-color-light mb-3">Tautan Cepat</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="#" class="text-decoration-none">Beranda</a></li>
                    <li><a href="#" class="text-decoration-none">Tentang Kami</a></li>
                    <li><a href="#" class="text-decoration-none">Layanan</a></li>
                    <li><a href="#" class="text-decoration-none">Outlet Terdekat</a></li>
                    <li><a href="#" class="text-decoration-none">Karir</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h5 class="text-color-light mb-3">Bantuan & Sumber Daya</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="#" class="text-decoration-none">Pusat Bantuan</a></li>
                    <li><a href="#" class="text-decoration-none">FAQ</a></li>
                    <li><a href="#" class="text-decoration-none">Dukungan Teknis</a></li>
                    <li><a href="#" class="text-decoration-none">Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-decoration-none">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="text-color-light mb-3">Hubungi Kami</h5>
                <p class="text-color-light mb-2"><i class="fas fa-map-marker-alt me-2"></i> Jl. Contoh No. 123, Jakarta, Indonesia</p>
                <p class="text-color-light mb-2"><i class="fas fa-phone me-2"></i> +62 812-3456-7890</p>
                <p class="text-color-light mb-2"><i class="fas fa-envelope me-2"></i> info@laundryapp.com</p>
                <div class="mt-4">
                    <a href="#" class="btn btn-primary btn-sm rounded-pill px-4">Hubungi Kami</a>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0 text-center text-md-start">
                <div class="footer-copyright-text text-color-light">&copy; 2025 Laundry App. All Rights Reserved.</div>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="dropdown d-inline-block">
                    <a href="#" class="text-decoration-none dropdown-toggle text-color-light" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-2"></i> Indonesia (Bahasa Indonesia)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a href="#" class="dropdown-item">Indonesia (Bahasa Indonesia)</a></li>
                        <li><a href="#" class="dropdown-item">United States (English)</a></li>
                        <li><a href="#" class="dropdown-item">China (简体中文)</a></li>
                        <li><a href="#" class="dropdown-item">Brazil (Português)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Menggunakan palet warna yang sama dengan halaman lain */
    :root {
        --primary-color: #0056b3;
        --secondary-color: #ffc107;
        --blue-gradient-start: #007bff;
        --blue-gradient-end: #0056b3;
        --light-bg: #e3f2fd;
        --card-radius: 14px;
        --shadow-soft: 0 8px 28px rgba(33, 150, 243, 0.15);
        --footer-bg: #002244; /* Warna biru gelap yang lebih dalam untuk footer */
        --light-text-color: rgba(255, 255, 255, 0.8);
    }

    /* Custom Footer Styling */
    .footer-custom {
        background: linear-gradient(135deg, var(--blue-gradient-end) 0%, var(--primary-color) 100%);
        color: var(--light-text-color);
        font-family: 'Poppins', sans-serif;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 60px;
        padding-bottom: 40px;
    }

    .footer-custom h5 {
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 10px;
        color: var(--light-text-color);
    }
    .footer-custom h5::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background-color: var(--secondary-color); /* Menggunakan warna aksen kuning */
        border-radius: 2px;
    }

    .footer-custom .fs-28px {
        font-size: 2.2rem;
        font-weight: 800;
        color: #fff;
    }

    .footer-custom p {
        font-size: 0.95rem;
        line-height: 1.6;
        color: var(--light-text-color);
    }

    .footer-custom .social-icons a {
        color: var(--light-text-color);
        font-size: 1.5rem;
        transition: color 0.3s ease, transform 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
    }
    .footer-custom .social-icons a:hover {
        color: var(--secondary-color);
        transform: translateY(-3px);
        background-color: rgba(255, 255, 255, 0.2);
    }

    .footer-custom .footer-links li {
        margin-bottom: 10px;
    }
    .footer-custom .footer-links a {
        color: var(--light-text-color);
        font-size: 0.95rem;
        transition: color 0.3s ease;
        padding: 5px 0;
        display: inline-block;
    }
    .footer-custom .footer-links a:hover {
        color: var(--secondary-color);
        text-decoration: underline !important;
    }

    .footer-custom .footer-divider {
        border-color: rgba(255, 255, 255, 0.2);
        margin-top: 40px;
        margin-bottom: 30px;
    }

    .footer-custom .footer-copyright-text {
        font-size: 0.85rem;
        color: var(--light-text-color);
    }

    /* Language Dropdown */
    .footer-custom .dropdown-toggle {
        font-size: 0.9rem;
        padding: 8px 15px;
        border-radius: 8px;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: background-color 0.3s ease;
        color: var(--light-text-color);
    }
    .footer-custom .dropdown-toggle:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }
    .footer-custom .dropdown-menu {
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        background-color: #002e5d; /* Menggunakan warna yang lebih gelap dari footer */
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .footer-custom .dropdown-menu .dropdown-item {
        color: var(--light-text-color);
        font-size: 0.9rem;
        padding: 8px 15px;
        transition: background-color 0.3s ease;
    }
    .footer-custom .dropdown-menu .dropdown-item:hover {
        background-color: var(--blue-gradient-start);
        color: #fff;
    }

    /* Contact Info */
    .footer-custom .col-lg-3 p {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
        color: var(--light-text-color);
    }
    .footer-custom .col-lg-3 p i {
        margin-top: 4px;
        font-size: 1rem;
        color: var(--secondary-color); /* Menggunakan warna aksen kuning */
    }

    /* Responsive Adjustments */
    @media (max-width: 767.98px) {
        .footer-custom {
            padding-top: 40px;
            padding-bottom: 20px;
        }
        .footer-custom h5 {
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .footer-custom h5::after {
            width: 30px;
            height: 2px;
        }
        .footer-custom .fs-28px {
            font-size: 1.8rem;
        }
        .footer-custom .social-icons {
            justify-content: center;
            margin-bottom: 30px;
        }
        .footer-custom .footer-links {
            text-align: center;
        }
        .footer-custom .col-lg-3 p {
            justify-content: center;
            text-align: center;
        }
        .footer-custom .col-lg-3 p i {
            margin-right: 8px;
        }
        .footer-custom .btn {
            width: 100%;
        }
        .footer-custom .text-md-start,
        .footer-custom .text-md-end {
            text-align: center !important;
        }
    }
</style>
