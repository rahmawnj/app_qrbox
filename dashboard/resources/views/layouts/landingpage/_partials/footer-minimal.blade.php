<style>
    /* Palet Warna Biru untuk Dashboard */
    :root {
        --primary-blue: #007bff;        /* Biru utama untuk latar belakang dan sorotan */
        --dark-blue: #0056b3;           /* Varian biru lebih gelap untuk hover atau elemen tertentu */
        --light-gray: #e9ecef;          /* Warna abu-abu terang untuk teks di atas biru gelap */
        --text-color-muted: #a0aec0;    /* Teks abu-abu muted untuk keterangan */
    }

    /* Footer Specific Styles (Minimal Dashboard Footer) */
    .dashboard-footer {
        background-color: var(--primary-blue);
        color: var(--light-gray);
        padding: 20px 0;
        font-size: 0.9rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1); /* Garis atas yang lebih halus */
    }

    .dashboard-footer .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; /* Izinkan wrap di layar kecil */
        gap: 15px; /* Jarak antar item saat wrap */
    }

    .dashboard-footer .copyright-text {
        flex-grow: 1; /* Teks copyright mengambil ruang yang tersedia */
        text-align: left;
    }

    .dashboard-footer .social-icons {
        display: flex;
        gap: 15px;
    }

    .dashboard-footer .social-icons a {
        color: var(--light-gray); /* Ikon berwarna terang agar kontras */
        font-size: 1.1rem;
        transition: color 0.2s ease;
    }

    .dashboard-footer .social-icons a:hover {
        color: var(--dark-blue); /* Warna hover yang lebih gelap */
    }

    /* Responsive adjustments for the minimal footer */
    @media (max-width: 767.98px) {
        .dashboard-footer .container {
            flex-direction: column;
            text-align: center;
        }
        .dashboard-footer .copyright-text {
            text-align: center;
        }
        .dashboard-footer .social-icons {
            justify-content: center; /* Posisikan ikon di tengah pada layar kecil */
        }
    }
</style>

<footer class="dashboard-footer">
    <div class="container">
        <div class="copyright-text">
            Copyrights &copy; {{ date('Y') }} Laundry App.
        </div>
        <div class="social-icons">
            <a href="#" aria-label="Facebook"><i class="ri-facebook-fill"></i></a>
            <a href="#" aria-label="Twitter"><i class="ri-twitter-fill"></i></a>
            <a href="#" aria-label="Instagram"><i class="ri-instagram-fill"></i></a>
            <a href="#" aria-label="LinkedIn"><i class="ri-linkedin-fill"></i></a>
        </div>
    </div>
</footer>
