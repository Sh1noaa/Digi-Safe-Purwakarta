<?php

$baseDir = __DIR__;
$publicDir = $baseDir . '/public';
$docsDir = $publicDir . '/docs';

if (!is_dir($docsDir)) {
    mkdir($docsDir, 0777, true);
}

// Convert images to base64
$logoData = base64_encode(file_get_contents($publicDir . '/images/logo.png'));
$loginMascotData = base64_encode(file_get_contents($publicDir . '/images/mascot-login.jpg'));
$registerMascotData = base64_encode(file_get_contents($publicDir . '/images/mascot-register.jpg'));

$logoSrc = "data:image/png;base64," . $logoData;
$loginMascotSrc = "data:image/jpeg;base64," . $loginMascotData;
$registerMascotSrc = "data:image/jpeg;base64," . $registerMascotData;

// Use nowdoc so $ variables in code snippets are NOT interpolated
$html = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan & Penjelasan Sistem Digi-Safe Purwakarta</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap');

        @page {
            size: A4 portrait;
            margin: 15mm 16mm 18mm 16mm;
            @bottom-right {
                content: "Halaman " counter(page);
                font-family: 'Inter', sans-serif;
                font-size: 8pt;
                color: #64748B;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9.5pt;
            line-height: 1.55;
            color: #1E293B;
            background-color: #FFFFFF;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        h1, h2, h3, h4, h5, .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            color: #0F172A;
            line-height: 1.25;
        }

        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        /* Header Document */
        .doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 2px solid #0F172A;
            margin-bottom: 18px;
        }
        .doc-header .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .doc-header img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .doc-header .brand-title {
            font-size: 14pt;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0F172A;
        }
        .doc-header .brand-subtitle {
            font-size: 8pt;
            color: #64748B;
            font-weight: 500;
        }
        .doc-header .badge-doc {
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 8pt;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Title Hero Cover */
        .cover-box {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: #FFFFFF;
            padding: 22px 24px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }
        .cover-tag {
            display: inline-block;
            background: rgba(132, 204, 22, 0.2);
            color: #BEF264;
            border: 1px solid rgba(190, 242, 100, 0.4);
            font-size: 7.5pt;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .cover-box h1 {
            color: #FFFFFF;
            font-size: 16pt;
            font-weight: 800;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .cover-box p {
            color: #94A3B8;
            font-size: 8.8pt;
            line-height: 1.5;
            margin-bottom: 14px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }
        .meta-item {
            font-size: 7.5pt;
        }
        .meta-item .label {
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .meta-item .value {
            color: #F8FAFC;
            font-weight: 600;
            font-size: 8.5pt;
        }

        /* Chapter Headings */
        .chapter-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5pt;
            font-weight: 800;
            color: #0F172A;
            margin-top: 20px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #E2E8F0;
        }
        .chapter-pill {
            background: #0284C7;
            color: #FFFFFF;
            font-size: 7.5pt;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        p {
            margin-bottom: 9px;
            text-align: justify;
        }

        /* Question & Answer Box */
        .qa-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 14px;
        }
        .q-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
            font-size: 7.5pt;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .q-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10pt;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
        }
        .a-text {
            font-size: 9pt;
            color: #334155;
            line-height: 1.55;
        }

        /* Speech Script Box */
        .script-card {
            background: #F0FDF4;
            border: 1.5px solid #86EFAC;
            border-left: 4px solid #16A34A;
            border-radius: 8px;
            padding: 12px 14px;
            margin: 10px 0 14px 0;
        }
        .script-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #DCFCE7;
            color: #15803D;
            font-size: 7.5pt;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .script-quote {
            font-family: 'Inter', sans-serif;
            font-style: italic;
            font-size: 8.8pt;
            color: #14532D;
            line-height: 1.55;
        }

        /* Node Flow Diagram in CSS */
        .node-diagram {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FFFFFF;
            border: 1px dashed #94A3B8;
            border-radius: 10px;
            padding: 12px 10px;
            margin: 12px 0;
        }
        .node-card {
            background: #F8FAFC;
            border: 1.5px solid #0284C7;
            border-radius: 8px;
            padding: 6px 10px;
            text-align: center;
            font-size: 7.5pt;
            font-weight: 600;
            color: #0F172A;
            min-width: 95px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        .node-card.accent {
            border-color: #84CC16;
            background: #F7FEE7;
            color: #365314;
        }
        .node-arrow {
            color: #64748B;
            font-weight: bold;
            font-size: 8.5pt;
        }

        /* Mascot Display */
        .mascot-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 12px 0;
        }
        .mascot-card {
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            background: #FAFAFA;
        }
        .mascot-card img {
            width: 85px;
            height: 85px;
            object-fit: contain;
            margin: 0 auto 6px auto;
            border-radius: 8px;
        }
        .mascot-card .caption {
            font-size: 7.5pt;
            font-weight: 600;
            color: #475569;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin: 12px 0;
        }
        th, td {
            border: 1px solid #E2E8F0;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #0F172A;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 8pt;
        }
        tr:nth-child(even) {
            background-color: #F8FAFC;
        }
        .badge-method {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 7pt;
        }
        .badge-get { background: #E0F2FE; color: #0369A1; }
        .badge-post { background: #DCFCE7; color: #15803D; }

        /* Code snippets */
        code {
            font-family: 'Consolas', 'Courier New', monospace;
            background: #F1F5F9;
            color: #BE185D;
            padding: 1px 4px;
            border-radius: 4px;
            font-size: 8pt;
        }
        pre {
            background: #0F172A;
            color: #F8FAFC;
            padding: 10px 12px;
            border-radius: 8px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 7.5pt;
            line-height: 1.45;
            margin: 8px 0;
            overflow-x: auto;
        }

        ul, ol {
            margin-left: 18px;
            margin-bottom: 8px;
        }
        li {
            margin-bottom: 3px;
        }

        .footer-note {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #E2E8F0;
            font-size: 7.5pt;
            color: #94A3B8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- HEADER RESMI -->
    <div class="doc-header">
        <div class="brand">
            <img src="{{LOGO_SRC}}" alt="Digi-Safe Logo">
            <div>
                <div class="brand-title">DIGI-SAFE PURWAKARTA</div>
                <div class="brand-subtitle">Platform Literasi, Komunitas & Mitigasi Keamanan Siber Kab. Purwakarta</div>
            </div>
        </div>
        <div class="badge-doc">DOKUMEN SIDANG & UJI TEKNIS</div>
    </div>

    <!-- COVER / RINGKASAN EKSEKUTIF -->
    <div class="cover-box">
        <span class="cover-tag">PANDUAN PERTANGGUNGJAWABAN SISTEM & PRESENTASI</span>
        <h1>Panduan Narasi & Arsitektur Fitur: "Kenapa Dibuat Seperti Ini dan Bagaimana Cara Kerjanya?"</h1>
        <p>
            Dokumen ini disusun sebagai panduan komprehensif bagi pengembang saat menghadapi sesi tanya-jawab, demonstrasi, maupun evaluasi akademik (Uji Kompetensi Keahlian RPL). Memuat filosofi desain UX/UI, arsitektur teknis Laravel 11, mekanisme keamanan autentikasi node-based, serta skrip presentasi lisan siap pakai.
        </p>
        <div class="meta-grid">
            <div class="meta-item">
                <div class="label">Pengembang</div>
                <div class="value">Razief Afkar Rusli</div>
            </div>
            <div class="meta-item">
                <div class="label">Institusi</div>
                <div class="value">SMKN 2 Purwakarta</div>
            </div>
            <div class="meta-item">
                <div class="label">Teknologi Utama</div>
                <div class="value">Laravel 11 + Tailwind + Alpine</div>
            </div>
            <div class="meta-item">
                <div class="label">Fokus Utama</div>
                <div class="value">Node Flowchart Auth & Gamifikasi</div>
            </div>
        </div>
    </div>

    <!-- BAB 1 -->
    <div class="chapter-title">
        <span class="chapter-pill">BAB 1</span>
        <span>Filosofi Desain & Karakteristik "Anti AI-Slop"</span>
    </div>

    <div class="qa-card avoid-break">
        <div class="q-badge">Pertanyaan Konseptual</div>
        <div class="q-text">1. Mengapa Digi-Safe menolak template generik website buatan AI ("AI-Slop")?</div>
        <div class="a-text">
            Banyak website modern yang dihasilkan oleh generator kecerdasan buatan (AI) menggunakan pola yang seragam dan menjemukan: perpaduan warna ungu-biru neon yang menyilaukan mata, efek kaca buram (glassmorphism) yang berat, emoji berhamburan sebagai pengganti ikon, dan teks formal kosong tanpa konteks lokal.
            <br><br>
            <strong>Digi-Safe Purwakarta mengadopsi 4 pilar visual otentik:</strong>
            <ul>
                <li><strong>Palet Warna Terukur:</strong> Sky Blue (<code>#1E88E5</code>) melambangkan transparansi dan ketenangan, Lime Cyber (<code>#84CC16</code>) sebagai aksen keberhasilan dan aksi, serta Slate/Hitam pekat (<code>#0F172A</code>) untuk struktur yang tegas.</li>
                <li><strong>Tipografi Berstandar Industri:</strong> Menggunakan Google Fonts <em>Plus Jakarta Sans</em> untuk judul (modern, tegas, elegan) dipadukan dengan <em>Inter</em> untuk body teks (teruji tingkat keterbacaan/readability tinggi pada layar digital).</li>
                <li><strong>Ikonografi Inline SVG Asli:</strong> Menolak penggunaan emoji kartun. Semua ikon menggunakan garis SVG presisi (feather/heroicons style) yang tajam pada resolusi apa pun.</li>
                <li><strong>Micro-Interactions yang Ringan:</strong> Animasi hover, floating cards, dan canvas dot-grid yang efisien tanpa membebani GPU atau memori browser pengguna.</li>
            </ul>
        </div>
    </div>

    <div class="script-card avoid-break">
        <div class="script-badge">🗣️ Kalimat Presentasi ke Penguji (Hafalkan / Ucapkan Ini)</div>
        <div class="script-quote">
            "Bapak/Ibu Penguji, kami sengaja merancang antarmuka Digi-Safe Purwakarta dari nol dengan pendekatan Anti AI-Slop. Kami tidak menggunakan template instan generik yang biasanya dipenuhi gradasi neon menyilaukan dan emoji berlebihan. Kami memilih palet Sky Blue dan Lime yang bersahabat, tipografi Plus Jakarta Sans, serta ikon SVG yang presisi. Pendekatan ini membuat web kami terasa profesional, kredibel, dan ramah bagi warga Purwakarta yang ingin belajar keamanan siber tanpa merasa terintimidasi."
        </div>
    </div>

    <!-- BAB 2: FOKUS UTAMA LOGIN & REGISTER -->
    <div class="page-break"></div>
    <div class="chapter-title">
        <span class="chapter-pill">BAB 2</span>
        <span>Bedah Mendalam: Sistem Autentikasi (Login & Register) [FOKUS UTAMA]</span>
    </div>

    <div class="qa-card avoid-break">
        <div class="q-badge">Pertanyaan Kritis Penguji</div>
        <div class="q-text">2. "Kok bisa bentuk Login dan Registernya seperti Flowchart / Node bercabang? Kenapa tidak pakai form kotak biasa di tengah?"</div>
        <div class="a-text">
            <strong>Latar Belakang & Filosofi Perancangan (Kenapa?):</strong>
            <ol>
                <li><strong>Metafora Keamanan Jaringan (Security Pipeline Metaphor):</strong> Dalam dunia cybersecurity, otentikasi bukan sekadar mengisi formulir kertas, melainkan sebuah <em>data verification pipeline</em>. Data user berjalan dari node identitas (Username/Email) melewati gerbang sandi (Password Node) hingga diverifikasi di server. Desain flowchart ini memvisualisasikan perjalanan paket data tersebut.</li>
                <li><strong>Mereduksi Beban Mental Pengguna (Cognitive Load Reduction):</strong> Form kotak vertikal konvensional sering memicu rasa malas (<em>form fatigue</em>) karena terlihat seperti dokumen birokrasi. Dengan memecah input ke dalam "kartu node mandiri" yang dihubungkan garis SVG melengkung, pengguna merasakan sensasi interaktif seperti menyusun alur kerja di platform modern (Figma, Miro, atau Whimsical).</li>
                <li><strong>Kanvas Arsitektur Digital (Dot-Grid Canvas #F8FAFC):</strong> Latar belakang abu sangat muda dengan titik-titik halus (dot pattern) menghadirkan nuansa blueprint teknik perangkat lunak yang bersih, rapi, dan berbobot.</li>
            </ol>
        </div>
    </div>

    <!-- DIAGRAM VISUAL NODE FLOW -->
    <div class="node-diagram avoid-break">
        <div class="node-card">
            <strong>Node 1: Root</strong><br>
            Header / Identitas
        </div>
        <div class="node-arrow">─── SVG Curvature ───►</div>
        <div class="node-card">
            <strong>Node 2: Input</strong><br>
            Email / Username
        </div>
        <div class="node-arrow">─── SVG Curvature ───►</div>
        <div class="node-card">
            <strong>Node 3: Security</strong><br>
            Bcrypt Password
        </div>
        <div class="node-arrow">─── SVG Curvature ───►</div>
        <div class="node-card accent">
            <strong>Node 4: Action</strong><br>
            Auth Decision / Submit
        </div>
    </div>

    <div class="qa-card avoid-break">
        <div class="q-badge">Pertanyaan Desain UI/UX</div>
        <div class="q-text">3. "Kenapa ada maskot astronot di samping form pada desktop? Kenapa posisinya berbeda di Login dan Register? Dan kenapa hilang di layar HP?"</div>
        <div class="a-text">
            <strong>Alasan Desain & Psikologi Visual:</strong>
            <ul>
                <li><strong>Halaman Register (Maskot di Sisi Kiri Desktop):</strong> Maskot astronot memegang megafon dan terbang menghadap ke kanan (ke arah kartu pendaftaran). Dalam kaidah <em>gaze cueing</em> dan <em>directional psychology</em>, arah pandang dan gestur maskot secara alami mengarahkan mata pengguna untuk mulai mengisi data diri.</li>
                <li><strong>Halaman Login (Maskot di Sisi Kanan Desktop):</strong> Maskot astronot melayang dengan ekspresi bintang selebrasi di sebelah kanan. Ini memberikan impresi sambutan hangat ("Selamat datang kembali!") bagi pengguna yang sudah terdaftar.</li>
                <li><strong>Mengapa Hilang di Layar HP (Mobile View)?</strong> Di smartphone, ruang layar vertikal sangat terbatas. Memaksa maskot tampil akan mendorong form ke bawah dan memicu scrolling berlebih. Menggunakan kelas Tailwind <code>hidden md:flex</code>, maskot disembunyikan secara cerdas pada mobile agar pengguna dapat langsung mengakses form dalam satu pandangan layar (<em>above the fold</em>).</li>
            </ul>
        </div>
    </div>

    <!-- MASCOT PREVIEW -->
    <div class="mascot-container avoid-break">
        <div class="mascot-card">
            <img src="{{REGISTER_MASCOT_SRC}}" alt="Mascot Register">
            <div class="caption">Halaman Register (Kiri Desktop)<br><em>Astronot Megafon: Ajakan Bergabung</em></div>
        </div>
        <div class="mascot-card">
            <img src="{{LOGIN_MASCOT_SRC}}" alt="Mascot Login">
            <div class="caption">Halaman Login (Kanan Desktop)<br><em>Astronot Bintang: Sambutan Pengguna</em></div>
        </div>
    </div>

    <div class="qa-card avoid-break">
        <div class="q-badge">Pertanyaan Arsitektur Teknis</div>
        <div class="q-text">4. "Bagaimana arsitektur teknis backend-nya di Laravel 11? Kok bisa aman dari serangan siber?"</div>
        <div class="a-text">
            Sistem autentikasi Digi-Safe Purwakarta dibangun secara custom menggunakan controller native (<code>AuthController.php</code>) tanpa ketergantungan library berat seperti Breeze atau Jetstream. Ini menjamin kontrol 100% terhadap tabel database <code>users</code> kustom Digi-Safe:
            <br><br>
            <strong>Langkah & Proteksi Keamanan Backend:</strong>
            <ol>
                <li>
                    <strong>Validasi Input Lapis Ganda:</strong>
                    Mencegah duplikasi username/email dan memastikan password memiliki panjang minimum 8 karakter terkonfirmasi:
                    <pre><code>$validated = $request->validate([
    'nama_lengkap' => 'required|string|max:100',
    'username'     => 'required|string|max:50|unique:users,username|alpha_dash',
    'email'        => 'required|email|max:100|unique:users,email',
    'password'     => 'required|string|min:8|confirmed',
]);</code></pre>
                </li>
                <li>
                    <strong>Enkripsi Password Satu Arah (Bcrypt Hashing):</strong>
                    Password pengguna tidak pernah disimpan dalam format teks biasa (plaintext), melainkan diacak secara matematis melalui <code>Hash::make($request->password)</code> dengan algoritma Bcrypt 12 putaran (rounds). Ini tahan terhadap serangan kamus (dictionary attack) dan pembocoran basis data.
                </li>
                <li>
                    <strong>Pencegahan Serangan Session Fixation:</strong>
                    Saat user berhasil login via <code>Auth::attempt()</code>, sistem mengeksekusi:
                    <pre><code>$request->session()->regenerate();</code></pre>
                    Ini memaksa framework membuat ID sesi baru yang unik dan membuang ID sesi lama, sehingga mencegah peretas membajak sesi login.
                </li>
                <li>
                    <strong>Proteksi CSRF (Cross-Site Request Forgery):</strong>
                    Setiap request HTTP POST wajib melampirkan direktif <code>@csrf</code>. Laravel secara otomatis memverifikasi kecocokan token antara browser dan session server.
                </li>
                <li>
                    <strong>Konsep "Silent Admin" (Privilege Escalation Defense):</strong>
                    Akun administrator (<code>admin@digisafe.id</code>) disuntikkan secara aman melalui <code>DatabaseSeeder.php</code> dengan status <code>role = 'admin'</code>. Form pendaftaran publik hanya memberikan hak akses <code>role = 'user'</code>. Ini menutup celah manipulasi peran pengguna.
                </li>
                <li>
                    <strong>Integrasi Navbar Reaktif:</strong>
                    Navbar global mendeteksi kondisi autentikasi secara otomatis (<code>@auth</code> / <code>@guest</code>). Tombol "Gabung?" secara instan berubah menjadi avatar inisial nama dengan badge peran dan dropdown logout aman melalui HTTP POST.
                </li>
            </ol>
        </div>
    </div>

    <div class="script-card avoid-break">
        <div class="script-badge">🗣️ Skrip Menjawab Soal Login & Register (Sangat Disarankan Diucapkan)</div>
        <div class="script-quote">
            "Izin menjawab Bapak/Ibu Penguji. Alasan kami merombak Login dan Register menjadi bentuk Node Flowchart adalah untuk merepresentasikan konsep arsitektur keamanan siber secara visual. Kami memandang bahwa autentikasi adalah sebuah alur atau pipeline verifikasi data, bukan sekadar formulir kertas biasa. Desain node ini menurunkan beban kognitif pengguna sehingga proses login terasa interaktif dan tidak membosankan.<br><br>
            Dari sisi teknis Laravel 11, kami tidak memakai generator instan. Kami membangun AuthController sendiri yang menerapkan proteksi CSRF, validasi unik alpha-dash, hashing Bcrypt 12 rounds pada password, dan yang terpenting: pemanggilan session()->regenerate() untuk menangkal serangan Session Fixation. Selain itu, kami menerapkan konsep Silent Admin di mana akun admin hanya bisa dibuat melalui database seeding, sehingga tidak ada celah bagi pengguna biasa untuk menaikkan hak aksesnya secara ilegal."
        </div>
    </div>

    <!-- BAB 3: BEDAH FITUR LAINNYA -->
    <div class="page-break"></div>
    <div class="chapter-title">
        <span class="chapter-pill">BAB 3</span>
        <span>Bedah Fitur-Fitur Lainnya di Digi-Safe Purwakarta</span>
    </div>

    <!-- BERANDA -->
    <div class="qa-card avoid-break">
        <div class="q-badge">Halaman Beranda (/)</div>
        <div class="q-text">5. "Kenapa Hero Beranda memakai 3D Curved Perspective Cards dan ada animasi burung/awan?"</div>
        <div class="a-text">
            <strong>Konsep:</strong> Keamanan siber seringkali dipersepsikan sebagai hal yang gelap, rumit, dan menakutkan (klise hacker berjaket hoodie hitam di ruangan temaram).
            <ul>
                <li><strong>Sky Blue & Animasi Burung:</strong> Memberikan nuansa keterbukaan, rasa aman, dan harapan bahwa ruang digital di Purwakarta bisa nyaman untuk semua kalangan.</li>
                <li><strong>3D Perspective Cards Melayang:</strong> Tiga kartu (Sandi Kuat, Deteksi Phishing, Backup Rutin) disusun secara berlapis menggunakan CSS transform 3D untuk melambangkan konsep fundamental keamanan siber: <em>Defense-in-Depth</em> (pertahanan berlapis).</li>
                <li><strong>Bento Grid Tentang & Layanan:</strong> Menggunakan prinsip modularitas informasi. Mata pengguna dapat memindai poin-poin penting dalam hitungan detik tanpa tersesat dalam paragraf panjang.</li>
            </ul>
        </div>
    </div>

    <!-- EDUKASI -->
    <div class="qa-card avoid-break">
        <div class="q-badge">Halaman Edukasi (/edukasi)</div>
        <div class="q-text">6. "Kenapa Edukasi mengarahkan ke jurnal dan artikel eksternal resmi, bukan membuat artikel blog sendiri?"</div>
        <div class="a-text">
            <strong>Alasan Kredibilitas & Tanggung Jawab Akademik:</strong>
            Dalam bidang keamanan siber, informasi yang keliru atau usang dapat berakibat fatal pada kebocoran data pengguna. Menghubungkan platform ke rujukan primer (seperti portal resmi BSSN, Siberkreasi Kominfo, dan jurnal terakreditasi) menjamin bahwa literasi yang disajikan adalah standar resmi yang sahih dan dapat dipertanggungjawabkan secara hukum.
            <br>
            Desain Bento Grid dilengkapi dengan indikator kategori (Phishing, Malware, Enkripsi) dan estimasi waktu baca (3-5 menit) agar warga dapat belajar efisien sesuai kebutuhan.
        </div>
    </div>

    <!-- MINIGAME -->
    <div class="qa-card avoid-break">
        <div class="q-badge">Halaman Minigame (/minigame)</div>
        <div class="q-text">7. "Kenapa ada Minigame di web keamanan siber? Apakah hanya pemanis belaka?"</div>
        <div class="a-text">
            <strong>Sama sekali bukan pemanis, melainkan penerapan metode <em>Experiential Learning & Gamification</em>:</strong>
            Riset pedagogi membuktikan bahwa seseorang mengingat materi hingga 75% lebih baik ketika mengalami simulasi langsung dibanding hanya membaca teks pasif.
            <br><br>
            <strong>Tiga Simulasi Game Interaktif:</strong>
            <ol>
                <li><strong>Phishing Hunter:</strong> Pengguna bertindak sebagai analis email untuk memeriksa keabsahan pengirim, domain palsu, dan manipulasi psikologis urgensi hadiah.</li>
                <li><strong>Cyber Defender:</strong> Pengguna dihadapkan pada timer real-time untuk mengambil keputusan mitigasi cepat saat server diserang ransomware atau DDoS.</li>
                <li><strong>Link or Trap?:</strong> Melatih ketelitian pengguna mendeteksi serangan <em>typosquatting</em> (misal: membedakan <code>klikbca.com</code> vs <code>k1ikbca.com</code>).</li>
            </ol>
            <br>
            <strong>Arsitektur Teknis Client-Server:</strong> Game logic berjalan secara responsif di sisi client menggunakan Alpine.js tanpa latensi jaringan. Saat permainan usai, skor dikirim secara asynchronous melalui endpoint AJAX <code>POST /minigame/save-score</code> ke <code>MinigameController</code> dan disimpan ke tabel <code>skor_game</code>. Statistik, grafik performa mingguan, dan leaderboard otomatis terbarui secara instan.
        </div>
    </div>

    <!-- FORUM KOMUNITAS -->
    <div class="qa-card avoid-break">
        <div class="q-badge">Halaman Forum Komunitas (/forum)</div>
        <div class="q-text">8. "Kenapa ada lekukan kurva cekung di bawah hero Forum? Dan kenapa form jawaban memakai modal popup?"</div>
        <div class="a-text">
            <strong>Efek Visual Kurva Cekung (Funnel Transition):</strong>
            Lekukan kurva cekung SVG di bawah hero berfungsi sebagai <em>visual funnel</em> (corong visual). Secara estetika, ia memandu mata pengguna yang sedang membaca judul sambutan di hero putih untuk mengalir masuk ke arena kartu-kartu diskusi komunitas di bawahnya.
            <br><br>
            <strong>Mengapa Modal Popup "Pendapat?" via Alpine.js & AJAX?</strong>
            Membalas di halaman baru akan menyebabkan <em>page reload</em> dan menghilangkan posisi scroll pengguna. Dengan modal popup Alpine.js, pengguna tetap berada di konteks diskusi yang sama, mengetik jawaban, dan data tersimpan via AJAX ke <code>/forum/{id}/jawaban</code> tanpa reload halaman sedikit pun.
            <br><br>
            <strong>Admin Verified Badge:</strong> Setiap jawaban dari akun admin otomatis diberi lencana khusus warna Lime/Biru untuk membedakan panduan teknis resmi dari opini pengguna umum.
        </div>
    </div>

    <!-- TESTIMONI -->
    <div class="qa-card avoid-break">
        <div class="q-badge">Halaman Testimoni (/testimoni)</div>
        <div class="q-text">9. "Kenapa ada ikon-ikon aplikasi melayang blur (Spotify, Discord, Roblox)? Dan bagaimana sistem testimoninya?"</div>
        <div class="a-text">
            <strong>Relevansi Kontekstual:</strong>
            Kejahatan siber di kalangan pelajar dan masyarakat Purwakarta paling sering terjadi pada akun-akun media sosial, game, dan hiburan (seperti pencurian akun Roblox, phishing akun Discord, dan pembajakan Spotify). Menghadirkan ikon-ikon tersebut dengan efek <em>depth-of-field blur</em> mengingatkan pengunjung bahwa platform Digi-Safe relevan dengan ekosistem digital yang mereka gunakan sehari-hari.
            <br><br>
            <strong>Dua Kolom Marquee Vertikal & Form Bintang Interaktif:</strong>
            Ulasan warga ditampilkan melalui CSS vertical marquee yang bergerak halus otomatis. Pengguna dapat menekan tombol "+ Tulis Ulasan Sekarang" untuk membuka modal interaktif pemberian bintang 1-5 dan ulasan yang langsung divalidasi dan tersimpan di database <code>testimoni</code>.
        </div>
    </div>

    <!-- TENTANG KAMI -->
    <div class="qa-card avoid-break">
        <div class="q-badge">Halaman Tentang Kami (/tentang)</div>
        <div class="q-text">10. "Kenapa halaman Tentang didesain menyerupai Lembar Kerja Dokumen Pengolah Kata (Tentang_DigiSafe.docx)?"</div>
        <div class="a-text">
            <strong>Filosofi Living Document & Transparansi Akademik:</strong>
            Menampilkan antarmuka dokumen pengolah kata lengkap dengan toolbar (undo, font dropdown, bold, highlight) melambangkan bahwa proyek Digi-Safe Purwakarta adalah sebuah <em>dokumen terbuka yang terus berkembang dan dapat dipertanggungjawabkan risetnya</em>.
            <br><br>
            Halaman ini juga menyematkan profil pengembang (Razief Afkar Rusli - XII RPL SMKN 2 Purwakarta), media sosial, serta Google Maps interaktif SMKN 2 Purwakarta untuk menegaskan identitas lokal dan akuntabilitas pencipta sistem.
        </div>
    </div>

    <!-- BAB 4: SKRIP PRESENTASI LISAN -->
    <div class="page-break"></div>
    <div class="chapter-title">
        <span class="chapter-pill">BAB 4</span>
        <span>Lembar Contekan Presentasi Lisan (Script Siap Pakai untuk Sidang/Demo)</span>
    </div>

    <p>Gunakan format skrip berikut saat mempresentasikan karya Anda di hadapan Guru Pembimbing, Penguji UKK, atau Audiens:</p>

    <div class="script-card avoid-break">
        <div class="script-badge">Bagian 1: Kalimat Pembuka Presentasi</div>
        <div class="script-quote">
            "Assalamu’alaikum Wr. Wb. Selamat pagi/siang Bapak dan Ibu Penguji. Perkenalkan, saya <strong>Razief Afkar Rusli</strong> dari kelas XII Rekayasa Perangkat Lunak SMKN 2 Purwakarta. Pada kesempatan kali ini, saya bangga mempersembahkan proyek web <strong>Digi-Safe Purwakarta</strong>, sebuah platform komprehensif yang memadukan literasi, gamifikasi interaktif, forum komunitas, dan sistem autentikasi modern untuk memperkuat ketahanan digital warga Kabupaten Purwakarta."
        </div>
    </div>

    <div class="script-card avoid-break">
        <div class="script-badge">Bagian 2: Menjelaskan Fitur Autentikasi Login & Register</div>
        <div class="script-quote">
            "Saat masuk ke menu Login atau Registrasi, Bapak/Ibu akan melihat inovasi antarmuka yang sangat unik. Alih-alih formulir kotak biasa yang monoton, kami merancangnya sebagai <strong>Node Flowchart</strong>. Ini adalah visualisasi dari <em>Cybersecurity Data Pipeline</em>, di mana setiap field data merupakan simpul verifikasi yang saling bertautan.<br><br>
            Pada desktop, kami menempatkan maskot astronot secara terarah: di halaman Register maskot memegang megafon mengajak bergabung, sedangkan di halaman Login maskot menyambut hangat pengguna lama. Di layar ponsel, maskot disembunyikan otomatis agar pengguna langsung nyaman mengetik.<br><br>
            Secara backend di Laravel 11, sistem ini menerapkan standar keamanan ketat: enkripsi Bcrypt 12 rounds, validasi alpha-dash, proteksi token CSRF, dan pemanggilan <code>session()->regenerate()</code> untuk menghentikan serangan Session Fixation. Kami juga menerapkan konsep <em>Silent Admin</em> agar akun admin tidak bisa dieksploitasi oleh publik."
        </div>
    </div>

    <div class="script-card avoid-break">
        <div class="script-badge">Bagian 3: Menjelaskan Minigame & Gamifikasi</div>
        <div class="script-quote">
            "Salah satu keunggulan terbesar Digi-Safe adalah adanya <strong>Pusat Minigame</strong>. Berdasarkan riset psikologi belajar, masyarakat lebih cepat paham bahaya siber jika mengalaminya langsung. Kami membangun tiga simulasi: <em>Phishing Hunter</em> untuk membedakan email palsu, <em>Cyber Defender</em> untuk reflek melawan malware, dan <em>Link or Trap?</em> untuk membongkar domain jebakan. Game ini dibangun dengan Alpine.js yang sangat responsif, dan seluruh riwayat nilai tersimpan ke basis data secara asynchronous melalui AJAX tanpa reload halaman."
        </div>
    </div>

    <div class="script-card avoid-break">
        <div class="script-badge">Bagian 4: Menjelaskan Forum, Testimoni & Halaman Tentang</div>
        <div class="script-quote">
            "Untuk mendorong kolaborasi warga, kami menyediakan <strong>Forum Komunitas</strong> dengan efek kurva corong dan modal interaktif yang memungkinkan tanya-jawab instan tanpa berpindah halaman, lengkap dengan verifikasi jawaban admin. Kami juga menghadirkan halaman <strong>Testimoni</strong> dengan animasi marquee ulasan nyata, serta halaman <strong>Tentang Kami</strong> berdesain editor dokumen yang transparan dan mencantumkan profil saya beserta lokasi SMKN 2 Purwakarta melalui integrasi Google Maps."
        </div>
    </div>

    <div class="script-card avoid-break">
        <div class="script-badge">Bagian 5: Kalimat Penutup Presentasi</div>
        <div class="script-quote">
            "Kesimpulannya, Digi-Safe Purwakarta bukan hanya proyek web yang berfungsi secara teknis, tetapi dibangun dengan filosofi desain yang matang, bebas dari template AI yang klise, dan menerapkan prinsip arsitektur perangkat lunak yang aman serta ramah pengguna. Terima kasih atas perhatian Bapak dan Ibu Penguji, saya sangat terbuka untuk menerima pertanyaan dan masukan."
        </div>
    </div>

    <!-- BAB 5: TABEL PEMETAAN TEKNIS -->
    <div class="page-break"></div>
    <div class="chapter-title">
        <span class="chapter-pill">BAB 5</span>
        <span>Tabel Pemetaan Lengkap Arsitektur Teknis Sistem</span>
    </div>

    <table class="avoid-break">
        <thead>
            <tr>
                <th style="width: 18%;">URL Endpoint</th>
                <th style="width: 8%;">Metode</th>
                <th style="width: 22%;">Controller & Method</th>
                <th style="width: 15%;">Model / DB</th>
                <th style="width: 37%;">Fungsi & Fitur Keamanan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>/</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>BerandaController@index</code></td>
                <td>-</td>
                <td>Landing page dengan 3D perspective cards, bento grid, dan responsive sticky navbar.</td>
            </tr>
            <tr>
                <td><code>/edukasi</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>EdukasiController@index</code></td>
                <td>-</td>
                <td>Bento hub kurasi literasi terpercaya dari portal otoritatif (BSSN/Kominfo).</td>
            </tr>
            <tr>
                <td><code>/login</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>AuthController@loginView</code></td>
                <td><code>User</code></td>
                <td>Tampilan node flowchart login, canvas dot-grid, maskot astronot kanan.</td>
            </tr>
            <tr>
                <td><code>/login</code></td>
                <td><span class="badge-method badge-post">POST</span></td>
                <td><code>AuthController@login</code></td>
                <td><code>User</code> (users)</td>
                <td>Verifikasi kredensial, proteksi CSRF, <code>session()->regenerate()</code>, error flash.</td>
            </tr>
            <tr>
                <td><code>/register</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>AuthController@registerView</code></td>
                <td><code>User</code></td>
                <td>Tampilan node flowchart register, canvas dot-grid, maskot astronot kiri.</td>
            </tr>
            <tr>
                <td><code>/register</code></td>
                <td><span class="badge-method badge-post">POST</span></td>
                <td><code>AuthController@register</code></td>
                <td><code>User</code> (users)</td>
                <td>Validasi unique, <code>Hash::make()</code> Bcrypt, pembuatan akun role user, auto-login.</td>
            </tr>
            <tr>
                <td><code>/logout</code></td>
                <td><span class="badge-method badge-post">POST</span></td>
                <td><code>AuthController@logout</code></td>
                <td>-</td>
                <td><code>Auth::logout()</code>, invalidasi sesi, regenerasi token CSRF.</td>
            </tr>
            <tr>
                <td><code>/minigame</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>MinigameController@index</code></td>
                <td><code>SkorGame</code></td>
                <td>Dashboard gamifikasi, riwayat bermain user, grafik CSS bar, leaderboard.</td>
            </tr>
            <tr>
                <td><code>/minigame/{type}</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>MinigameController@play</code></td>
                <td><code>SoalMinigame</code></td>
                <td>Simulasi game interaktif (Phishing Hunter, Cyber Defender, Link or Trap?).</td>
            </tr>
            <tr>
                <td><code>/minigame/save-score</code></td>
                <td><span class="badge-method badge-post">POST</span></td>
                <td><code>MinigameController@saveScore</code></td>
                <td><code>SkorGame</code> (skor_game)</td>
                <td>AJAX endpoint penyimpanan skor asynchronous dengan verifikasi session aktif.</td>
            </tr>
            <tr>
                <td><code>/forum</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>ForumController@index</code></td>
                <td><code>Forum</code>, <code>JawabanForum</code></td>
                <td>Daftar topik diskusi, filter kategori, hero kurva cekung, modal AJAX.</td>
            </tr>
            <tr>
                <td><code>/forum/store</code></td>
                <td><span class="badge-method badge-post">POST</span></td>
                <td><code>ForumController@store</code></td>
                <td><code>Forum</code> (forum)</td>
                <td>Penyimpanan topik diskusi baru dengan validasi panjang dan sanitasi input.</td>
            </tr>
            <tr>
                <td><code>/forum/{id}/jawaban</code></td>
                <td><span class="badge-method badge-post">POST</span></td>
                <td><code>ForumController@storeJawaban</code></td>
                <td><code>JawabanForum</code></td>
                <td>Pengiriman opini/solusi via AJAX tanpa reload halaman; deteksi role admin.</td>
            </tr>
            <tr>
                <td><code>/testimoni</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>TestimoniController@index</code></td>
                <td><code>Testimoni</code></td>
                <td>Tampilan 3D depth-of-field icon squircle, vertical marquee, modal rating bintang.</td>
            </tr>
            <tr>
                <td><code>/testimoni/store</code></td>
                <td><span class="badge-method badge-post">POST</span></td>
                <td><code>TestimoniController@store</code></td>
                <td><code>Testimoni</code> (testimoni)</td>
                <td>Validasi nama, instansi, ulasan, rating 1-5 bintang, status auto-approved.</td>
            </tr>
            <tr>
                <td><code>/tentang</code></td>
                <td><span class="badge-method badge-get">GET</span></td>
                <td><code>TentangController@index</code></td>
                <td>-</td>
                <td>Tampilan editor dokumen <code>Tentang_DigiSafe.docx</code>, profil, embed Google Maps.</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen ini dibuat secara resmi untuk mendokumentasikan sistem Digi-Safe Purwakarta &copy; 2026. Seluruh hak cipta dilindungi undang-undang.
    </div>

</body>
</html>
HTML;

// Replace placeholders
$html = str_replace(
    ['{{LOGO_SRC}}', '{{LOGIN_MASCOT_SRC}}', '{{REGISTER_MASCOT_SRC}}'],
    [$logoSrc, $loginMascotSrc, $registerMascotSrc],
    $html
);

file_put_contents($docsDir . '/Penjelasan_Sistem_DigiSafe_Purwakarta.html', $html);
echo "HTML generated successfully at: " . $docsDir . '/Penjelasan_Sistem_DigiSafe_Purwakarta.html' . PHP_EOL;

// Convert to PDF using Chrome Headless
$chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
$htmlFile = $docsDir . '/Penjelasan_Sistem_DigiSafe_Purwakarta.html';
$pdfFile = $docsDir . '/Penjelasan_Sistem_DigiSafe_Purwakarta.pdf';
$rootPdfFile = 'c:\\xampp\\htdocs\\digi-safe-purwakarta\\Penjelasan_Sistem_DigiSafe_Purwakarta.pdf';

$cmd = "\"{$chromePath}\" --headless=new --disable-gpu --no-pdf-header-footer --print-to-pdf=\"{$pdfFile}\" \"{$htmlFile}\"";
echo "Executing Chrome PDF generation..." . PHP_EOL;
exec($cmd, $output, $returnCode);

if (file_exists($pdfFile)) {
    $size = filesize($pdfFile);
    echo "PDF generated successfully! Size: " . $size . " bytes." . PHP_EOL;
    copy($pdfFile, $rootPdfFile);
    echo "Copied PDF to project root: " . $rootPdfFile . PHP_EOL;
} else {
    echo "Error generating PDF. Return code: " . $returnCode . PHP_EOL;
}
