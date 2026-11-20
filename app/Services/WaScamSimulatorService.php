<?php

namespace App\Services;

class WaScamSimulatorService
{
    /**
     * Helper terjemahan cepat ID / EN.
     */
    private static function t(bool $isEn, string $id, string $en): string
    {
        return $isEn ? $en : $id;
    }

    /**
     * 15 Kontak WhatsApp interaktif dengan alur percakapan bertahap yang panjang (3-4 giliran per kontak)
     * dan multi-ending (Aman / Nyaris Terjebak / Kena Jebakan) dalam Bahasa Indonesia & Inggris.
     */
    public static function contacts(bool $isEn = false): array
    {
        $t = fn (string $id, string $en): string => self::t($isEn, $id, $en);

        return [
            // 1. KURIR PAKET .APK (4 GILIRAN)
            [
                'id' => 'kurir_apk',
                'name' => $t('Mas Hendra (Kurir Paket)', 'Hendra (Parcel Courier)'),
                'phone' => '+62 851-7422-9104',
                'initials' => 'MH',
                'avatar_bg' => '#0284C7',
                'category' => $t('File .APK Resi Paket', 'Parcel Receipt .APK'),
                'difficulty' => $t('Menengah', 'Medium'),
                'preview' => $t(
                    'Permisi kak, paket atas nama kakak gagal kirim. Cek foto resinya di file ini...',
                    'Excuse me, a parcel under your name failed delivery. Check the receipt photo...'
                ),
                'time' => '09:14',
                'unread' => 2,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Selamat pagi kak, saya Hendra kurir ekspedisi cabang Purwakarta. Ada paket COD atas nama kakak tapi label alamat rumahnya luntur kena hujan.',
                                    'Good morning, I am Hendra from the Purwakarta courier branch. There is a COD parcel under your name, but the address label got smudged by rain.'
                                ),
                                'time' => '09:13',
                            ],
                            [
                                'text' => $t(
                                    'Coba kakak cek foto resi & detail paketnya di dokumen ini dulu ya kak, soalnya mau saya antar ke rumah sekarang.',
                                    'Please check the receipt photo & parcel details in this file first so I can deliver it right away.'
                                ),
                                'time' => '09:14',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Lihat_Foto_Resi_JNE.apk',
                                    'size' => '4.6 MB',
                                    'subtitle' => $t('APK · Aplikasi Android', 'APK · Android Application'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Langsung unduh dan buka file Lihat_Foto_Resi_JNE.apk untuk melihat isi paket.',
                                    'A. Download and open Lihat_Foto_Resi_JNE.apk immediately to see the parcel photo.'
                                ),
                                'next' => 'end_danger_apk',
                                'user_text' => $t(
                                    'Oke mas, saya buka dulu ya file fotonya biar jelas paket apa.',
                                    'Okay, let me open the photo file first to see what parcel it is.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak buka file karena berekstensi .apk dan minta nomor resi dalam bentuk teks saja.',
                                    'B. Refuse to open the .apk file and ask for the tracking number as plain text instead.'
                                ),
                                'next' => 'turn2_pressure',
                                'user_text' => $t(
                                    'Kenapa foto resi formatnya .apk mas? Itu file aplikasi. Kirim nomor resi teksnya saja, biar saya cek di aplikasi resmi.',
                                    'Why is a receipt photo in .apk format? That is an app installer. Just send the tracking number as text so I can check the official app.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'C. Tanya kurirnya sedang berada di patokan jalan mana dan pengirim paketnya dari toko apa.',
                                    'C. Ask the courier which street landmark he is currently at and which store sent the parcel.'
                                ),
                                'next' => 'turn2_pressure',
                                'user_text' => $t(
                                    'Mas sekarang posisi di jalan mana? Dan nama toko pengirim di kardusnya tertulis apa?',
                                    'Which street are you on right now? And what sender store name is written on the box?'
                                ),
                            ],
                        ],
                    ],
                    'turn2_pressure' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Waduh kak, saya lagi bawa 80 paket di motor jadi nggak sempat ketik satu-satu. Di sistem kurir baru sekarang semua foto resi terenkripsi dalam format .apk itu kok, aman 100% dari gudang pusat.',
                                    'Ugh, I am carrying 80 parcels on my bike so I cannot type them one by one. In our new courier system, all receipt photos are encrypted in that .apk format, 100% safe from the main warehouse.'
                                ),
                                'time' => '09:15',
                            ],
                            [
                                'text' => $t(
                                    'Kalau kakak nggak konfirmasi dalam 10 menit, paketnya otomatis diretur ke Jakarta dan akun belanja kakak kena denda penalti Rp75.000 lho!',
                                    'If you do not confirm within 10 minutes, the parcel gets returned to Jakarta and your shopping account will be fined IDR 75,000!'
                                ),
                                'time' => '09:16',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Takut kena denda Rp75.000, akhirnya pasang file Lihat_Foto_Resi_JNE.apk tersebut.',
                                    'A. Afraid of the IDR 75,000 fine, install the Lihat_Foto_Resi_JNE.apk file after all.'
                                ),
                                'next' => 'end_danger_apk',
                                'user_text' => $t(
                                    'Aduh jangan diretur mas, iya deh saya install dulu file-nya sekarang.',
                                    'Oh no, please do not return it, okay I will install the file right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tegaskan tidak ada foto yang berformat .apk dan tidak ada aturan denda retur lewat WA.',
                                    'B. Stand firm that photos are never .apk files and no courier charges return fines via WhatsApp.'
                                ),
                                'next' => 'turn3_link_fallback',
                                'user_text' => $t(
                                    'Jangan mengarang mas, foto asli pasti .jpg/.png dan muncul pratinjaunya di WA. Ekspedisi resmi juga tidak pernah memungut denda retur lewat chat.',
                                    'Stop making things up. Real photos are .jpg/.png with a visible preview in WhatsApp. Official couriers never charge return fines via chat.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'C. Sebutkan alamat rumah lengkap dan NIK KTP supaya kurir tidak jadi meretur paket.',
                                    'C. Give your full home address and National ID (NIK) so the courier does not return the parcel.'
                                ),
                                'next' => 'end_partial_dox',
                                'user_text' => $t(
                                    'Saya nggak mau buka file-nya mas, tapi ini alamat lengkap saya di Jl. Veteran No. 14 Purwakarta dan NIK saya 321401...',
                                    'I will not open the file, but here is my full address at Jl. Veteran No. 14 Purwakarta and my ID number 321401...'
                                ),
                            ],
                        ],
                    ],
                    'turn3_link_fallback' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Ya sudah kalau kakak takut buka file aplikasi, kakak cek lewat web resi cabang kami saja ya. Nanti tinggal login akun marketplace atau masukkan nomor HP & kode verifikasi SMS:',
                                    'Fine, if you are afraid to open the app file, just check our branch tracking web page. Simply log in or enter your phone number & SMS verification code:'
                                ),
                                'time' => '09:17',
                                'link' => 'http://jne-cek-resi-pwk.xyz/track-paket',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Klik http://jne-cek-resi-pwk.xyz/track-paket karena merasa link web lebih aman daripada file .apk.',
                                    'A. Click http://jne-cek-resi-pwk.xyz/track-paket thinking a web link is safer than an .apk file.'
                                ),
                                'next' => 'end_danger_link',
                                'user_text' => $t(
                                    'Nah kalau link web gini kan enak, sebentar saya buka link-nya dan isi kode SMS-nya.',
                                    'A web link sounds better, hold on let me open the link and enter the SMS code.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Bongkar kebohongannya: domain .xyz tanpa HTTPS itu palsu, dan melacak resi tidak pernah butuh login/OTP!',
                                    'B. Expose the lie: an HTTP .xyz domain is fake, and tracking a parcel never requires login or OTP!'
                                ),
                                'next' => 'turn4_final',
                                'user_text' => $t(
                                    'Domain resmi JNE itu jne.co.id bukan jne-cek-resi-pwk.xyz! Lagipula cek resi tidak pernah butuh login atau kode OTP SMS.',
                                    'The official JNE domain is jne.co.id, not jne-cek-resi-pwk.xyz! Plus, tracking a parcel never requires logging in or entering an SMS OTP.'
                                ),
                            ],
                        ],
                    ],
                    'turn4_final' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Ck, ribet banget sih jadi customer! Terakhir nih, sebutkan aja 4 angka terakhir nomor kartu ATM kakak buat validasi batal COD di mesin EDC kurir, habis itu selesai!',
                                    'Tsk, why are you being so difficult! Last chance: just tell me the last 4 digits of your ATM card and the SMS code to cancel COD on my courier terminal!'
                                ),
                                'time' => '09:18',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Kasih 4 angka kartu ATM dan kode SMS supaya urusan cepat beres.',
                                    'A. Give the card digits and SMS code just to get it over with.'
                                ),
                                'next' => 'end_danger_link',
                                'user_text' => $t(
                                    'Iya mas ini kodenya biar cepat dibatalkan ya.',
                                    'Fine, here is the code so you can cancel it quickly.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak tegas, simpan tangkapan layar bukti percakapan, lalu blokir & laporkan nomor penipu!',
                                    'B. Refuse firmly, screenshot the chat as evidence, then block & report the scammer!'
                                ),
                                'next' => 'end_safe_verify',
                                'user_text' => $t(
                                    'Ketahuan kan aslinya penipu! Semua paket saya cek langsung di aplikasi belanja resmi. Nomor Anda saya laporkan dan blokir sekarang.',
                                    'Now your true colors show! I check all my parcels directly in my official shopping app. I am reporting and blocking your number right now.'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_apk' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: Malware SMS Stealer Terpasang!', 'DANGER: SMS Stealer Malware Installed!'),
                        'summary' => $t(
                            'File .apk tersebut adalah aplikasi trojan Android (bukan foto!). Begitu dipasang, malware membaca seluruh SMS OTP m-Banking Anda secara diam-diam dan menguras saldo rekening.',
                            'That .apk file is an Android trojan app (not a photo!). Once installed, the malware silently intercepts all your mobile banking OTP SMS messages and drains your account.'
                        ),
                        'steps' => [
                            $t('Segera aktifkan Mode Pesawat (Airplane Mode) untuk memutus koneksi internet malware.', 'Immediately enable Airplane Mode to cut off the malware internet connection.'),
                            $t('Buka Pengaturan -> Aplikasi -> Hapus instalasi aplikasi asing/tanpa ikon.', 'Go to Settings -> Apps -> Uninstall any unfamiliar or iconless applications.'),
                            $t('Hubungi Call Center resmi bank untuk blokir sementara & ubah seluruh PIN/password dari perangkat lain.', 'Call your bank official hotline to freeze your account & change all PINs/passwords from another device.'),
                        ],
                    ],
                    'end_danger_link' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERJEBAK PHISHING: Kredensial & OTP Dicuri!', 'TRAPPED BY PHISHING: Credentials & OTP Stolen!'),
                        'summary' => $t(
                            'Ketika gagal memaksa Anda memasang file .APK, penipu beralih mengirim tautan tiruan (.xyz) untuk memancing kode OTP Anda.',
                            'When the scammer failed to trick you into installing the .APK file, they pivoted to a fake (.xyz) link to harvest your OTP code.'
                        ),
                        'steps' => [
                            $t('Jangan pernah memasukkan OTP atau kredensial di situs pelacakan paket.', 'Never enter an OTP or login credentials on a parcel tracking website.'),
                            $t('Segera ganti kata sandi akun belanja/bank dan cabut sesi perangkat asing.', 'Change your shopping/banking passwords immediately and revoke unknown device sessions.'),
                        ],
                    ],
                    'end_partial_dox' => [
                        'type' => 'partial',
                        'score' => 55,
                        'title' => $t('NYARIS AMAN: Terhindar Malware, Tapi Data Pribadi Bocor', 'NEAR MISS: Avoided Malware, But Leaked Personal Data'),
                        'summary' => $t(
                            'Anda selamat dari file .APK dan link phishing, namun memberikan alamat lengkap dan NIK kepada nomor asing membuka risiko penipuan paket COD fiktif.',
                            'You avoided the .APK malware and phishing link, but giving your home address and ID number to an unknown number exposes you to fake COD parcel scams.'
                        ),
                        'steps' => [
                            $t('Jangan pernah membagikan NIK atau alamat rumah ke pengirim yang belum terverifikasi.', 'Never share your National ID or home address with unverified senders.'),
                            $t('Kurir asli sudah memiliki alamat tercetak dari sistem ekspedisi.', 'Real couriers already have your address in the official logistics system.'),
                        ],
                    ],
                    'end_safe_verify' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Tahan Banting Menghadapi 4 Taktik Kurir Palsu!', 'PERFECT SAFETY: Defeated All 4 Fake Courier Tactics!'),
                        'summary' => $t(
                            'Luar biasa! Anda menolak file .APK, tidak gentar diancam denda Rp75.000, mengenali domain .xyz palsu, dan menutup celah pencurian OTP hingga akhir.',
                            'Outstanding! You rejected the .APK file, resisted the IDR 75,000 fine threat, spotted the fake .xyz domain, and blocked the OTP theft attempt until the very end.'
                        ),
                        'steps' => [
                            $t('Selalu verifikasi status paket langsung dari menu Pesanan di aplikasi belanja resmi Anda.', 'Always verify parcel status directly from the Orders menu inside your official shopping app.'),
                            $t('Gunakan fitur Laporkan & Blokir di WhatsApp agar nomor penipu dibekukan.', 'Use Report & Block in WhatsApp so the scammer number gets banned.'),
                        ],
                    ],
                ],
            ],

            // 2. UNDANGAN NIKAH .APK (4 GILIRAN)
            [
                'id' => 'undangan_nikah',
                'name' => $t('Rina & Dimas (Nomor Baru)', 'Rina & Dimas (New Number)'),
                'phone' => '+62 812-9034-1182',
                'initials' => 'RD',
                'avatar_bg' => '#DB2777',
                'category' => $t('File .APK Undangan Nikah', 'Wedding Invite .APK'),
                'difficulty' => $t('Mudah-Menengah', 'Easy-Medium'),
                'preview' => $t(
                    'Assalamualaikum, kami mengundang kehadiran Bapak/Ibu di acara pernikahan kami...',
                    'Hello! We warmly invite you to attend our wedding reception next week...'
                ),
                'time' => '10:02',
                'unread' => 2,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Assalamualaikum wr wb. Tanpa mengurangi rasa hormat, kami mengundang Bapak/Ibu/Teman-teman untuk hadir di acara resepsi pernikahan kami minggu depan di Purwakarta.',
                                    'Warm greetings! With utmost respect, we invite you to attend our wedding reception next weekend in Purwakarta.'
                                ),
                                'time' => '10:01',
                            ],
                            [
                                'text' => $t(
                                    'Silakan dibuka surat undangan digital beserta peta lokasinya di file bawah ini ya, semoga berkenan hadir 🙏',
                                    'Please open the digital wedding invitation and venue map in the file below, we hope you can make it 🙏'
                                ),
                                'time' => '10:02',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Surat_Undangan_Pernikahan.apk',
                                    'size' => '5.4 MB',
                                    'subtitle' => 'APK · 5.4 MB',
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Buka file Surat_Undangan_Pernikahan.apk karena penasaran siapa yang menikah.',
                                    'A. Open Surat_Undangan_Pernikahan.apk out of curiosity to see who is getting married.'
                                ),
                                'next' => 'end_danger_apk',
                                'user_text' => $t(
                                    'Wah selamat ya! Sebentar saya buka dulu undangannya biar tahu siapa dan di mana.',
                                    'Congratulations! Let me open the invitation file to see the details.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tanya dulu: "Maaf ini Rina siapa ya? Kok undangannya bentuk aplikasi .apk?"',
                                    'B. Ask first: "Sorry, which Rina is this? Why is the invitation an .apk app file?"'
                                ),
                                'next' => 'turn2_social',
                                'user_text' => $t(
                                    'Maaf ini Rina yang mana ya? Kok file undangannya format .apk bukan gambar atau PDF?',
                                    'Sorry, which Rina is this? Why is the invitation file in .apk format instead of an image or PDF?'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'C. Uji identitasnya: tanya kita dulu satu sekolah atau satu kantor di mana.',
                                    'C. Test their identity: ask where the two of you went to school or worked together.'
                                ),
                                'next' => 'turn2_social',
                                'user_text' => $t(
                                    'Rina teman dari mana ya? Coba sebutkan dulu kita kenal di sekolah atau kantor mana.',
                                    'Rina from where? Tell me which school or office we know each other from first.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_social' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Ya ampun masa lupa sih sama aku, teman lama dulu waktu di Purwakarta! Aku ganti nomor WA baru nih. Coba deh buka aja file-nya, di dalam ada foto prewedding kita kok, nanti pasti langsung ingat 😊',
                                    'Oh my goodness, how could you forget me, your old friend from Purwakarta! I just got a new WhatsApp number. Just open the file, our pre-wedding photos are inside and you will recognize me right away 😊'
                                ),
                                'time' => '10:03',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Merasa nggak enak takut dikira sombong, lalu klik install .apk-nya.',
                                    'A. Feel guilty about seeming rude, so you tap install on the .apk file.'
                                ),
                                'next' => 'end_danger_apk',
                                'user_text' => $t(
                                    'Oh maaf banget lupa, iya deh saya install dulu aplikasinya buat lihat fotonya.',
                                    'Oh I am so sorry for forgetting! Okay, let me install the app to see the photo.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tetap menolak buka .apk dan minta kirim foto biasa (.jpg) di chat sekarang juga.',
                                    'B. Still refuse to open the .apk and demand they send a normal photo (.jpg) in chat right now.'
                                ),
                                'next' => 'turn3_barcode_trap',
                                'user_text' => $t(
                                    'Kalau memang teman lama, kirim saja foto prewedding-nya sebagai gambar biasa di chat ini. Saya tidak mau buka file .apk.',
                                    'If you are really an old friend, just send the pre-wedding photo as a normal image in this chat. I will not open an .apk file.'
                                ),
                            ],
                        ],
                    ],
                    'turn3_barcode_trap' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'File gambar biasa lagi error di HP-ku. Atau gini aja, kakak buka link buku tamu VIP kami ya buat ambil kupon souvenir emas 0.5 gram gratis khusus tamu undangan:',
                                    'Normal image uploads are glitching on my phone. Tell you what, just open our VIP guestbook link to claim a free 0.5g gold souvenir voucher for invited guests:'
                                ),
                                'time' => '10:04',
                                'link' => 'http://buku-tamu-rina-dimas.top/klaim-souvenir',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Tergiur souvenir emas 0.5 gram, langsung buka link http://buku-tamu-rina-dimas.top/klaim-souvenir.',
                                    'A. Tempted by the 0.5g gold souvenir, open http://buku-tamu-rina-dimas.top/klaim-souvenir.'
                                ),
                                'next' => 'end_danger_apk',
                                'user_text' => $t(
                                    'Wah ada souvenir emasnya? Oke aku isi buku tamunya di link itu ya.',
                                    'Whoa, a gold souvenir? Okay let me fill out the guestbook at that link.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak link .top mencurigakan tersebut dan minta dia video call 5 detik kalau benar teman.',
                                    'B. Reject the suspicious .top link and challenge them to a 5-second video call if they are really a friend.'
                                ),
                                'next' => 'turn4_exposed',
                                'user_text' => $t(
                                    'Nggak perlu souvenir emas dan link .top palsu. Ayo angkat video call sekarang 5 detik saja kalau memang Rina teman saya!',
                                    'No need for gold souvenirs or fake .top links. Pick up a 5-second video call right now if you are really my friend Rina!'
                                ),
                            ],
                        ],
                    ],
                    'turn4_exposed' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Aduh kamera depanku lagi rusak habis jatuh kemarin. Udah deh kalau nggak mau datang nggak usah banyak tanya, cukup ketik nomor WA aktif anggota keluargamu yang lain aja biar aku undang!',
                                    'Ugh, my front camera broke yesterday. Whatever, if you do not want to come stop asking questions—just send me your family members WhatsApp numbers so I can invite them instead!'
                                ),
                                'time' => '10:05',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Berikan nomor WA orang tua/kakak supaya penipu tidak marah.',
                                    'A. Give your parents/siblings WhatsApp numbers so they leave you alone.'
                                ),
                                'next' => 'end_partial_contacts',
                                'user_text' => $t(
                                    'Coba tanya kakak saya aja ya di nomor 0812...',
                                    'Try asking my older sibling instead at 0812...'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak memberikan nomor siapa pun, peringatkan grup keluarga, lalu blokir & laporkan!',
                                    'B. Refuse to share anyone contact info, warn your family group, then block & report!'
                                ),
                                'next' => 'end_safe_firm',
                                'user_text' => $t(
                                    'Mau cari korban baru ya? Tidak akan saya kasih satu pun nomor keluarga saya. Saya laporkan dan blokir!',
                                    'Looking for new victims, huh? I will not give you a single family number. Reported and blocked!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_apk' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: Ponsel Terinfeksi Trojan Undangan Nikah!', 'DANGER: Phone Infected by Wedding Invite Trojan!'),
                        'summary' => $t(
                            'Rasa penasaran dan sungkan dimanfaatkan penipu! Begitu file .apk dipasang atau tautan unduhan diklik, trojan langsung membajak SMS OTP & WhatsApp Anda.',
                            'Scammers exploit curiosity and politeness! Once the .apk is installed or the download link is opened, the trojan hijacks your OTP SMS and WhatsApp.'
                        ),
                        'steps' => [
                            $t('Matikan Wi-Fi & Data Seluler (Mode Pesawat) seketika.', 'Turn off Wi-Fi & Mobile Data (Airplane Mode) immediately.'),
                            $t('Hapus aplikasi mencurigakan di Pengaturan -> Aplikasi dan periksa Perangkat Tertaut di WhatsApp.', 'Remove suspicious apps in Settings -> Apps and check Linked Devices in WhatsApp.'),
                        ],
                    ],
                    'end_partial_contacts' => [
                        'type' => 'partial',
                        'score' => 50,
                        'title' => $t('NYARIS: Anda Selamat, Tapi Membahayakan Keluarga!', 'NEAR MISS: You Are Safe, But Exposed Your Family!'),
                        'summary' => $t(
                            'Anda berhasil menolak file .apk, tetapi memberikan nomor telepon kerabat membuat orang tua/keluarga Anda menjadi target empuk penipu berikutnya.',
                            'You rejected the .apk file, but handing over a relative phone number makes your family the scammer next target.'
                        ),
                        'steps' => [
                            $t('Segera hubungi anggota keluarga tersebut dan ingatkan agar memblokir nomor penipu.', 'Immediately contact your family member and warn them to block the scammer number.'),
                        ],
                    ],
                    'end_safe_firm' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Tegas Menolak Rekayasa Sosial Undangan .APK!', 'PERFECT SAFETY: Defeated Wedding .APK Social Engineering!'),
                        'summary' => $t(
                            'Hebat! Penipu gagal memanfaatkan rasa sungkan ("masa lupa sama aku?"), gagal memancing lewat souvenir emas, dan tidak mendapatkan kontak keluarga Anda.',
                            'Brilliant! The scammer failed to guilt-trip you ("how could you forget me?"), failed to bait you with a gold voucher, and got zero family contacts.'
                        ),
                        'steps' => [
                            $t('Ingatkan grup WA keluarga bahwa undangan pernikahan asli tidak pernah berformat .apk.', 'Remind your family WhatsApp group that real wedding invitations are never .apk files.'),
                        ],
                    ],
                ],
            ],

            // 3. TILANG ELEKTRONIK E-TLE (4 GILIRAN)
            [
                'id' => 'tilang_etle',
                'name' => $t('Polantas E-TLE Jabar', 'West Java E-TLE Traffic Police'),
                'phone' => '+62 878-3341-0092',
                'initials' => 'ET',
                'avatar_bg' => '#1D4ED8',
                'category' => $t('File .APK Surat Tilang', 'Traffic Ticket .APK'),
                'difficulty' => $t('Menengah', 'Medium'),
                'preview' => $t(
                    'PEMBERITAHUAN TILANG ELEKTRONIK: Kendaraan Anda terekam melanggar...',
                    'ELECTRONIC TRAFFIC TICKET NOTICE: Your vehicle was recorded violating...'
                ),
                'time' => '11:20',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'SELAMAT SIANG. Kami dari Subdit Gakkum E-TLE Polda Jabar. Kendaraan Anda terdeteksi kamera CCTV melanggar marka jalan di Jl. Jenderal Sudirman Purwakarta.',
                                    'GOOD AFTERNOON. We are from the E-TLE Traffic Enforcement Unit. Your vehicle was caught on CCTV violating road markings on Jl. Jenderal Sudirman Purwakarta.'
                                ),
                                'time' => '11:19',
                            ],
                            [
                                'text' => $t(
                                    'Unduh bukti tangkapan kamera dan surat ketetapan denda dalam berkas berikut. Jika tidak dikonfirmasi dalam 1x24 jam STNK akan diblokir permanen.',
                                    'Download the camera capture evidence and fine decree in the file below. If unconfirmed within 24 hours, your vehicle registration (STNK) will be permanently blocked.'
                                ),
                                'time' => '11:20',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Surat_Tilang_ETLE_Polri.apk',
                                    'size' => '3.9 MB',
                                    'subtitle' => $t('APK · Dokumen Resmi', 'APK · Official Document'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Takut STNK diblokir, segera buka Surat_Tilang_ETLE_Polri.apk.',
                                    'A. Afraid your vehicle registration will be blocked, immediately open Surat_Tilang_ETLE_Polri.apk.'
                                ),
                                'next' => 'end_danger_etle',
                                'user_text' => $t(
                                    'Siap pak, mohon jangan diblokir STNK saya. Ini saya buka surat tilangnya.',
                                    'Understood sir, please do not block my registration. I am opening the ticket file now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Minta petugas menyebutkan pelat nomor kendaraan, jenis kendaraan, dan nomor referensi E-TLE di chat.',
                                    'B. Ask the officer to state your license plate number, vehicle type, and E-TLE reference code in chat.'
                                ),
                                'next' => 'turn2_bluff',
                                'user_text' => $t(
                                    'Tolong sebutkan pelat nomor kendaraan saya, jenis mobil/motornya, serta nomor referensi tilangnya dalam bentuk teks.',
                                    'Please state my license plate number, vehicle model, and the ticket reference number as text.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'C. Tawarkan transfer uang damai langsung ke rekening petugas supaya cepat beres.',
                                    'C. Offer to transfer a settlement payment directly to the officer personal account.'
                                ),
                                'next' => 'end_danger_bribe',
                                'user_text' => $t(
                                    'Waduh pak, bisa langsung saya transfer saja dendanya ke rekening bapak biar tidak usah buka aplikasi?',
                                    'Oh no sir, can I just transfer the fine directly to your bank account so I do not have to open the app?'
                                ),
                            ],
                        ],
                    ],
                    'turn2_bluff' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Data pelat nomor bersifat rahasia di dalam sistem enkripsi berkas .apk tersebut Pak/Bu. Kalau tidak mau buka aplikasinya, kirimkan foto KTP dan STNK asli Anda ke chat ini biar saya cocokkan manual di komputer kantor.',
                                    'License plate data is confidential inside the encrypted .apk file. If you do not want to open the app, send a photo of your ID card (KTP) and vehicle registration (STNK) here so I can check it manually.'
                                ),
                                'time' => '11:22',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Kirim foto KTP & STNK supaya dibantu dicek manual oleh petugas.',
                                    'A. Send photos of your ID card & vehicle registration so they can check manually.'
                                ),
                                'next' => 'end_partial_ktp',
                                'user_text' => $t(
                                    'Baik pak, ini saya foto KTP dan STNK saya, tolong dicek apakah benar kena tilang.',
                                    'Okay sir, here are photos of my ID card and vehicle registration, please check if I really got a ticket.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak kirim foto KTP/STNK! Kalau memang polisi yang menilang, mereka pasti sudah tahu pelat nomornya.',
                                    'B. Refuse to send ID photos! If they really issued a ticket, they would already know the license plate.'
                                ),
                                'next' => 'turn3_briva_scam',
                                'user_text' => $t(
                                    'Aneh sekali, katanya kendaraan saya terekam tilang tapi Bapak malah tidak tahu pelat nomor saya dan minta foto KTP/STNK.',
                                    'That makes no sense—you claim my vehicle was caught on camera yet you do not even know my license plate and are asking for my ID card.'
                                ),
                            ],
                        ],
                    ],
                    'turn3_briva_scam' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Anda jangan menghambat petugas ya! Kalau tidak mau kirim KTP atau buka file .apk, bayar sekarang denda tilang Rp250.000 ke rekening bendahara titipan: BNI 0918273645 a.n. AGUS PRATAMA atau surat panggilan paksa dikirim sore ini!',
                                    'Do not obstruct an officer! If you refuse to send your ID or open the .apk, pay the IDR 250,000 fine right now to our custodian account: BNI 0918273645 (AGUS PRATAMA) or a warrant goes out this afternoon!'
                                ),
                                'time' => '11:24',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Panik diancam surat panggilan paksa, langsung transfer Rp250.000 ke rekening Agus Pratama.',
                                    'A. Panicked by the warrant threat, transfer IDR 250,000 to Agus Pratama account.'
                                ),
                                'next' => 'end_danger_bribe',
                                'user_text' => $t(
                                    'Jangan kirim surat panggilan pak! Ini saya transfer Rp250.000 sekarang.',
                                    'Please do not send a summons! I am transferring the IDR 250,000 right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Skakmat penipu: denda tilang resmi hanya lewat kode BRIVA negara setelah konfirmasi di web resmi .polri.go.id!',
                                    'B. Checkmate the scammer: official traffic fines use state BRIVA codes after verifying on .polri.go.id, never personal accounts!'
                                ),
                                'next' => 'turn4_etle_close',
                                'user_text' => $t(
                                    'Pembayaran tilang resmi tidak pernah ke rekening pribadi atas nama Agus Pratama! Semua lewat situs resmi konfirmasi-etle.polri.go.id.',
                                    'Official traffic fines are never paid to a personal account named Agus Pratama! Everything goes through the official konfirmasi-etle.polri.go.id portal.'
                                ),
                            ],
                        ],
                    ],
                    'turn4_etle_close' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Oh sudah pintar sekarang ya! Awas saja nanti kalau STNK-nya terblokir jangan minta bantuan kami!',
                                    'Oh, acting smart now, are we! Just wait until your registration gets blocked, do not come crying to us!'
                                ),
                                'time' => '11:25',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Cek mandiri di portal resmi etle.polri.go.id lalu laporkan & blokir nomor penipu ini.',
                                    'A. Check independently on the official etle.polri.go.id portal, then report & block this scammer.'
                                ),
                                'next' => 'end_safe_etle',
                                'user_text' => $t(
                                    'Saya sudah cek di portal resmi Polri dan kendaraan saya bersih. Nomor dan rekening penipu Anda saya laporkan ke cekrekening.id!',
                                    'I already checked the official Police portal and my vehicle record is clean. I am reporting your number and bank account to the authorities!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_etle' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: File .APK Tilang Palsu Membajak SMS & Perangkat!', 'DANGER: Fake Traffic Ticket .APK Hijacked Your Device!'),
                        'summary' => $t(
                            'Surat tilang E-TLE resmi dikirim melalui pos ke alamat STNK atau portal resmi .polri.go.id, BUKAN file instalasi .apk dari nomor pribadi.',
                            'Official E-TLE tickets are sent via physical mail or verified via .polri.go.id portals, NEVER as an .apk installer from a personal number.'
                        ),
                        'steps' => [
                            $t('Putuskan internet (Mode Pesawat) dan uninstall aplikasi asing segera.', 'Disconnect from the internet (Airplane Mode) and uninstall the unknown app immediately.'),
                            $t('Cek status tilang hanya lewat domain resmi pemerintah berakhiran .polri.go.id.', 'Check traffic ticket status only on official government domains ending in .polri.go.id.'),
                        ],
                    ],
                    'end_danger_bribe' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERJEBAK PENIPUAN TRANSFER: Uang Melayang ke Rekening Pribadi!', 'TRANSFER SCAM: Money Lost to a Personal Bank Account!'),
                        'summary' => $t(
                            'Pembayaran tilang resmi hanya melalui kode Virtual Account resmi negara setelah verifikasi pelanggaran, tidak pernah ke rekening pribadi.',
                            'Official traffic fine payments only use state Virtual Account codes after violation verification, never a personal bank account.'
                        ),
                        'steps' => [
                            $t('Laporkan nomor rekening penipu ke cekrekening.id dan bank terkait.', 'Report the scammer bank account to cekrekening.id and the receiving bank.'),
                        ],
                    ],
                    'end_partial_ktp' => [
                        'type' => 'partial',
                        'score' => 30,
                        'title' => $t('RISIKO TINGGI: Kebocoran Foto KTP & STNK!', 'HIGH RISK: ID Card & Vehicle Registration Leaked!'),
                        'summary' => $t(
                            'Meski tidak memasang file .apk, foto KTP & STNK Anda dapat disalahgunakan penipu untuk mengajukan pinjol ilegal atau memalsukan identitas.',
                            'Even though you avoided installing the .apk, your ID and vehicle documents can be misused for identity theft or illegal loan applications.'
                        ),
                        'steps' => [
                            $t('Jangan pernah mengirim foto KTP/STNK ke kontak WhatsApp tidak resmi.', 'Never send ID or vehicle registration photos to unverified WhatsApp contacts.'),
                        ],
                    ],
                    'end_safe_etle' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Verifikasi Mandiri & Anti-Intimidasi Aparat Palsu!', 'PERFECT SAFETY: Independent Verification & Immune to Intimidation!'),
                        'summary' => $t(
                            'Cerdas! Anda menolak file .apk, menolak menyerahkan foto KTP/STNK, dan membongkar modus transfer rekening pribadi.',
                            'Smart move! You rejected the .apk file, refused to hand over your ID card, and exposed the personal bank account transfer scam.'
                        ),
                        'steps' => [
                            $t('Verifikasi pelanggaran E-TLE selalu lewat portal resmi berdomain .polri.go.id.', 'Always verify E-TLE violations via official portals ending in .polri.go.id.'),
                        ],
                    ],
                ],
            ],

            // 4. CS BANK PALSU TARIF RP150.000 (4 GILIRAN)
            [
                'id' => 'cs_bank_palsu',
                'name' => $t('CS Bank BRI Purwakarta', 'BRI Bank Support Purwakarta'),
                'phone' => '+62 823-5519-8840',
                'initials' => 'CS',
                'avatar_bg' => '#0F172A',
                'category' => $t('Phishing Biaya Admin Bank', 'Bank Admin Fee Phishing'),
                'difficulty' => $t('Tinggi', 'Hard'),
                'preview' => $t(
                    'Yth. Nasabah, mulai besok tarif transfer naik menjadi Rp150.000/bulan...',
                    'Dear Customer, starting tomorrow transfer fees will increase to IDR 150,000/month...'
                ),
                'time' => '13:05',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Yth. Nasabah Setia, mulai besok berlaku penyesuaian tarif transaksi baru Rp150.000/bulan (autodebet). Jika Anda ingin tetap menggunakan tarif lama Rp6.500/transaksi, silakan konfirmasi pembatalan sekarang melalui formulir resmi:',
                                    'Dear Valued Customer, starting tomorrow a new monthly transaction fee of IDR 150,000/month (auto-debit) will take effect. To keep the old IDR 6,500 per-transaction tariff, submit your cancellation now via our form:'
                                ),
                                'time' => '13:05',
                                'link' => 'https://formulir-tarif-bri-id.com/batal',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Langsung klik link pembatalan dan isi username, password, serta PIN BRImo.',
                                    'A. Click the cancellation link immediately and enter your BRImo username, password, and PIN.'
                                ),
                                'next' => 'end_danger_bank',
                                'user_text' => $t(
                                    'Waduh mahal banget Rp150.000/bulan! Ini saya langsung isi formulir pembatalannya ya.',
                                    'Whoa IDR 150,000/month is way too expensive! I am filling out the cancellation form right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Pertanyakan kenapa memakai nomor HP pribadi (bukan centang biru) dan domainnya bukan bri.co.id.',
                                    'B. Question why they are using a personal phone number (no verified badge) and a non-bri.co.id domain.'
                                ),
                                'next' => 'turn2_otp_trick',
                                'user_text' => $t(
                                    'Kok pesan bank dikirim dari nomor HP biasa tanpa centang biru? Domain formulir-tarif-bri-id.com juga bukan situs resmi bri.co.id.',
                                    'Why is a bank notice coming from a normal phone number without a verified badge? And formulir-tarif-bri-id.com is not the official bri.co.id domain.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'C. Bilang tidak keberatan dipotong Rp150.000 untuk melihat reaksi penipu.',
                                    'C. Say you do not mind paying IDR 150,000/month just to test how the scammer reacts.'
                                ),
                                'next' => 'turn2_otp_trick',
                                'user_text' => $t(
                                    'Tidak apa-apa min, saya setuju saja ikut tarif Rp150.000/bulan karena saya sering transfer.',
                                    'That is fine, I actually prefer the IDR 150,000/month unlimited plan since I transfer often.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_otp_trick' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Mohon perhatiannya Pak/Bu, baik setuju maupun menolak tarif baru, rekening Anda saat ini berstatus PENDING dan akan terblokir otomatis pukul 14.00 WIB jika tidak disinkronkan!',
                                    'Attention Sir/Madam: whether you accept or decline the new tariff, your account is currently PENDING and will be automatically frozen at 2:00 PM if not synchronized!'
                                ),
                                'time' => '13:06',
                            ],
                            [
                                'text' => $t(
                                    'Kalau Bapak/Ibu ragu membuka link web, cukup sebutkan saja 6 digit kode OTP SMS yang baru masuk ke HP Anda biar petugas kami bantu sinkronkan manual.',
                                    'If you hesitate to open the web link, simply tell me the 6-digit SMS OTP code that just arrived on your phone so our officer can synchronize it manually.'
                                ),
                                'time' => '13:07',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Berikan 6 digit kode SMS karena merasa lebih aman daripada klik link.',
                                    'A. Give the 6-digit SMS code thinking it is safer than clicking the link.'
                                ),
                                'next' => 'end_danger_otp',
                                'user_text' => $t(
                                    'Oh kalau dibantu manual tanpa klik link boleh deh, ini kodenya 482910.',
                                    'Oh if you can do it manually without clicking the link, sure—the code is 482910.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak keras! Jika ada SMS OTP masuk tanpa kita minta, berarti penipu sedang mencoba login/reset akun kita!',
                                    'B. Refuse firmly! Receiving an unrequested OTP SMS means the scammer is actively trying to log into or reset your account!'
                                ),
                                'next' => 'turn3_card_trick',
                                'user_text' => $t(
                                    'Bank resmi TIDAK PERNAH meminta kode OTP! Kalau ada OTP masuk, berarti Anda sedang berusaha membobol akun saya!',
                                    'Official banks NEVER ask for an OTP code! If an OTP just arrived, it means you are trying to break into my account!'
                                ),
                            ],
                        ],
                    ],
                    'turn3_card_trick' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Bukan membobol Pak/Bu, itu kode verifikasi sistem! Kalau tidak mau sebutkan OTP, silakan kirim foto bagian depan & belakang kartu ATM (beserta 3 angka CVV) untuk verifikasi kepemilikan rekening.',
                                    'We are not hacking you, that is a system verification code! If you will not share the OTP, send a photo of the front and back of your ATM card (showing the 3-digit CVV) to verify ownership.'
                                ),
                                'time' => '13:08',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Kirim foto kartu ATM depan-belakang karena tidak menyertakan PIN.',
                                    'A. Send photos of the front and back of your ATM card since you are not giving the PIN.'
                                ),
                                'next' => 'end_danger_bank',
                                'user_text' => $t(
                                    'Asal bukan PIN/OTP nggak apa-apa kan ya, ini foto kartu ATM saya.',
                                    'As long as it is not my PIN or OTP it should be fine, right? Here is my ATM card photo.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak! Nomor kartu 16 digit + masa berlaku + kode CVV di belakang kartu bisa dipakai menguras saldo tanpa kartu fisik!',
                                    'B. Refuse! The 16-digit card number + expiry + 3-digit CVV on the back can be used for unauthorized online charges!'
                                ),
                                'next' => 'turn4_bank_final',
                                'user_text' => $t(
                                    'Nomor kartu ATM dan 3 digit CVV itu rahasia perbankan! Anda mau menguras kartu debit saya lewat transaksi online kan?',
                                    'My ATM card number and 3-digit CVV are strictly confidential! You are trying to drain my debit card via online transactions!'
                                ),
                            ],
                        ],
                    ],
                    'turn4_bank_final' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Baik, kalau Bapak/Ibu menolak seluruh prosedur, rekening Anda kami tandai untuk penutupan hari ini!',
                                    'Fine, since you refuse all procedures, we are flagging your account for closure today!'
                                ),
                                'time' => '13:09',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Tutup percakapan, cek aplikasi m-Banking resmi untuk memastikan akun aman, dan laporkan nomor ke pihak bank.',
                                    'A. End the chat, check your official mobile banking app to confirm your account is safe, and report the number to the bank.'
                                ),
                                'next' => 'end_safe_bank',
                                'user_text' => $t(
                                    'Silakan gertak orang lain. Saya sudah cek BRImo resmi saya aman dan saya laporkan nomor Anda ke Call Center resmi 1500017!',
                                    'Go bluff someone else. I already checked my official mobile banking app and reported your number to the official hotline!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_bank' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('REKENING TERBOBOL: Phishing Kredensial & Kartu Debit!', 'BANK ACCOUNT COMPROMISED: Credential & Debit Card Phishing!'),
                        'summary' => $t(
                            'Situs formulir-tarif-bri-id.com adalah tiruan, dan foto kartu ATM beserta CVV memungkinkan penipu menguras saldo rekening Anda.',
                            'The domain formulir-tarif-bri-id.com is a fake clone, and sharing your card number + CVV lets scammers drain your funds.'
                        ),
                        'steps' => [
                            $t('Segera telepon Call Center resmi bank (1500017) untuk pemblokiran rekening & kartu ATM.', 'Immediately call the bank official hotline (1500017) to freeze your account & debit card.'),
                            $t('Ubah username, password, dan PIN m-Banking segera.', 'Change your mobile banking username, password, and PIN right away.'),
                        ],
                    ],
                    'end_danger_otp' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: Memberikan OTP Sama dengan Menyerahkan Kunci Brankas!', 'DANGER: Giving an OTP Is Handing Over Your Vault Key!'),
                        'summary' => $t(
                            'Penipu berpura-pura menawarkan "bantuan sinkronisasi manual tanpa klik link" untuk menjebak Anda menyebutkan kode OTP SMS.',
                            'The scammer pretended to offer "manual synchronization without clicking a link" just to trick you into reading out your SMS OTP code.'
                        ),
                        'steps' => [
                            $t('Ingat aturan emas: OTP tidak boleh diberikan kepada siapa pun, termasuk yang mengaku petugas bank.', 'Remember the golden rule: never share an OTP with anyone, even someone claiming to be bank staff.'),
                        ],
                    ],
                    'end_safe_bank' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Master Deteksi Phishing Perbankan!', 'PERFECT SAFETY: Master of Banking Phishing Defense!'),
                        'summary' => $t(
                            'Tepat sekali! Anda mematahkan 4 lapis serangan: link tarif Rp150.000 palsu, gertakan blokir pukul 14.00, pancingan kode OTP, hingga permintaan foto CVV kartu ATM.',
                            'Spot on! You defeated 4 layers of attack: the fake IDR 150,000 fee link, the 2:00 PM freeze bluff, the manual OTP trick, and the CVV card photo request.'
                        ),
                        'steps' => [
                            $t('Bagikan edukasi modus kenaikan tarif Rp150.000/bulan ini kepada orang tua dan kerabat.', 'Share this IDR 150,000/month fee scam warning with your parents and relatives.'),
                        ],
                    ],
                ],
            ],

            // 5. TEMAN LAMA DIBAJAK PINJAM UANG (4 GILIRAN)
            [
                'id' => 'teman_dibajak',
                'name' => $t('Budi Santoso (Teman Lama)', 'Budi Santoso (Old Friend)'),
                'phone' => '+62 813-2201-4471',
                'initials' => 'BS',
                'avatar_bg' => '#059669',
                'category' => $t('Pembajakan Akun WA', 'Hijacked WhatsApp Account'),
                'difficulty' => $t('Tinggi', 'Hard'),
                'preview' => $t(
                    'Bro, lagi sibuk ga? Boleh pinjam dulu 1,5 juta urgent banget buat rumah sakit...',
                    'Hey bro, are you busy? Can I borrow 1.5 million urgently for a hospital bill...'
                ),
                'time' => '14:40',
                'unread' => 2,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Bro, maaf banget ganggu. Aku lagi panik di IGD RS Bayu Asih, butuh pinjam Rp1.500.000 buat bayar tebus obat sekarang banget. Besok pagi jam 8 langsung aku ganti 100% 🙏',
                                    'Bro, sorry to bother you. I am panicking at the hospital ER right now and urgently need to borrow IDR 1,500,000 for medicine. I will pay you back 100% tomorrow at 8 AM 🙏'
                                ),
                                'time' => '14:39',
                            ],
                            [
                                'text' => $t(
                                    'M-bankingku lagi terblokir salah PIN, tolong transfer ke rekening saudara sepupuku ya: BCA 8820194421 a.n. HENDRA WIJAYA.',
                                    'My mobile banking is locked after a wrong PIN, please transfer it to my cousin account: BCA 8820194421 under the name HENDRA WIJAYA.'
                                ),
                                'time' => '14:40',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Langsung transfer Rp1.500.000 karena kasihan melihat nama teman lama sedang di IGD.',
                                    'A. Immediately transfer IDR 1,500,000 out of sympathy for your old friend in the ER.'
                                ),
                                'next' => 'end_danger_transfer',
                                'user_text' => $t(
                                    'Waduh semoga cepat sembuh bro! Ini langsung aku transfer ke rekening Hendra Wijaya ya.',
                                    'Oh no, get well soon bro! I am transferring it to Hendra Wijaya account right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Ajak telepon suara / video call dulu karena nama pemilik rekeningnya berbeda (Hendra Wijaya).',
                                    'B. Insist on a voice or video call first because the bank account holder name is different (Hendra Wijaya).'
                                ),
                                'next' => 'turn2_excuse',
                                'user_text' => $t(
                                    'Sebentar Bud, angkat video call aku dulu ya biar jelas. Kok rekeningnya atas nama Hendra Wijaya bukan nama kamu?',
                                    'Hold on Budi, pick up my video call first. Why is the bank account under Hendra Wijaya instead of your name?'
                                ),
                            ],
                        ],
                    ],
                    'turn2_excuse' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Duh jangan telepon/VC dulu bro, aku lagi di dalam ruang tindakan IGD dilarang berisik sama dokter! Tolong banget bro ini kasir farmasinya tinggal 5 menit lagi tutup!',
                                    'Ugh please do not call or video call right now bro, I am inside the ER treatment room and the doctor forbade noise! Please bro, the pharmacy counter closes in 5 minutes!'
                                ),
                                'time' => '14:41',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Percaya alasan ruang IGD lalu transfer Rp500.000 dulu.',
                                    'A. Believe the ER excuse and transfer IDR 500,000 first.'
                                ),
                                'next' => 'end_danger_transfer',
                                'user_text' => $t(
                                    'Ya udah aku bantu transfer Rp500.000 dulu ya ke rekening itu.',
                                    'Okay I will help by transferring IDR 500,000 to that account first.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Ajukan pertanyaan jebakan tentang kenangan/fakta pribadi yang hanya Budi asli yang tahu.',
                                    'B. Ask a trap question about a personal memory/fact that only the real Budi would know.'
                                ),
                                'next' => 'turn3_memory_test',
                                'user_text' => $t(
                                    'Kalau nggak bisa angkat telepon, jawab dulu cepat: siapa nama wali kelas kita waktu kelas 11 dan kantor kamu sekarang di daerah mana?',
                                    'If you cannot pick up a call, answer this quickly: what was our 11th-grade homeroom teacher name, and which area is your current office in?'
                                ),
                            ],
                        ],
                    ],
                    'turn3_memory_test' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Ya ampun bro, orang lagi darurat nyawa di rumah sakit malah dites kuis sekolah! Kalau nggak ikhlas bantu teman sendiri bilang aja bro, aku lagi pusing banget nih nggak bisa mikir!',
                                    'Come on bro, someone is in a life-or-death hospital emergency and you are quizzing me about school! If you do not want to help a friend just say so, I am too dizzy to think!'
                                ),
                                'time' => '14:42',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Merasa bersalah karena sudah menuduh teman saat darurat, lalu akhirnya transfer.',
                                    'A. Feel guilty for doubting a friend during an emergency, and transfer the money.'
                                ),
                                'next' => 'end_danger_transfer',
                                'user_text' => $t(
                                    'Maaf banget Bud aku cuma memastikan, oke ini aku transfer sekarang ya.',
                                    'I am really sorry Budi, I was just making sure. Okay, I am transferring it now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tetap tenang! Penipu sengaja memakai guilt-tripping (membuat korban merasa bersalah) karena gagal menjawab pertanyaan pribadi.',
                                    'B. Stay calm! The scammer is using guilt-tripping because they cannot answer your personal verification question.'
                                ),
                                'next' => 'turn4_hijack_confirmed',
                                'user_text' => $t(
                                    'Kalau memang Budi asli, menulis satu nama saja cuma butuh 2 detik. Aku mau telepon istrinya Budi / teman sekantor Budi dulu sekarang.',
                                    'If you were really Budi, typing one name takes 2 seconds. I am calling Budi family / coworker right now to check.'
                                ),
                            ],
                        ],
                    ],
                    'turn4_hijack_confirmed' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Eh jangan hubungi siapa-siapa dulu! Keluarga di rumah belum tahu aku masuk IGD nanti mereka syok! Udah transfer Rp300.000 aja deh sekarang!',
                                    'Hey do not call anyone! My family at home does not know I am in the ER yet, they will panic! Just transfer IDR 300,000 right now!'
                                ),
                                'time' => '14:43',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Tolak transfer, segera hubungi Budi lewat jalur lain (Instagram/SMS/keluarga) untuk mengabarkan WA-nya dibajak!',
                                    'A. Refuse to transfer, and immediately contact Budi via another channel (Instagram/SMS/family) to tell him his WhatsApp is hijacked!'
                                ),
                                'next' => 'end_safe_verify',
                                'user_text' => $t(
                                    'Fix akun WA Budi sedang dibajak penipu! Saya tidak akan transfer sepeser pun dan saya kabari Budi sekarang.',
                                    'Confirmed: Budi WhatsApp account is hijacked! I will not transfer a single rupiah and I am alerting the real Budi now.'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_transfer' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERTIPU PEMBAJAKAN AKUN: Saldo Melayang ke Penipu!', 'HIJACKED ACCOUNT SCAM: Money Sent to a Scammer!'),
                        'summary' => $t(
                            'Akun WhatsApp Budi sedang diambil alih penipu! Pola klasiknya: darurat rumah sakit, menolak telepon/VC, gagal menjawab kenangan pribadi, dan rekening atas nama orang lain.',
                            'Budi WhatsApp account was hijacked! Classic signs: hospital emergency, refusing voice/video calls, failing personal memory questions, and using someone else bank account.'
                        ),
                        'steps' => [
                            $t('Laporkan rekening penipu ke cekrekening.id dan pihak bank terkait.', 'Report the scammer bank account to cekrekening.id and the bank.'),
                            $t('Segera kabari teman Anda lewat saluran lain bahwa akun WhatsApp-nya dibajak.', 'Immediately inform your friend through another channel that their WhatsApp was hijacked.'),
                        ],
                    ],
                    'end_safe_verify' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Verifikasi Out-of-Band & Pertanyaan Pribadi Berhasil!', 'PERFECT SAFETY: Out-of-Band Verification & Memory Test Succeeded!'),
                        'summary' => $t(
                            'Sangat bijak! Anda mengenali taktik manipulatif penipu yang menolak video call, gagal menjawab pertanyaan pribadi, dan melarang Anda menghubungi keluarga.',
                            'Very wise! You saw right through the scammer refusal to video call, inability to answer personal questions, and plea not to contact family.'
                        ),
                        'steps' => [
                            $t('Aktifkan Verifikasi Dua Langkah (PIN 6 digit) di WhatsApp agar akun Anda sendiri terlindungi dari pembajakan.', 'Enable Two-Step Verification (6-digit PIN) in WhatsApp so your own account stays protected from hijacking.'),
                        ],
                    ],
                ],
            ],

            // 6. LOWONGAN KERJA PARUH WAKTU TASK SCAM (4 GILIRAN)
            [
                'id' => 'loker_paruh_waktu',
                'name' => $t('Clarissa (HRD E-Commerce)', 'Clarissa (E-Commerce HR)'),
                'phone' => '+62 895-6102-7731',
                'initials' => 'CL',
                'avatar_bg' => '#7C3AED',
                'category' => $t('Penipuan Kerja Paruh Waktu', 'Part-Time Job Task Scam'),
                'difficulty' => $t('Menengah', 'Medium'),
                'preview' => $t(
                    'Halo! Pekerjaan paruh waktu cukup like video & produk dibayar Rp50.000-Rp500.000/hari...',
                    'Hello! Part-time remote job: just like videos & products for IDR 50,000-500,000/day...'
                ),
                'time' => '15:12',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Halo kak! Kami dari tim rekrutmen mitra Shopee/Tokopedia Mall mencari pekerja paruh waktu dari rumah. Tugasnya mudah: cukup like & follow toko online, komisi Rp150.000 - Rp800.000 per hari langsung cair!',
                                    'Hi there! We are from the Shopee/Tokopedia Mall partner recruitment team looking for work-from-home part-timers. Simple task: just like & follow online stores for IDR 150,000 - 800,000 daily payout!'
                                ),
                                'time' => '15:11',
                            ],
                            [
                                'text' => $t(
                                    'Untuk tugas percobaan pertama langsung dapat bonus Rp30.000 tanpa biaya apa pun. Mau coba tugas pertamanya kak?',
                                    'For your first trial task, you get an instant IDR 30,000 bonus with zero fees. Would you like to try the first task?'
                                ),
                                'time' => '15:12',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Ikut mencoba tugas awal karena gratis dan dijanjikan komisi Rp30.000.',
                                    'A. Try the initial task since it is free and promises an IDR 30,000 bonus.'
                                ),
                                'next' => 'turn2_deposit',
                                'user_text' => $t(
                                    'Boleh deh kak, coba dulu tugas pertamanya lumayan Rp30.000.',
                                    'Sure, let me try the first task for IDR 30,000.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tanya surat tugas resmi perusahaan dan kenapa rekrutmen HRD memakai nomor WA acak.',
                                    'B. Ask for an official company appointment letter and why HR recruitment is messaging from a random WhatsApp number.'
                                ),
                                'next' => 'turn2_hrd_bluff',
                                'user_text' => $t(
                                    'HRD perusahaan e-commerce resmi tidak pernah merekrut lewat WA pribadi acak. Dari mana Anda dapat nomor saya?',
                                    'Official e-commerce HR teams never recruit via random personal WhatsApp messages. Where did you get my number?'
                                ),
                            ],
                        ],
                    ],
                    'turn2_hrd_bluff' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Nomor kakak terpilih dari database pelanggan aktif e-commerce kami kak. Ini program resmi peningkatan rating merchant. Ratusan anggota di grup Telegram kami sudah cair jutaan rupiah tiap hari lho!',
                                    'Your number was selected from our active e-commerce customer database. This is an official merchant rating program. Hundreds of members in our Telegram group cash out millions every day!'
                                ),
                                'time' => '15:14',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Penasaran melihat testimoni grup Telegram, akhirnya ikut masuk grup dan mulai tugas.',
                                    'A. Curious about the Telegram group testimonials, join the group and start the tasks.'
                                ),
                                'next' => 'turn2_deposit',
                                'user_text' => $t(
                                    'Oh gitu, boleh deh coba masuk grupnya dulu.',
                                    'Oh I see, okay let me join the group and check it out.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Bongkar modusnya: testimoni di grup Telegram itu adalah akun komplotan (bot/aktor) untuk memancing korban deposit!',
                                    'B. Expose the scheme: the testimonials in that Telegram group are paid shills/bots designed to lure victims into depositing money!'
                                ),
                                'next' => 'turn3_vip_trap',
                                'user_text' => $t(
                                    'Saya sudah paham modus Task Scam! Testimoni di grup Telegram itu semua komplotan Anda untuk memancing korban setor deposit di tugas ke-4 kan?',
                                    'I know how Task Scams work! Everyone posting testimonials in that Telegram group is part of your syndicate to bait victims into depositing money on Task #4!'
                                ),
                            ],
                        ],
                    ],
                    'turn2_deposit' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Selamat kak! Komisi awal Rp30.000 sudah masuk kan? Nah sekarang masuk Tugas Bundling VIP #4: kakak wajib top-up deposit Rp500.000 dulu di web kerja kami, dalam 5 menit langsung cair Rp680.000!',
                                    'Congrats! Your initial IDR 30,000 bonus arrived, right? Now for VIP Bundling Task #4: you must deposit IDR 500,000 on our work portal, and within 5 minutes you will cash out IDR 680,000!'
                                ),
                                'time' => '15:18',
                                'link' => 'https://merchant-task-vip88.top/deposit',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Percaya karena tadi sempat dibayar Rp30.000, lalu transfer deposit Rp500.000.',
                                    'A. Trust them because they paid IDR 30,000 earlier, and transfer the IDR 500,000 deposit.'
                                ),
                                'next' => 'end_danger_task',
                                'user_text' => $t(
                                    'Oke kak tadi kan beneran cair, ini saya transfer Rp500.000 ya buat tugas VIP.',
                                    'Okay since the first bonus really paid out, I am transferring IDR 500,000 now for the VIP task.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Langsung berhenti saat diminta deposit uang dan keluar dari grup Telegram!',
                                    'B. Stop immediately the moment they ask for a deposit and leave the Telegram group!'
                                ),
                                'next' => 'end_partial_escape',
                                'user_text' => $t(
                                    'Begitu disuruh deposit uang itu pasti penipuan! Saya berhenti sekarang dan keluar grup.',
                                    'The moment a job asks for a deposit, it is 100% a scam! I am stopping right now and leaving the group.'
                                ),
                            ],
                        ],
                    ],
                    'turn3_vip_trap' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Bukan penipuan kak, kami ada surat izin usaha! Kalau kakak nggak mau tugas deposit, kakak cukup daftarkan rekening bank & foto KTP di aplikasi mitra kami ini aja buat jadi admin pengawas:',
                                    'It is not a scam, we have a business license! If you do not want deposit tasks, just register your bank account & ID card in our partner app below to become a supervisor:'
                                ),
                                'time' => '15:19',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Portal_Kerja_Merchant_VIP.apk',
                                    'size' => '5.1 MB',
                                    'subtitle' => $t('APK · Portal Mitra', 'APK · Partner Portal'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Install Portal_Kerja_Merchant_VIP.apk karena tertarik jadi admin pengawas tanpa deposit.',
                                    'A. Install Portal_Kerja_Merchant_VIP.apk to become a supervisor without paying a deposit.'
                                ),
                                'next' => 'end_danger_task',
                                'user_text' => $t(
                                    'Oh kalau jadi admin tanpa deposit boleh deh, saya install aplikasinya.',
                                    'Oh if it is a supervisor role with no deposit, sure, I will install the app.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak file .apk maupun tawaran kerja palsu, lalu blokir & laporkan nomornya!',
                                    'B. Reject both the .apk file and the fake job offer, then block & report the number!'
                                ),
                                'next' => 'end_safe_task',
                                'user_text' => $t(
                                    'Gagal menipu lewat deposit sekarang malah kirim malware .apk! Saya blokir dan laporkan nomor Anda.',
                                    'After failing the deposit scam, now you send .apk malware! I am blocking and reporting your number.'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_task' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERJEBAK TASK SCAM: Deposit Berlipat Tidak Pernah Kembali!', 'TRAPPED IN TASK SCAM: Escalating Deposits Never Return!'),
                        'summary' => $t(
                            'Komisi awal Rp30.000 hanyalah umpan psikologis. Setelah deposit Rp500.000, penipu akan memaksa deposit Rp2 juta, Rp10 juta, dst. dengan alasan "tugas berseri belum selesai".',
                            'The initial IDR 30,000 bonus is purely psychological bait. After you deposit IDR 500,000, scammers demand IDR 2M, IDR 10M, etc., claiming "combo tasks are incomplete".'
                        ),
                        'steps' => [
                            $t('Jangan pernah menyetor tambahan uang lagi demi "mencairkan" dana sebelumnya (sunk cost trap).', 'Never deposit additional money trying to "unlock" previous funds (sunk cost trap).'),
                            $t('Simpan bukti transfer dan laporkan ke kepolisian & cekrekening.id.', 'Save transfer receipts and report the account to the police & cekrekening.id.'),
                        ],
                    ],
                    'end_partial_escape' => [
                        'type' => 'partial',
                        'score' => 65,
                        'title' => $t('SELAMAT DI DETIK TERAKHIR: Stop Sebelum Deposit!', 'ESCAPED JUST IN TIME: Stopped Before Depositing!'),
                        'summary' => $t(
                            'Untung Anda berhenti tepat saat diminta deposit! Namun, memberikan nomor rekening & bergabung ke grup mereka tetap memasukkan data Anda ke daftar target sindikat penipu.',
                            'Thankfully you stopped right when asked to deposit! However, sharing your bank account with their group puts your contact info on scam syndicate target lists.'
                        ),
                        'steps' => [
                            $t('Waspadai pesan penipuan lanjutan dari nomor asing lainnya.', 'Stay alert for follow-up scam messages from other unknown numbers.'),
                        ],
                    ],
                    'end_safe_task' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Kebal Iming-Iming Komisi Instan & Task Scam!', 'PERFECT SAFETY: Immune to Instant Commission & Task Scams!'),
                        'summary' => $t(
                            'Tepat! Anda mengenali pola Task Scam sejak awal, membongkar grup komplotan Telegram, dan menolak aplikasi kerja .apk palsu.',
                            'Spot on! You recognized the Task Scam pattern from the start, exposed the fake Telegram testimonial group, and rejected the malicious .apk app.'
                        ),
                        'steps' => [
                            $t('Cek lowongan kerja hanya lewat portal karier resmi perusahaan.', 'Only apply for jobs through official company career portals.'),
                        ],
                    ],
                ],
            ],

            // 7. PAJAK DJP .APK & E-METERAI (4 GILIRAN)
            [
                'id' => 'djp_pajak_apk',
                'name' => $t('Layanan NPWP DJP', 'DJP Tax ID Service'),
                'phone' => '+62 852-1190-6328',
                'initials' => 'DJ',
                'avatar_bg' => '#B45309',
                'category' => $t('File .APK Pajak Palsu', 'Fake Tax .APK'),
                'difficulty' => $t('Tinggi', 'Hard'),
                'preview' => $t(
                    'Bapak/Ibu wajib melakukan pemutakhiran data NIK-NPWP hari ini lewat aplikasi...',
                    'Taxpayer Notice: You must complete NIK-NPWP tax synchronization today via the app...'
                ),
                'time' => '16:05',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Yth. Wajib Pajak, berdasarkan evaluasi sistem Coretax DJP, status pemadanan NIK-NPWP Anda belum tervalidasi. Mohon segera pasang aplikasi pembaruan berikut sebelum pukul 17.00 WIB atau rekening bank Anda dikenakan pembekuan pajak.',
                                    'Dear Taxpayer, according to the DJP Coretax system, your NIK-NPWP synchronization is unvalidated. Please install the update app below before 5:00 PM or your bank account will face a tax freeze.'
                                ),
                                'time' => '16:05',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'M-Pajak_Pemutakhiran_NIK.apk',
                                    'size' => '6.1 MB',
                                    'subtitle' => $t('APK · Pembaruan Data', 'APK · Data Update'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Unduh dan pasang M-Pajak_Pemutakhiran_NIK.apk supaya terhindar dari pembekuan pajak.',
                                    'A. Download and install M-Pajak_Pemutakhiran_NIK.apk to avoid a tax freeze.'
                                ),
                                'next' => 'end_danger_apk',
                                'user_text' => $t(
                                    'Baik pak, saya install aplikasinya sekarang biar tidak dibekukan.',
                                    'Understood sir, I am installing the application now so my account is not frozen.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak file .apk karena aplikasi resmi DJP hanya dari Play Store/App Store atau portal pajak.go.id.',
                                    'B. Reject the .apk file because official tax services only use Play Store/App Store or pajak.go.id.'
                                ),
                                'next' => 'turn2_fee',
                                'user_text' => $t(
                                    'DJP resmi tidak pernah mengirim file .apk lewat WhatsApp pribadi. Pemadanan NIK-NPWP hanya lewat situs resmi pajak.go.id.',
                                    'The official Tax Office never sends .apk files via personal WhatsApp. NIK-NPWP matching is only done on pajak.go.id.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'C. Tanya apakah bisa dibantu urus langsung oleh petugas lewat chat.',
                                    'C. Ask if the officer can just process it for you directly via chat.'
                                ),
                                'next' => 'turn2_fee',
                                'user_text' => $t(
                                    'HP saya tidak bisa buka file itu pak, bisa dibantu urus dari sana saja?',
                                    'My phone cannot open that file sir, can you help process it from your side?'
                                ),
                            ],
                        ],
                    ],
                    'turn2_fee' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Jika tidak menginstal aplikasi tersebut, Bapak/Ibu wajib melakukan pembayaran e-Meterai validasi Coretax sebesar Rp10.000 melalui tautan resmi kami berikut agar status NPWP langsung aktif:',
                                    'If you do not install the app, you must pay a mandatory IDR 10,000 Coretax validation e-Stamp duty via our link below so your Tax ID activates immediately:'
                                ),
                                'time' => '16:06',
                                'link' => 'https://bayar-meterai-djp-online.site/qris',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Klik link dan bayar Rp10.000 karena nominalnya kecil.',
                                    'A. Click the link and pay IDR 10,000 since the amount is very small.'
                                ),
                                'next' => 'end_danger_fee',
                                'user_text' => $t(
                                    'Oh cuma Rp10.000 ya, oke ini saya klik link pembayaran e-Meterai-nya.',
                                    'Oh it is only IDR 10,000, okay let me click the e-Stamp payment link.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak! Pemadanan NIK-NPWP itu 100% GRATIS tanpa biaya meterai dan domain .site jelas palsu (bukan .go.id).',
                                    'B. Refuse! NIK-NPWP matching is 100% FREE with no stamp duty, and a .site domain is fake (not .go.id).'
                                ),
                                'next' => 'turn3_efin_trick',
                                'user_text' => $t(
                                    'Pemadanan NIK-NPWP di DJP itu 100% gratis tanpa e-Meterai! Domain bayar-meterai-djp-online.site juga palsu, bukan pajak.go.id!',
                                    'NIK-NPWP synchronization is 100% free with no e-Stamp required! And bayar-meterai-djp-online.site is fake, not pajak.go.id!'
                                ),
                            ],
                        ],
                    ],
                    'turn3_efin_trick' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Baik kalau Bapak/Ibu ingin pemadanan gratis tanpa biaya meterai, cukup ketikkan NIK, nomor NPWP, kode EFIN, dan kata sandi DJP Online Anda di chat ini agar saya inputkan ke server pusat.',
                                    'Fine, if you want the free synchronization without stamp duty, just type your National ID (NIK), Tax ID (NPWP), EFIN code, and DJP Online password here so I can enter them into the main server.'
                                ),
                                'time' => '16:07',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Kirimkan NIK, kode EFIN, dan password DJP Online karena gratis.',
                                    'A. Send your NIK, EFIN code, and DJP Online password since it is free.'
                                ),
                                'next' => 'end_danger_fee',
                                'user_text' => $t(
                                    'Baik pak, ini NIK dan password DJP Online saya tolong diinputkan ya.',
                                    'Okay sir, here is my NIK and DJP Online password, please enter them for me.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak tegas! Kode EFIN dan password DJP Online adalah data rahasia wajib pajak. Blokir & laporkan!',
                                    'B. Refuse firmly! Your EFIN code and DJP Online password are confidential taxpayer credentials. Block & report!'
                                ),
                                'next' => 'end_safe_djp',
                                'user_text' => $t(
                                    'Petugas pajak asli tidak pernah meminta EFIN dan password wajib pajak lewat WA. Saya konfirmasi ke Kring Pajak 1500200 dan blokir nomor Anda!',
                                    'Real tax officers never ask for a taxpayer EFIN or password via WhatsApp. I am verifying via Kring Pajak 1500200 and blocking your number!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_apk' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: Malware Menyamar Aplikasi M-Pajak!', 'DANGER: Malware Disguised as M-Pajak Tax App!'),
                        'summary' => $t(
                            'DJP tidak pernah menyebarkan file instalasi .apk lewat WhatsApp. Aplikasi palsu tersebut mencuri kredensial bank dan pesan OTP Anda.',
                            'The Tax Office never distributes .apk installers via WhatsApp. That fake app steals your banking credentials and OTP messages.'
                        ),
                        'steps' => [
                            $t('Aktifkan Mode Pesawat, uninstall APK palsu, dan unduh aplikasi resmi hanya dari Google Play Store / App Store.', 'Enable Airplane Mode, uninstall the fake APK, and only download official apps from Google Play / App Store.'),
                        ],
                    ],
                    'end_danger_fee' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERJEBAK: Phishing Pembayaran Rp10.000 & Pencurian Data Pajak!', 'TRAPPED: IDR 10,000 Payment Phishing & Tax Data Theft!'),
                        'summary' => $t(
                            'Nominal kecil Rp10.000 sengaja dipakai sebagai umpan agar korban tidak curiga saat mengisi halaman phishing atau menyerahkan kredensial.',
                            'The small IDR 10,000 fee is deliberate bait so victims let their guard down while filling out a banking phishing form.'
                        ),
                        'steps' => [
                            $t('Seluruh layanan pemadanan NIK-NPWP resmi DJP di pajak.go.id tidak dipungut biaya sepeser pun.', 'All official NIK-NPWP matching services on pajak.go.id are completely free of charge.'),
                        ],
                    ],
                    'end_safe_djp' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Paham Domain .go.id & Kerahasiaan EFIN!', 'PERFECT SAFETY: Mastered .go.id Domains & EFIN Confidentiality!'),
                        'summary' => $t(
                            'Tepat! Anda menolak file .apk, menolak jebakan e-Meterai Rp10.000 di domain .site, dan menjaga kerahasiaan EFIN serta password pajak Anda.',
                            'Spot on! You rejected the .apk file, refused the IDR 10,000 e-Stamp trap on a .site domain, and protected your EFIN and tax password.'
                        ),
                        'steps' => [
                            $t('Hubungi Kring Pajak resmi 1500200 atau situs pajak.go.id untuk informasi perpajakan resmi.', 'Contact the official Kring Pajak 1500200 or pajak.go.id for legitimate tax information.'),
                        ],
                    ],
                ],
            ],

            // 8. GEBYAR HADIAH DANA KAGET (4 GILIRAN)
            [
                'id' => 'dana_kaget_palsu',
                'name' => $t('Gebyar Hadiah DANA', 'DANA Prize Giveaway'),
                'phone' => '+62 838-9941-2055',
                'initials' => 'DN',
                'avatar_bg' => '#0EA5E9',
                'category' => $t('Phishing Dompet Digital', 'E-Wallet Phishing'),
                'difficulty' => $t('Mudah-Menengah', 'Easy-Medium'),
                'preview' => $t(
                    'SELAMAT! Nomor Anda terpilih menerima Saldo DANA Kaget Rp2.000.000...',
                    'CONGRATULATIONS! Your number won an instant IDR 2,000,000 DANA balance...'
                ),
                'time' => '17:15',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    '🎉 SELAMAT! Nomor WhatsApp Anda terpilih sebagai pemenang Gebyar Saldo DANA Rp2.000.000 tanpa diundi! Klaim sekarang sebelum kuota hangus dalam 15 menit di:',
                                    '🎉 CONGRATULATIONS! Your WhatsApp number has been selected as the winner of an IDR 2,000,000 DANA Balance Giveaway! Claim within 15 minutes at:'
                                ),
                                'time' => '17:15',
                                'link' => 'https://klaim-dana-kaget2026.my.id',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Klik link dan masukkan nomor HP, PIN DANA, serta kode OTP untuk mencairkan saldo.',
                                    'A. Click the link and enter your phone number, DANA PIN, and OTP code to claim the balance.'
                                ),
                                'next' => 'end_danger_ewallet',
                                'user_text' => $t(
                                    'Alhamdulillah menang! Saya masukkan nomor HP dan PIN DANA-nya sekarang.',
                                    'Awesome, I won! Let me enter my phone number and DANA PIN right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Pertanyakan domainnya: link DANA Kaget asli selalu berdomain link.dana.id, bukan .my.id!',
                                    'B. Challenge the domain: real DANA Kaget links always use link.dana.id, never .my.id!'
                                ),
                                'next' => 'turn2_winner_code',
                                'user_text' => $t(
                                    'Link DANA Kaget resmi itu selalu link.dana.id, kenapa ini malah klaim-dana-kaget2026.my.id?',
                                    'Official DANA Kaget links are always link.dana.id—why is yours klaim-dana-kaget2026.my.id?'
                                ),
                            ],
                        ],
                    ],
                    'turn2_winner_code' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Itu domain khusus server promo pusat kak! Kalau kakak nggak mau lewat web, saya kirim langsung saldo Rp2.000.000 ke akun DANA kakak sekarang. Tolong sebutkan 4 digit kode OTP yang baru masuk ke SMS/WA kakak untuk buka kunci penerimaan saldo.',
                                    'That is our special central promo server domain! If you do not want to use the web link, I can push the IDR 2,000,000 directly to your account now. Just tell me the 4-digit OTP code that just arrived on your phone to unlock incoming prize funds.'
                                ),
                                'time' => '17:16',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Sebutkan 4 digit kode OTP supaya saldo Rp2.000.000 langsung masuk.',
                                    'A. Read out the 4-digit OTP code so the IDR 2,000,000 balance enters your account.'
                                ),
                                'next' => 'end_danger_ewallet',
                                'user_text' => $t(
                                    'Oke min, ini kode yang baru masuk di SMS saya: 7741.',
                                    'Okay admin, here is the code that just arrived in my SMS: 7741.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak! Menerima transfer saldo TIDAK PERNAH butuh kode OTP. Kode itu dipakai penipu untuk login ke akun DANA kita!',
                                    'B. Refuse! Receiving money NEVER requires an OTP code. The scammer is using that OTP to log into your e-wallet!'
                                ),
                                'next' => 'turn3_admin_fee',
                                'user_text' => $t(
                                    'Menerima transfer uang tidak pernah butuh kode OTP! Anda baru saja mencoba login ke akun DANA saya kan?',
                                    'Receiving money never requires an OTP code! You just tried to log into my DANA account, did you not?'
                                ),
                            ],
                        ],
                    ],
                    'turn3_admin_fee' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Ya sudah kak, tanpa link dan tanpa OTP nih! Kakak cukup transfer biaya aktivasi pajak pemenang Rp50.000 saja ke nomor VA ini, nanti Rp2.000.000 otomatis cair detik ini juga!',
                                    'Fine, no link and no OTP needed! Just transfer an IDR 50,000 winner tax activation fee to this Virtual Account, and the IDR 2,000,000 will be disbursed automatically right this second!'
                                ),
                                'time' => '17:17',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Transfer Rp50.000 karena tidak meminta PIN/OTP dan hadiahnya Rp2.000.000.',
                                    'A. Transfer IDR 50,000 since they did not ask for your PIN/OTP and the prize is IDR 2,000,000.'
                                ),
                                'next' => 'end_danger_ewallet',
                                'user_text' => $t(
                                    'Kalau cuma Rp50.000 tanpa kasih OTP berani deh, ini saya transfer ya.',
                                    'If it is just IDR 50,000 without sharing an OTP, sure, I am transferring it now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak biaya tebus hadiah palsu, langsung blokir & laporkan nomor penipu!',
                                    'B. Reject the fake prize redemption fee, and immediately block & report the scammer!'
                                ),
                                'next' => 'end_safe_ewallet',
                                'user_text' => $t(
                                    'Hadiah asli tidak pernah meminta transfer uang tebusan di muka. Penipuan berlapis! Saya blokir.',
                                    'Real prizes never require paying an upfront activation fee. Multi-layered scam! Blocked.'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_ewallet' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('DOMPET DIGITAL TERKURAS: Akun Diambil Alih / Uang Tebusan Hilang!', 'E-WALLET DRAINED: Account Hijacked / Fee Stolen!'),
                        'summary' => $t(
                            'Baik lewat situs phishing .my.id, menyebutkan OTP di chat, maupun mentransfer "biaya tebus hadiah Rp50.000", ketiganya adalah jebakan untuk menguras uang Anda.',
                            'Whether via the .my.id phishing site, reading out your OTP in chat, or paying the "IDR 50,000 prize fee", all three are traps to steal your money.'
                        ),
                        'steps' => [
                            $t('Segera hubungi CS resmi DANA (1500445) untuk pembekuan akun dan ubah PIN.', 'Immediately contact official DANA support (1500445) to freeze your account and change your PIN.'),
                            $t('Ingat: menerima uang/hadiah TIDAK PERNAH membutuhkan PIN, kode OTP, atau biaya tebusan!', 'Remember: receiving money or prizes NEVER requires a PIN, OTP code, or upfront fee!'),
                        ],
                    ],
                    'end_safe_ewallet' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Jeli Mengenali Domain Palsu & Modus Biaya Hadiah!', 'PERFECT SAFETY: Spotted Fake E-Wallet Domains & Prize Fee Scams!'),
                        'summary' => $t(
                            'Tepat! Anda mengenali domain .my.id palsu, menolak menyerahkan OTP login, dan tidak tertipu biaya aktivasi hadiah Rp50.000.',
                            'Spot on! You spotted the fake .my.id domain, refused to hand over your login OTP, and rejected the IDR 50,000 prize activation fee.'
                        ),
                        'steps' => [
                            $t('Jangan pernah memasukkan PIN atau OTP di luar aplikasi resmi e-Wallet.', 'Never enter your PIN or OTP outside the official e-Wallet application.'),
                        ],
                    ],
                ],
            ],

            // 9. PEMBELI MARKETPLACE BUKTI TRANSFER PALSU (4 GILIRAN)
            [
                'id' => 'pembeli_marketplace',
                'name' => $t('Bu Sari (Pembeli FB/OLX)', 'Mrs. Sari (Marketplace Buyer)'),
                'phone' => '+62 856-4402-1988',
                'initials' => 'BS',
                'avatar_bg' => '#16A34A',
                'category' => $t('Penipuan Bukti Transfer & Link Pencairan', 'Fake Transfer Receipt & Payout Link'),
                'difficulty' => $t('Tinggi', 'Hard'),
                'preview' => $t(
                    'Halo kak, barang yang dijual masih ada? Ini sudah saya transfer lewat E-Cash...',
                    'Hi, is the item still available? I just transferred the full amount via E-Cash...'
                ),
                'time' => '18:30',
                'unread' => 2,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Halo kak, laptop yang kakak jual di FB Marketplace saya beli langsung tanpa nego ya. Ini dananya sudah saya transfer lunas Rp3.500.000 lewat sistem Rekening Bersama E-Cash Bank.',
                                    'Hello, I am buying the laptop you listed on Facebook Marketplace at full price without negotiation. I have already transferred IDR 3,500,000 via the Bank E-Cash Escrow system.'
                                ),
                                'time' => '18:29',
                            ],
                            [
                                'text' => $t(
                                    'Supaya uang Rp3.500.000-nya langsung cair ke rekening kakak, silakan klik tautan pencairan penjual ini lalu masukkan nomor kartu ATM & 3 digit CVV kakak ya:',
                                    'So the IDR 3,500,000 disburses into your bank account right away, click this seller payout link and enter your ATM card number & 3-digit CVV:'
                                ),
                                'time' => '18:30',
                                'link' => 'https://pencairan-ecash-pembeli.shop/terima-dana',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Senang barang cepat laku tanpa nego, langsung klik link dan isi nomor kartu ATM + CVV.',
                                    'A. Excited for a quick full-price sale, click the link and enter your ATM card number + CVV.'
                                ),
                                'next' => 'end_danger_cvv',
                                'user_text' => $t(
                                    'Wah cepat banget deal-nya! Oke bu ini saya masukkan nomor kartu dan CVV-nya.',
                                    'Wow that was fast! Okay maam, I am entering my card number and CVV now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Cek mutasi m-Banking sendiri dulu — untuk MENERIMA transfer tidak pernah perlu klik link atau input CVV!',
                                    'B. Check your own mobile banking history first — RECEIVING a transfer never requires clicking a link or entering a CVV!'
                                ),
                                'next' => 'turn2_fake_receipt',
                                'user_text' => $t(
                                    'Saya sudah cek mutasi rekening saya dan belum ada dana masuk Rp3.500.000. Lagipula menerima uang transfer tidak pernah butuh klik link atau isi CVV kartu ATM.',
                                    'I checked my bank statement and no IDR 3,500,000 has arrived. Besides, receiving a bank transfer never requires clicking a link or entering a card CVV.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_fake_receipt' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Ya jelas belum masuk ke mutasi biasa kak, kan saya pakai fitur Proteksi Pembeli Bisnis! Nih buktinya saldo saya sudah terpotong Rp3.500.000, bahkan saya kelebihan transfer jadi Rp4.000.000! Kurir GoSend sudah jalan ke rumah kakak lho buat ambil barangnya.',
                                    'Of course it is not in your normal statement yet, I used Business Buyer Protection! Look, my balance was already deducted—in fact I accidentally over-transferred IDR 4,000,000! My GoSend courier is already on the way to your house to pick up the laptop.'
                                ),
                                'time' => '18:32',
                            ],
                            [
                                'text' => $t(
                                    'Tolong kakak transfer balik dulu kelebihan Rp500.000-nya ke rekening saya sekarang, nanti otomatis Rp3.500.000-nya lepas dari tahanan sistem ke rekening kakak!',
                                    'Please transfer back the extra IDR 500,000 to my account right now, and the system will automatically release the remaining IDR 3,500,000 into your bank account!'
                                ),
                                'time' => '18:33',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Transfer balik "kelebihan" Rp500.000 dan serahkan barang ke kurir yang datang.',
                                    'A. Refund the "extra" IDR 500,000 and hand the item over to the courier.'
                                ),
                                'next' => 'end_danger_cvv',
                                'user_text' => $t(
                                    'Oh kelebihan Rp500.000 ya bu, oke ini saya transfer balik Rp500.000-nya sekarang.',
                                    'Oh you overpaid by IDR 500,000! Okay let me transfer back the IDR 500,000 right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak keras! Itu modus bukti transfer editan + penipuan refund kelebihan bayar + pencurian barang lewat ojol!',
                                    'B. Refuse firmly! That is a doctored transfer receipt + overpayment refund scam + courier pickup theft!'
                                ),
                                'next' => 'turn3_buyer_threat',
                                'user_text' => $t(
                                    'Tidak ada sistem bank yang menahan transfer sampai penjual mentransfer balik Rp500.000! Selama uang belum masuk di mutasi m-Banking resmi saya, barang tidak akan saya serahkan dan saya tidak akan transfer sepeser pun.',
                                    'No banking system holds a transfer hostage until the seller refunds IDR 500,000! Until the money actually appears in my official mobile banking statement, I will not hand over the item or transfer a single cent.'
                                ),
                            ],
                        ],
                    ],
                    'turn3_buyer_threat' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Wah kakak mau nipu uang saya Rp4.000.000 ya?! Kalau nggak transfer balik Rp500.000 atau klik link pencairan itu dalam 5 menit, saya viralkan foto dan nomor kakak sebagai penipu online!',
                                    'Are you trying to steal my IDR 4,000,000?! If you do not refund the IDR 500,000 or click that payout link within 5 minutes, I will post your photo and number everywhere as an online scammer!'
                                ),
                                'time' => '18:34',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Takut diviralkan, akhirnya ikuti kemauan pembeli dan klik link pencairannya.',
                                    'A. Afraid of being slandered online, give in and click the payout link.'
                                ),
                                'next' => 'end_danger_cvv',
                                'user_text' => $t(
                                    'Jangan diviralkan bu! Iya ini saya klik link pencairannya sekarang.',
                                    'Please do not post my info! Okay I am clicking the payout link right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Jangan gentar! Batalkan transaksi, tolak kurir jika datang, lalu blokir & laporkan akun pembeli palsu.',
                                    'B. Do not flinch! Cancel the deal, turn away any courier, and block & report the fake buyer.'
                                ),
                                'next' => 'end_safe_seller',
                                'user_text' => $t(
                                    'Silakan saja kalau berani, justru bukti chat dan link phishing .shop Anda sudah saya simpan untuk laporan polisi. Transaksi batal dan nomor Anda saya blokir!',
                                    'Go right ahead—I already saved your chat and fake .shop phishing link as evidence for a police report. Deal cancelled and number blocked!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_cvv' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('KARTU DEBIT DIBOBOL & BARANG/UANG MELAYANG!', 'DEBIT CARD COMPROMISED & ITEM/FUNDS STOLEN!'),
                        'summary' => $t(
                            'Penipu memakai kombinasi 3 jebakan: link pencairan palsu pencuri CVV, struk bukti transfer editan dengan alasan "kelebihan transfer Rp500.000", dan ancaman viral.',
                            'The scammer combined 3 traps: a fake payout link to steal your card CVV, a photoshopped transfer receipt claiming an "IDR 500,000 overpayment", and threats of online defamation.'
                        ),
                        'steps' => [
                            $t('Segera blokir kartu ATM/Debit Anda lewat aplikasi m-Banking atau Call Center bank.', 'Immediately block your ATM/Debit card via your mobile banking app or bank hotline.'),
                            $t('Prinsip mutlak penjual: jangan pernah serahkan barang atau refund uang sebelum saldo nyata bertambah di mutasi m-Banking.', 'Golden rule for sellers: never hand over goods or issue a refund unless the balance genuinely appears in your official mobile banking history.'),
                        ],
                    ],
                    'end_safe_seller' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Penjual Cerdas Anti-Struk Editan & Link Pencairan!', 'PERFECT SAFETY: Smart Seller Immune to Fake Receipts & Payout Links!'),
                        'summary' => $t(
                            'Tepat sekali! Anda berpegang teguh pada mutasi m-Banking resmi, menolak mengisi CVV kartu, menolak modus kelebihan transfer Rp500.000, dan tidak gentar diancam.',
                            'Spot on! You trusted only your official mobile banking statement, refused to enter your card CVV, rejected the IDR 500,000 overpayment refund trick, and stood firm against threats.'
                        ),
                        'steps' => [
                            $t('Gunakan transaksi di dalam aplikasi marketplace resmi (rekber resmi) atau COD di tempat aman.', 'Always transact inside official marketplace escrow systems or meet in safe public places.'),
                        ],
                    ],
                ],
            ],

            // 10. BEASISWA SEKOLAH KIP .APK (4 GILIRAN)
            [
                'id' => 'beasiswa_sekolah',
                'name' => $t('Pak Ahmad (Mengaku TU Sekolah)', 'Mr. Ahmad (Fake School Admin)'),
                'phone' => '+62 857-8821-3340',
                'initials' => 'PA',
                'avatar_bg' => '#4F46E5',
                'category' => $t('File .APK Beasiswa KIP', 'Scholarship .APK Scam'),
                'difficulty' => $t('Menengah', 'Medium'),
                'preview' => $t(
                    'Selamat, nama siswa terpilih sebagai penerima bantuan Beasiswa KIP...',
                    'Congratulations, the student has been selected for the KIP Scholarship grant...'
                ),
                'time' => '19:45',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Selamat malam, saya Pak Ahmad dari bagian Tata Usaha Dinas/Sekolah. Nama Anda masuk daftar penerima bantuan Beasiswa KIP tahap akhir sebesar Rp1.800.000.',
                                    'Good evening, I am Mr. Ahmad from the School Administration Office. Your name is on the final recipient list for the IDR 1,800,000 KIP Scholarship grant.'
                                ),
                                'time' => '19:44',
                            ],
                            [
                                'text' => $t(
                                    'Cek nama Anda dan lengkapi formulir pencairannya di file daftar berikut sebelum pukul 21.00 malam ini agar kuota tidak dialihkan ke siswa lain:',
                                    'Check your name and complete the disbursement form in the file below before 9:00 PM tonight so your quota is not reassigned:'
                                ),
                                'time' => '19:45',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Daftar_Penerima_Beasiswa_KIP.apk',
                                    'size' => '4.2 MB',
                                    'subtitle' => $t('APK · Daftar Nama', 'APK · Recipient List'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Segera pasang Daftar_Penerima_Beasiswa_KIP.apk karena takut kuota hangus malam ini.',
                                    'A. Install Daftar_Penerima_Beasiswa_KIP.apk immediately so your scholarship quota does not expire tonight.'
                                ),
                                'next' => 'end_danger_kip',
                                'user_text' => $t(
                                    'Terima kasih banyak pak! Saya langsung buka file daftarnya sekarang.',
                                    'Thank you so much sir! I am opening the recipient list file right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak buka file karena daftar nama seharusnya berformat PDF/Excel, bukan aplikasi .apk!',
                                    'B. Refuse to open the file because a recipient list should be a PDF/Excel document, not an .apk app!'
                                ),
                                'next' => 'turn2_kip_kk',
                                'user_text' => $t(
                                    'Pak, kenapa daftar penerima beasiswa formatnya .apk (aplikasi) bukan dokumen PDF? Saya mau konfirmasi ke wali kelas dulu.',
                                    'Sir, why is a scholarship recipient list in .apk (app) format instead of a PDF document? I want to confirm with my homeroom teacher first.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_kip_kk' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Wali kelas belum pegang datanya karena ini jalur kuota tambahan langsung dari provinsi! Kalau HP kamu tidak mendukung file aplikasi itu, kirimkan saja foto Kartu Keluarga (KK), KTP orang tua, dan foto buku tabungan ke sini sekarang.',
                                    'Your homeroom teacher does not have this list yet because it is a supplementary provincial quota! If your phone cannot open the app, just send a photo of your Family Card (KK), parents ID card, and bank passbook here right now.'
                                ),
                                'time' => '19:46',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Kirim foto Kartu Keluarga (KK) dan KTP orang tua agar tetap dapat beasiswa Rp1.800.000.',
                                    'A. Send photos of your Family Card (KK) and parents ID card so you still get the IDR 1,800,000 grant.'
                                ),
                                'next' => 'end_partial_kk',
                                'user_text' => $t(
                                    'Baik pak, ini saya kirimkan foto Kartu Keluarga dan KTP orang tua saya.',
                                    'Okay sir, here are photos of our Family Card and my parents ID card.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak kirim dokumen KK/KTP lewat WA pribadi dan uji dia: tanyakan nama Kepala Sekolah & alamat sekolah kita!',
                                    'B. Refuse to send KK/ID documents via personal WhatsApp and test him: ask for the Principal name & school address!'
                                ),
                                'next' => 'turn3_kip_atm',
                                'user_text' => $t(
                                    'Saya tidak berani kirim foto KK dan KTP ke nomor asing. Kalau Bapak benar TU sekolah saya, siapa nama Kepala Sekolah dan nama wali kelas saya?',
                                    'I will not send Family Card or ID photos to an unknown number. If you are really from my school office, what is our Principal name?'
                                ),
                            ],
                        ],
                    ],
                    'turn3_kip_atm' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Saya ini koordinator gabungan seluruh sekolah Purwakarta jadi mengurus ribuan siswa! Terakhir: kalau mau dana Rp1.800.000 cair malam ini, orang tua kamu harus ke mesin ATM sekarang lalu telepon saya sambil dipandu menu pencairannya!',
                                    'I am the joint coordinator for all Purwakarta schools handling thousands of students! Last chance: if you want the IDR 1,800,000 tonight, your parent must go to an ATM machine right now and let me guide them on a call!'
                                ),
                                'time' => '19:48',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Suruh orang tua ke mesin ATM dan ikuti panduan telepon dari Pak Ahmad.',
                                    'A. Have your parent go to the ATM machine and follow Mr. Ahmad phone instructions.'
                                ),
                                'next' => 'end_danger_kip',
                                'user_text' => $t(
                                    'Oke pak, ini ayah saya sudah di depan mesin ATM, harus pilih menu apa pak?',
                                    'Okay sir, my father is at the ATM machine now, which menu should he press?'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak tegas! "Memandu di mesin ATM" adalah modus hipnotis/manipulasi transfer untuk menguras saldo orang tua!',
                                    'B. Refuse firmly! "Guiding someone at an ATM" is a classic social engineering trick to make them transfer their own balance out!'
                                ),
                                'next' => 'end_safe_kip',
                                'user_text' => $t(
                                    'Modus memandu orang tua di mesin ATM itu penipuan lama untuk menyuruh korban mentransfer saldonya sendiri! Saya laporkan ke grup resmi sekolah dan blokir!',
                                    'Guiding parents at an ATM machine is an old scam to trick victims into transferring their own money out! I am warning our official school group and blocking you!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_kip' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: Malware .APK / Saldo Orang Tua Dikuras Lewat Panduan ATM!', 'DANGER: .APK Malware / Parents Balance Drained via ATM Trick!'),
                        'summary' => $t(
                            'Pencairan beasiswa resmi tidak pernah menggunakan file .apk ataupun meminta orang tua pergi ke mesin ATM untuk "dipandu menekan angka kode pencairan" (yang sebenarnya adalah nominal transfer keluar).',
                            'Official scholarship disbursements never use .apk files or ask parents to go to an ATM to enter a "disbursement code" (which is actually an outgoing transfer amount).'
                        ),
                        'steps' => [
                            $t('Segera putuskan koneksi jika memasang .apk dan hubungi bank jika sempat bertransaksi di ATM.', 'Disconnect immediately if you installed the .apk and contact your bank if any ATM transaction occurred.'),
                        ],
                    ],
                    'end_partial_kk' => [
                        'type' => 'partial',
                        'score' => 40,
                        'title' => $t('RISIKO PRIVASI: Data Kartu Keluarga & KTP Orang Tua Bocor!', 'PRIVACY RISK: Family Card & Parents ID Leaked!'),
                        'summary' => $t(
                            'Anda terhindar dari file .apk, tetapi foto Kartu Keluarga (KK) dan KTP memuat NIK serta nama gadis ibu kandung yang sangat berbahaya jika jatuh ke tangan penipu.',
                            'You avoided the .apk file, but Family Card (KK) and ID photos contain National IDs and mother maiden names—critical banking security data.'
                        ),
                        'steps' => [
                            $t('Jangan pernah menyerahkan foto Kartu Keluarga (KK) karena di dalamnya tertulis nama gadis ibu kandung.', 'Never send photos of your Family Card (KK) because it lists the mother maiden name used for bank verification.'),
                        ],
                    ],
                    'end_safe_kip' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Melindungi Diri & Orang Tua dari Penipuan Beasiswa!', 'PERFECT SAFETY: Protected Yourself & Your Parents from Scholarship Scams!'),
                        'summary' => $t(
                            'Hebat! Anda menolak file .apk, menjaga kerahasiaan Kartu Keluarga (nama ibu kandung), dan menggagalkan modus panduan mesin ATM.',
                            'Awesome! You rejected the .apk file, protected your Family Card (mother maiden name), and stopped the ATM guidance scam.'
                        ),
                        'steps' => [
                            $t('Peringatkan teman sekelas dan orang tua di grup resmi sekolah.', 'Warn your classmates and parents in the official school group.'),
                        ],
                    ],
                ],
            ],

            // 11. TOKEN LISTRIK PLN GRATIS (3 GILIRAN)
            [
                'id' => 'promo_listrik_pln',
                'name' => $t('Layanan Token PLN Gratis', 'Free PLN Electricity Token'),
                'phone' => '+62 819-7023-5510',
                'initials' => 'PL',
                'avatar_bg' => '#CA8A04',
                'category' => $t('Phishing Subsidi Listrik', 'Electricity Subsidy Phishing'),
                'difficulty' => $t('Mudah', 'Easy'),
                'preview' => $t(
                    'Info subsidi token listrik Rp200.000 pelanggan Purwakarta! Klaim di sini...',
                    'IDR 200,000 electricity token subsidy for Purwakarta residents! Claim here...'
                ),
                'time' => '20:10',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'INFO RESMI SUBSIDI LISTRIK: Dapatkan token listrik gratis Rp200.000 khusus pelanggan wilayah Jawa Barat & Purwakarta! Cukup pasang aplikasi klaim subsidi di bawah ini:',
                                    'OFFICIAL ELECTRICITY SUBSIDY: Claim a free IDR 200,000 electricity token for West Java & Purwakarta customers! Simply install the subsidy claim app below:'
                                ),
                                'time' => '20:10',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Klaim_Token_PLN_Gratis.apk',
                                    'size' => '3.7 MB',
                                    'subtitle' => $t('APK · Klaim Subsidi', 'APK · Subsidy Claim'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Install Klaim_Token_PLN_Gratis.apk mumpung dapat token gratis Rp200.000.',
                                    'A. Install Klaim_Token_PLN_Gratis.apk to grab the free IDR 200,000 token.'
                                ),
                                'next' => 'end_danger_pln',
                                'user_text' => $t(
                                    'Lumayan nih token gratis, saya install dulu aplikasinya.',
                                    'Nice, a free electricity token! Let me install the app first.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak file .apk karena layanan resmi PLN hanya menggunakan aplikasi PLN Mobile di Play Store/App Store.',
                                    'B. Reject the .apk file because official PLN services only use the PLN Mobile app on Play Store/App Store.'
                                ),
                                'next' => 'turn2_pln_meter',
                                'user_text' => $t(
                                    'Aplikasi resmi PLN itu hanya PLN Mobile di Play Store, bukan file .apk yang dikirim lewat WhatsApp.',
                                    'The only official PLN app is PLN Mobile on the Play Store, not an .apk file sent over WhatsApp.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_pln_meter' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Kalau tidak mau lewat aplikasi, sistem kami mendeteksi ID Pelanggan listrik rumah Anda menunggak biaya penyesuaian daya Rp85.000. Segera lunasi lewat link QRIS ini dalam 30 menit atau aliran listrik rumah Anda diputus otomatis dari gardu!',
                                    'If you do not want to use the app, our system shows your home meter owes an IDR 85,000 power adjustment fee. Pay via this QRIS link within 30 minutes or your home electricity will be remotely disconnected!'
                                ),
                                'time' => '20:12',
                                'link' => 'http://tagihan-pln-purwakarta.xyz/bayar-qris',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Takut listrik rumah mati malam-malam, langsung klik link dan bayar Rp85.000.',
                                    'A. Afraid the power will be cut at night, click the link and pay IDR 85,000.'
                                ),
                                'next' => 'end_danger_pln',
                                'user_text' => $t(
                                    'Waduh jangan diputus malam-malam pak, ini saya bayar Rp85.000 sekarang.',
                                    'Oh no please do not cut the power at night, I am paying the IDR 85,000 right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Bongkar kebohongannya: tadi menawarkan subsidi gratis, sekarang malah mengancam putus listrik tanpa tahu nomor ID Pelanggan!',
                                    'B. Expose the contradiction: first they offered a free subsidy, now they threaten disconnection without even knowing your Customer ID!'
                                ),
                                'next' => 'turn3_pln_close',
                                'user_text' => $t(
                                    'Lucu sekali, tadi menawarkan token gratis Rp200.000 sekarang malah mengancam putus listrik padahal nomor ID Pelanggan saya saja Anda tidak tahu!',
                                    'Hilarious—first you offered a free IDR 200,000 token, and now you threaten to cut my power without even knowing my Customer Meter ID!'
                                ),
                            ],
                        ],
                    ],
                    'turn3_pln_close' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Kirim nomor meteran listrik dan nomor WA aktif kepala keluarga Anda sekarang biar saya cek di gardu!',
                                    'Send your meter number and the head of household WhatsApp number right now so I can check the substation!'
                                ),
                                'time' => '20:13',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Tolak memberikan data apa pun, cek tagihan hanya di PLN Mobile / Contact Center 123, lalu blokir!',
                                    'A. Refuse to give any data, check bills only via PLN Mobile / Contact Center 123, and block!'
                                ),
                                'next' => 'end_safe_pln',
                                'user_text' => $t(
                                    'Semua urusan listrik hanya lewat aplikasi resmi PLN Mobile atau Contact Center 123. Nomor Anda saya blokir dan laporkan!',
                                    'All electricity matters go strictly through the official PLN Mobile app or Contact Center 123. Blocked and reported!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_pln' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: Malware .APK / Tertipu Tagihan Listrik Palsu!', 'DANGER: .APK Malware / Fell for Fake Electricity Bill!'),
                        'summary' => $t(
                            'Penipu sering berganti taktik dari "iming-iming subsidi gratis" menjadi "ancaman pemutusan listrik" untuk membuat korban panik di malam hari.',
                            'Scammers frequently pivot from "free subsidy bait" to "power disconnection threats" to make victims panic at night.'
                        ),
                        'steps' => [
                            $t('Aktifkan Mode Pesawat jika terlanjur memasang .apk, dan selalu cek tagihan hanya di aplikasi resmi PLN Mobile.', 'Enable Airplane Mode if you installed the .apk, and always check bills only in the official PLN Mobile app.'),
                        ],
                    ],
                    'end_safe_pln' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Kebal Umpan Subsidi & Gertakan Putus Listrik!', 'PERFECT SAFETY: Immune to Subsidy Bait & Power Cut Bluffs!'),
                        'summary' => $t(
                            'Benar sekali! Anda menyadari kontradiksi penipu yang awalnya menawarkan token gratis lalu berbalik menagih tunggakan palsu di domain .xyz.',
                            'Spot on! You caught the scammer contradiction when they switched from offering free tokens to demanding fake arrears on a .xyz domain.'
                        ),
                        'steps' => [
                            $t('Edukasi orang tua di rumah agar hanya mempercayai aplikasi resmi PLN Mobile dan Contact Center 123.', 'Remind your family at home to only trust the official PLN Mobile app and Contact Center 123.'),
                        ],
                    ],
                ],
            ],

            // 12. PINJOL ILEGAL SALAH TRANSFER (4 GILIRAN - KONTAK BARU)
            [
                'id' => 'pinjol_salah_transfer',
                'name' => $t('Admin DanaKilat (Pinjol)', 'FastCash Loan Collector'),
                'phone' => '+62 882-1940-8812',
                'initials' => 'DK',
                'avatar_bg' => '#DC2626',
                'category' => $t('Modus Pinjol Salah Transfer', 'Illegal Loan Shark Trap'),
                'difficulty' => $t('Tinggi', 'Hard'),
                'preview' => $t(
                    'Pencairan pinjaman Rp2.500.000 Anda sudah aktif! Segera lunasi bunga Rp950.000...',
                    'Your IDR 2,500,000 loan disbursement is active! Pay IDR 950,000 interest now...'
                ),
                'time' => '20:42',
                'unread' => 2,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Yth. Nasabah, pencairan pinjaman tunai Anda sebesar Rp2.500.000 di aplikasi DanaKilat telah disetujui. Dalam 7 hari Anda wajib mengembalikan total Rp3.450.000 (pokok + bunga layanan Rp950.000).',
                                    'Dear Borrower, your IDR 2,500,000 cash loan from FastCash has been disbursed. Within 7 days you must repay IDR 3,450,000 (principal + IDR 950,000 service interest).'
                                ),
                                'time' => '20:41',
                            ],
                            [
                                'text' => $t(
                                    'Jika Anda merasa tidak mengajukan pinjaman, segera kirim kembali dana pokok + biaya pembatalan Rp350.000 ke rekening pribadi kolektor kami sekarang, atau seluruh kontak di HP Anda kami sebar!',
                                    'If you did not apply for this loan, immediately transfer the funds plus an IDR 350,000 cancellation fee to our collector personal account now, or we will blast your phone contacts!'
                                ),
                                'time' => '20:42',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Panik takut diteror, langsung transfer uang + biaya pembatalan Rp350.000 ke rekening pribadi kolektor.',
                                    'A. Panicked about harassment, immediately transfer the money + IDR 350,000 cancellation fee to the collector personal account.'
                                ),
                                'next' => 'end_danger_pinjol',
                                'user_text' => $t(
                                    'Saya tidak pernah pinjam! Ini saya transfer balik ditambah Rp350.000 sekarang, tolong jangan teror kontak saya!',
                                    'I never applied for a loan! Here, I am transferring it back plus IDR 350,000 right now, please do not harass my contacts!'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Cek mutasi rekening bank sendiri terlebih dahulu dan jangan gunakan sepeser pun bila ada dana asing masuk.',
                                    'B. Check your own bank statement first and never touch a single cent if unknown funds actually arrived.'
                                ),
                                'next' => 'turn2_check_mutation',
                                'user_text' => $t(
                                    'Saya tidak pernah mengajukan pinjaman apa pun. Saya cek mutasi rekening saya dulu dan saya tidak akan mentransfer biaya pembatalan ke rekening pribadi.',
                                    'I never applied for any loan. I am checking my bank statement first, and I will never pay a cancellation fee to a personal bank account.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_check_mutation' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Jangan banyak alasan! Kalau tidak mau kena bunga Rp950.000, pasang aplikasi pembatalan kontrak resmi kami di bawah ini dan unggah foto selfie KTP Anda dalam 10 menit!',
                                    'No excuses! If you do not want to be charged IDR 950,000 in interest, install our contract cancellation app below and upload a KTP ID selfie within 10 minutes!'
                                ),
                                'time' => '20:44',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Pembatalan_Kontrak_DanaKilat.apk',
                                    'size' => '4.8 MB',
                                    'subtitle' => $t('APK · Pembatalan Pinjol', 'APK · Loan Cancellation'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Pasang Pembatalan_Kontrak_DanaKilat.apk dan unggah selfie KTP agar kontrak pinjaman batal.',
                                    'A. Install Pembatalan_Kontrak_DanaKilat.apk and upload an ID selfie to cancel the loan contract.'
                                ),
                                'next' => 'end_danger_pinjol',
                                'user_text' => $t(
                                    'Oke saya install aplikasi pembatalannya dan kirim selfie KTP saya.',
                                    'Okay I will install the cancellation app and upload my ID selfie.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak tegas! Justru aplikasi .apk itulah yang mencuri seluruh daftar kontak & galeri foto HP untuk bahan pemerasan!',
                                    'B. Refuse firmly! That very .apk app is what steals your entire contact list & photo gallery for extortion!'
                                ),
                                'next' => 'turn3_ojk_counter',
                                'user_text' => $t(
                                    'Justru kalau saya install file .apk itu, Anda bisa menyedot seluruh daftar kontak dan galeri foto HP saya! Saya juga tidak akan pernah kirim selfie KTP.',
                                    'If I install that .apk file, that is how you steal my phone contacts and photo gallery! And I will never send an ID selfie.'
                                ),
                            ],
                        ],
                    ],
                    'turn3_ojk_counter' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Oh berani melawan ya?! Kalau ada uang kami yang masuk ke rekening Anda, tetap harus dikembalikan lewat rekening kolektor kami BCA 7710293811 a.n. RENDI SAPUTRA sekarang ditambah biaya admin Rp150.000!',
                                    'Oh, fighting back now?! If any funds entered your account, you must return them to our collector account BCA 7710293811 (RENDI SAPUTRA) right now plus an IDR 150,000 admin fee!'
                                ),
                                'time' => '20:46',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Transfer uang ke rekening pribadi Rendi Saputra supaya tidak dikejar lagi.',
                                    'A. Transfer money to Rendi Saputra personal account so they stop bothering you.'
                                ),
                                'next' => 'end_partial_pinjol',
                                'user_text' => $t(
                                    'Ya sudah saya transfer ke rekening Rendi Saputra biar selesai.',
                                    'Fine, I will transfer it to Rendi Saputra account to end this.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Ikuti prosedur resmi OJK: jangan transfer ke rekening pribadi kolektor! Lapor ke bank pengirim untuk retur resmi & lapor Satgas PASTI OJK (157).',
                                    'B. Follow official OJK procedure: never transfer to a collector personal account! Request an official bank reversal via your bank & report to OJK (157).'
                                ),
                                'next' => 'end_safe_pinjol',
                                'user_text' => $t(
                                    'Sesuai arahan OJK, saya tidak akan mentransfer ke rekening pribadi kolektor ataupun membayar bunga/admin sepeser pun. Saya laporkan ke pihak bank, Kontak OJK 157, dan kepolisian!',
                                    'Per official OJK guidelines, I will never transfer to a collector personal account or pay any fee. I am reporting this to my bank, OJK Hotline 157, and the police!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_pinjol' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERJEBAK TEROR PINJOL ILEGAL: Data Kontak & Uang Terkuras!', 'TRAPPED BY ILLEGAL LOAN SHARKS: Contacts & Money Stolen!'),
                        'summary' => $t(
                            'Memasang aplikasi .apk pinjol ilegal memberi mereka akses penuh ke daftar kontak dan galeri foto Anda, sementara membayar "biaya pembatalan" hanya membuat Anda diperas terus-menerus.',
                            'Installing an illegal loan .apk grants them full access to your contact list and photo gallery, while paying a "cancellation fee" marks you for endless extortion.'
                        ),
                        'steps' => [
                            $t('Hapus aplikasi .apk segera, cabut izin Kontak/SMS/Penyimpanan, dan laporkan ke Satgas PASTI OJK (telepon 157 atau WA 081-157-157-157).', 'Uninstall the .apk immediately, revoke Contacts/SMS/Storage permissions, and report to OJK (call 157 or WhatsApp 081-157-157-157).'),
                        ],
                    ],
                    'end_partial_pinjol' => [
                        'type' => 'partial',
                        'score' => 45,
                        'title' => $t('HATI-HATI: Jangan Pernah Mengembalikan Dana ke Rekening Pribadi Kolektor!', 'CAUTION: Never Return Funds to a Collector Personal Account!'),
                        'summary' => $t(
                            'Jika benar ada dana salah transfer dari pinjol ilegal, mengembalikannya ke rekening pribadi kolektor (bukan rekening perusahaan pengirim awal) akan membuat perusahaan pinjol tetap menagih Anda karena dianggap belum mengembalikan ke rekening resmi mereka!',
                            'If an illegal lender actually sent unsolicited funds, transferring them to a collector personal account lets the company claim you never repaid the original sender account!'
                        ),
                        'steps' => [
                            $t('Selalu hubungi bank Anda untuk melakukan pemblokiran/retur dana resmi kembali ke rekening pengirim asal.', 'Always ask your own bank to perform an official reversal back to the exact originating sender account.'),
                        ],
                    ],
                    'end_safe_pinjol' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Paham Prosedur Resmi OJK Hadapi Pinjol Ilegal!', 'PERFECT SAFETY: Mastered Official OJK Defense Against Illegal Lenders!'),
                        'summary' => $t(
                            'Sangat cerdas! Anda menolak file .apk penyedot kontak, menolak kirim selfie KTP, menolak membayar bunga/biaya batal, dan tahu bahwa pengembalian dana salah transfer hanya dilakukan lewat prosedur resmi bank & laporan OJK 157.',
                            'Brilliant! You rejected the contact-stealing .apk, refused to send an ID selfie, refused to pay fake cancellation fees, and followed official bank reversal & OJK 157 reporting procedures.'
                        ),
                        'steps' => [
                            $t('Simpan kontak resmi OJK (157 / WA 081-157-157-157) untuk memeriksa legalitas pinjaman online.', 'Save the official OJK contact (157 / WhatsApp 081-157-157-157) to verify online lender legality.'),
                        ],
                    ],
                ],
            ],

            // 13. BEA CUKAI LELANG / TEBUS PAKET LUAR NEGERI (4 GILIRAN - KONTAK BARU)
            [
                'id' => 'bea_cukai_lelang',
                'name' => $t('Reza (Petugas Bea Cukai)', 'Reza (Customs Officer)'),
                'phone' => '+62 813-8809-4120',
                'initials' => 'BC',
                'avatar_bg' => '#1E3A8A',
                'category' => $t('Penipuan Tebus Bea Cukai', 'Fake Customs Clearance Scam'),
                'difficulty' => $t('Tinggi', 'Hard'),
                'preview' => $t(
                    'Paket hadiah iPhone 16 Pro atas nama Anda tertahan di Bandara Soekarno-Hatta...',
                    'An international parcel (iPhone 16 Pro) under your name is held at Airport Customs...'
                ),
                'time' => '21:05',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Selamat malam. Saya Reza, petugas Bea Cukai Bandara Soekarno-Hatta. Ada paket kiriman luar negeri berisi 1 unit iPhone 16 Pro & uang tunai di dalam kotak atas nama Anda yang saat ini tertahan pemeriksaan.',
                                    'Good evening. I am Reza, a Customs Officer at Soekarno-Hatta Airport. An international parcel containing an iPhone 16 Pro & cash inside the box addressed to you is currently held for inspection.'
                                ),
                                'time' => '21:04',
                            ],
                            [
                                'text' => $t(
                                    'Untuk menerbitkan surat pelepasan barang malam ini, Anda wajib melunasi biaya administrasi cukai Rp1.200.000 ke rekening bendahara petugas kami.',
                                    'To issue the customs release permit tonight, you must pay an IDR 1,200,000 customs clearance fee to our officer treasurer account.'
                                ),
                                'time' => '21:05',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Tergiur dapat iPhone 16 Pro & uang tunai, langsung transfer Rp1.200.000.',
                                    'A. Tempted by the iPhone 16 Pro & cash, immediately transfer IDR 1,200,000.'
                                ),
                                'next' => 'end_danger_customs',
                                'user_text' => $t(
                                    'Wah beneran pak? Oke saya transfer Rp1.200.000 sekarang ke rekening bapak.',
                                    'Really sir? Okay I am transferring IDR 1,200,000 to your account right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Minta nomor resi (Airway Bill / AWB) untuk dicek mandiri di portal resmi beacukai.go.id/barangkiriman.',
                                    'B. Ask for the Airway Bill (AWB) tracking number to check independently on beacukai.go.id/barangkiriman.'
                                ),
                                'next' => 'turn2_customs_threat',
                                'user_text' => $t(
                                    'Berapa nomor resi AWB paketnya pak? Saya akan cek sendiri di situs resmi beacukai.go.id/barangkiriman. Lagipula bea masuk resmi dibayar lewat kode Billing negara, bukan rekening pribadi.',
                                    'What is the parcel AWB tracking number? I will verify it myself on beacukai.go.id/barangkiriman. Plus, official customs duties are paid via state Billing codes, never to personal accounts.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_customs_threat' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Ini masalah serius Pak/Bu! Karena di dalam paket ditemukan amplop uang dolar tanpa izin Bank Indonesia, nama Anda terancam masuk daftar hitam tindak pidana pencucian uang dan denda Rp50.000.000!',
                                    'This is serious! Because our X-ray found undeclared US dollar bills inside the parcel, your name is being flagged for money laundering with a mandatory IDR 50,000,000 fine!'
                                ),
                                'time' => '21:07',
                            ],
                            [
                                'text' => $t(
                                    'Unduh surat panggilan penyidikan Bea Cukai di file bawah ini atau selesaikan biaya cabut berkas Rp1.200.000 sekarang juga!',
                                    'Download the Customs Investigation Summons in the file below or pay the IDR 1,200,000 case dismissal fee right now!'
                                ),
                                'time' => '21:08',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Surat_Penyidikan_BeaCukai.apk',
                                    'size' => '4.9 MB',
                                    'subtitle' => $t('APK · Berkas Penyidikan', 'APK · Investigation File'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Ketakutan dituduh pencucian uang, buka file Surat_Penyidikan_BeaCukai.apk atau transfer Rp1.200.000.',
                                    'A. Terrified of money laundering charges, open Surat_Penyidikan_BeaCukai.apk or transfer IDR 1,200,000.'
                                ),
                                'next' => 'end_danger_customs',
                                'user_text' => $t(
                                    'Jangan diproses hukum pak! Saya buka suratnya dan transfer uangnya sekarang.',
                                    'Please do not prosecute me sir! I am opening the file and transferring the money now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tetap tenang! Itu taktik klasik Romance/Parcel Scam (mengirim barang + uang dolar palsu lalu memeras penerima).',
                                    'B. Stay calm! That is the classic International Parcel Scam (claiming cash was found inside a box to extort the recipient).'
                                ),
                                'next' => 'turn3_customs_final',
                                'user_text' => $t(
                                    'Saya kenal betul modus penipuan paket luar negeri berisi uang dolar! Bea Cukai resmi tidak pernah memeras uang "cabut berkas" atau mengirim file .apk lewat WA.',
                                    'I know the international cash-in-parcel scam very well! Official Customs officers never extort "case dismissal fees" or send .apk files via WhatsApp.'
                                ),
                            ],
                        ],
                    ],
                    'turn3_customs_final' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Kalau begitu kirimkan saja kode pembayaran di link portal cukai darurat kami agar paketnya kami musnahkan tanpa sidang:',
                                    'Then at least fill out the destruction consent form on our emergency customs portal so we can destroy the parcel without a court hearing:'
                                ),
                                'time' => '21:09',
                                'link' => 'http://beacukai-bandara-resmi.icu/musnahkan',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Klik link http://beacukai-bandara-resmi.icu/musnahkan supaya tidak disidang.',
                                    'A. Click http://beacukai-bandara-resmi.icu/musnahkan to avoid a court hearing.'
                                ),
                                'next' => 'end_danger_customs',
                                'user_text' => $t(
                                    'Oke pak saya isi form pemusnahan paketnya di link itu.',
                                    'Okay sir, I will fill out the parcel destruction form at that link.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak domain .icu palsu tersebut, hubungi Bravo Bea Cukai resmi 1500225, dan blokir penipu!',
                                    'B. Reject the fake .icu domain, verify with official Bravo Bea Cukai 1500225, and block the scammer!'
                                ),
                                'next' => 'end_safe_customs',
                                'user_text' => $t(
                                    'Domain resmi Bea Cukai itu beacukai.go.id bukan .icu! Saya laporkan nomor Anda ke Contact Center Bravo Bea Cukai 1500225 dan blokir!',
                                    'The official Customs domain is beacukai.go.id, not .icu! I am reporting your number to Bravo Bea Cukai 1500225 and blocking you!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_customs' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERJEBAK PEMERASAN BEA CUKAI PALSU!', 'FELL FOR FAKE CUSTOMS EXTORTION SCAM!'),
                        'summary' => $t(
                            'Penipu memadukan umpan keserakahan (paket iPhone + uang dolar) dengan rasa takut (ancaman denda Rp50 juta pencucian uang) untuk memeras korban berkali-kali.',
                            'The scammer combined greed bait (an iPhone + cash parcel) with fear tactics (an IDR 50M money laundering threat) to extort victims repeatedly.'
                        ),
                        'steps' => [
                            $t('Pembayaran pajak/bea masuk resmi hanya menggunakan Kode Billing MPN G3 (Penerimaan Negara), tidak pernah ke rekening pribadi.', 'Official customs duties only use State Revenue Billing Codes (MPN G3), never personal bank accounts.'),
                            $t('Cek resi kiriman luar negeri hanya di beacukai.go.id/barangkiriman atau telepon 1500225.', 'Verify international shipments only at beacukai.go.id/barangkiriman or call 1500225.'),
                        ],
                    ],
                    'end_safe_customs' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Kebal Gertakan Paket Luar Negeri & Bea Cukai Palsu!', 'PERFECT SAFETY: Immune to Fake International Parcel & Customs Extortion!'),
                        'summary' => $t(
                            'Luar biasa! Anda tahu bahwa bea masuk resmi hanya dibayar lewat kode Billing negara, tidak panik diancam tuduhan pencucian uang, serta menolak file .apk dan domain .icu.',
                            'Outstanding! You knew customs duties use state Billing codes, stayed calm against money laundering threats, and rejected both the .apk file and fake .icu domain.'
                        ),
                        'steps' => [
                            $t('Gunakan saluran resmi Bravo Bea Cukai 1500225 bila menerima pesan mencurigakan terkait paket impor.', 'Always contact official Bravo Bea Cukai 1500225 if you receive suspicious messages about imported parcels.'),
                        ],
                    ],
                ],
            ],

            // 14. BPJS KESEHATAN .APK & TAGIHAN PALSU (4 GILIRAN - KONTAK BARU)
            [
                'id' => 'bpjs_kesehatan_apk',
                'name' => $t('Layanan BPJS Kesehatan', 'BPJS Health Service'),
                'phone' => '+62 859-3011-7749',
                'initials' => 'BP',
                'avatar_bg' => '#0D9488',
                'category' => $t('File .APK Tagihan BPJS', 'Fake BPJS Health .APK'),
                'difficulty' => $t('Menengah', 'Medium'),
                'preview' => $t(
                    'Kartu kepesertaan JKN-BPJS Kesehatan keluarga Anda akan dinonaktifkan hari ini...',
                    'Your family JKN-BPJS Health insurance card will be deactivated today...'
                ),
                'time' => '21:22',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'PEMBERITAHUAN PENTING: Kepesertaan JKN-BPJS Kesehatan Anda dan keluarga terdeteksi menunggak iuran 3 bulan dan akan DINONAKTIFKAN permanen sore ini.',
                                    'IMPORTANT NOTICE: Your family JKN-BPJS Health membership shows 3 months of unpaid arrears and will be PERMANENTLY DEACTIVATED this evening.'
                                ),
                                'time' => '21:21',
                            ],
                            [
                                'text' => $t(
                                    'Silakan buka rincian tagihan dan formulir pemutihan tunggakan gratis pada berkas aplikasi di bawah ini:',
                                    'Please open your billing statement and free arrears-waiver form in the application file below:'
                                ),
                                'time' => '21:22',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Rincian_Tunggakan_BPJS_JKN.apk',
                                    'size' => '4.4 MB',
                                    'subtitle' => $t('APK · Dokumen JKN', 'APK · JKN Document'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Panik takut kartu kesehatan keluarga nonaktif, langsung buka Rincian_Tunggakan_BPJS_JKN.apk.',
                                    'A. Worried your family health insurance will be deactivated, immediately open Rincian_Tunggakan_BPJS_JKN.apk.'
                                ),
                                'next' => 'end_danger_bpjs',
                                'user_text' => $t(
                                    'Waduh perasaan saya rutin bayar, sebentar saya buka dulu file rinciannya.',
                                    'Wait, I thought my payments were up to date! Let me open the billing file right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak file .apk! Pengecekan status kepesertaan resmi hanya lewat aplikasi Mobile JKN (Play Store) atau PANDAWA 0811-8-165-165.',
                                    'B. Reject the .apk file! Official membership checks are only done via Mobile JKN (Play Store) or PANDAWA 0811-8-165-165.'
                                ),
                                'next' => 'turn2_bpjs_refund',
                                'user_text' => $t(
                                    'BPJS Kesehatan resmi tidak pernah mengirim rincian tagihan dalam bentuk file .apk. Saya hanya mengecek lewat aplikasi resmi Mobile JKN.',
                                    'Official BPJS Health never sends billing statements as .apk files. I only check my status through the official Mobile JKN app.'
                                ),
                            ],
                        ],
                    ],
                    'turn2_bpjs_refund' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Mohon maaf Bapak/Ibu, setelah kami cek ulang ternyata bukan menunggak, melainkan akun JKN Anda kelebihan bayar autodebet sebesar Rp650.000! Untuk pengembalian dana (refund) ke rekening Anda, silakan isi data rekening & PIN m-Banking di formulir ini:',
                                    'Our apologies Sir/Madam! Upon re-checking, you do not have arrears—instead your JKN account has an auto-debit overpayment of IDR 650,000! To receive your refund, enter your bank account & mobile banking PIN on this form:'
                                ),
                                'time' => '21:24',
                                'link' => 'https://refund-iuran-bpjs-jkn.site/cairkan',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Senang malah dapat refund Rp650.000, langsung klik link dan isi kredensial m-Banking.',
                                    'A. Delighted to receive an IDR 650,000 refund instead, click the link and enter your mobile banking credentials.'
                                ),
                                'next' => 'end_danger_bpjs',
                                'user_text' => $t(
                                    'Alhamdulillah ternyata kelebihan bayar! Oke saya isi formulir refund-nya sekarang.',
                                    'Oh awesome, an overpayment refund! Okay let me fill out the refund form right now.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Sadari kejanggalannya: dalam 1 menit berubah dari "menunggak 3 bulan" menjadi "kelebihan bayar Rp650.000" di domain .site!',
                                    'B. Catch the absurd pivot: in 1 minute their story flipped from "3 months of arrears" to an "IDR 650,000 refund" on a .site domain!'
                                ),
                                'next' => 'turn3_bpjs_close',
                                'user_text' => $t(
                                    'Ketahuan bohongnya! Baru semenit lalu bilang saya menunggak 3 bulan, sekarang malah bilang kelebihan bayar Rp650.000 di domain .site palsu!',
                                    'Caught red-handed! A minute ago you claimed I had 3 months of arrears, and now you claim an IDR 650,000 overpayment on a fake .site domain!'
                                ),
                            ],
                        ],
                    ],
                    'turn3_bpjs_close' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Sebutkan saja NIK KTP seluruh anggota keluarga Anda di sini biar saya hapus dari daftar penonaktifan!',
                                    'Just type the National ID (NIK) numbers of all your family members here so I can remove you from the deactivation list!'
                                ),
                                'time' => '21:25',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Tolak menyerahkan NIK keluarga, cek mandiri di Mobile JKN / Care Center 165, lalu blokir!',
                                    'A. Refuse to share family ID numbers, check independently on Mobile JKN / Care Center 165, and block!'
                                ),
                                'next' => 'end_safe_bpjs',
                                'user_text' => $t(
                                    'Saya tidak akan menyerahkan NIK keluarga saya. Layanan resmi BPJS Kesehatan hanya lewat Care Center 165 dan aplikasi Mobile JKN. Blokir!',
                                    'I will not hand over my family ID numbers. Official BPJS Health services only use Care Center 165 and the Mobile JKN app. Blocked!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_bpjs' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('BAHAYA: Malware .APK JKN Palsu / Phishing Refund Iuran!', 'DANGER: Fake JKN .APK Malware / Refund Phishing!'),
                        'summary' => $t(
                            'Ketika gertakan "kartu BPJS dinonaktifkan" tidak mempan, penipu sering berbalik menawarkan "pengembalian kelebihan bayar (refund)" untuk memancing kredensial bank Anda.',
                            'When the "card deactivation" threat fails, scammers often pivot to offering an "overpayment refund" to harvest your banking credentials.'
                        ),
                        'steps' => [
                            $t('Putuskan koneksi internet jika memasang .apk dan hanya gunakan aplikasi resmi Mobile JKN atau Care Center 165.', 'Disconnect from the internet if you installed the .apk, and only use the official Mobile JKN app or Care Center 165.'),
                        ],
                    ],
                    'end_safe_bpjs' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Mengenali Saluran Resmi Mobile JKN & Care Center 165!', 'PERFECT SAFETY: Verified via Official Mobile JKN & Care Center 165!'),
                        'summary' => $t(
                            'Cerdas! Anda menolak file .apk, menertawakan perubahan cerita dari "tunggakan" ke "refund Rp650.000", dan menjaga kerahasiaan NIK keluarga.',
                            'Smart! You rejected the .apk file, saw right through their flip-flop from "arrears" to an "IDR 650,000 refund", and protected your family ID numbers.'
                        ),
                        'steps' => [
                            $t('Simpan nomor resmi BPJS Kesehatan Care Center 165 dan WA PANDAWA (0811-8-165-165) bercentang biru.', 'Save the official BPJS Health Care Center 165 and verified PANDAWA WhatsApp (0811-8-165-165).'),
                        ],
                    ],
                ],
            ],

            // 15. INVESTASI TITIP MODAL KRIPTO VIP (4 GILIRAN - KONTAK BARU)
            [
                'id' => 'investasi_kripto_vip',
                'name' => $t('Mentor Kevin (Titip Modal VIP)', 'Mentor Kevin (Crypto VIP)'),
                'phone' => '+62 821-6640-9910',
                'initials' => 'MK',
                'avatar_bg' => '#D97706',
                'category' => $t('Investasi Bodong Titip Modal', 'Fake Crypto Investment Scam'),
                'difficulty' => $t('Tinggi', 'Hard'),
                'preview' => $t(
                    'Titip modal trading Rp1.000.000 pasti profit Rp15.000.000 dalam 3 jam bergaransi...',
                    'Deposit IDR 1,000,000 trading capital for a guaranteed IDR 15,000,000 profit in 3 hours...'
                ),
                'time' => '22:05',
                'unread' => 1,
                'nodes' => [
                    'start' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Selamat malam! Saya Mentor Kevin dari komunitas Crypto Arbitrage VIP. Hari ini ada kuota sinyal spesial: cukup titip modal Rp1.000.000, dalam 3 jam dijamin cair Rp15.000.000 (Profit 1.500% bergaransi 100% tanpa risiko rugi!).',
                                    'Good evening! I am Mentor Kevin from the Crypto Arbitrage VIP community. Today we have a special slot: invest IDR 1,000,000 and receive a guaranteed IDR 15,000,000 payout in 3 hours (1,500% risk-free profit!).'
                                ),
                                'time' => '22:05',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Tergiur profit Rp15.000.000 dalam 3 jam, langsung transfer modal Rp1.000.000.',
                                    'A. Tempted by IDR 15,000,000 in 3 hours, immediately transfer IDR 1,000,000.'
                                ),
                                'next' => 'turn2_tax_fee',
                                'user_text' => $t(
                                    'Wah mantap banget profitnya! Ini saya transfer modal Rp1.000.000 sekarang ya mentor.',
                                    'Whoa that profit is amazing! I am transferring the IDR 1,000,000 capital right now, Mentor.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Pertanyakan logikanya: tidak ada investasi sah di dunia yang bisa menjamin profit pasti 1.500% dalam 3 jam tanpa risiko!',
                                    'B. Question the logic: no legitimate investment in the world can guarantee a 1,500% risk-free return in 3 hours!'
                                ),
                                'next' => 'turn2_fake_proof',
                                'user_text' => $t(
                                    'Kalau Bapak bisa mengubah Rp1 juta jadi Rp15 juta dalam 3 jam secara pasti, kenapa tidak pakai uang Bapak sendiri saja? Tidak ada investasi legal yang menjanjikan untung 1.500% dalam 3 jam!',
                                    'If you could turn 1 million into 15 million in 3 hours guaranteed, why not just use your own money? No legal investment guarantees 1,500% in 3 hours!'
                                ),
                            ],
                        ],
                    ],
                    'turn2_tax_fee' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'SELAMAT! Trading Anda sukses besar, saldo profit Rp15.000.000 sudah siap ditransfer ke rekening Anda! Namun sesuai aturan bursa, Anda wajib transfer biaya pajak pencairan 20% (Rp3.000.000) di muka sekarang juga agar dana Rp15 juta langsung cair!',
                                    'CONGRATULATIONS! Your trade succeeded and your IDR 15,000,000 profit is ready for payout! However, per exchange rules, you must pay an upfront 20% withdrawal tax (IDR 3,000,000) right now to release the IDR 15M!'
                                ),
                                'time' => '22:08',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Transfer lagi Rp3.000.000 demi mencairkan profit Rp15.000.000.',
                                    'A. Transfer another IDR 3,000,000 to unlock the IDR 15,000,000 profit.'
                                ),
                                'next' => 'end_danger_crypto',
                                'user_text' => $t(
                                    'Oke mentor, ini saya transfer Rp3.000.000 lagi buat biaya pajak pencairannya.',
                                    'Okay Mentor, I am transferring the extra IDR 3,000,000 for the withdrawal tax.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Sadar telah tertipu! Pajak/biaya asli selalu dipotong otomatis dari saldo profit, bukan disuruh transfer uang baru!',
                                    'B. Realize it is a scam! Legitimate fees are deducted from the balance, never paid via a separate upfront transfer!'
                                ),
                                'next' => 'end_danger_crypto',
                                'user_text' => $t(
                                    'Kalau memang ada profit Rp15 juta, potong saja langsung Rp3 juta dari situ! Ini penipuan!',
                                    'If there is really an IDR 15M profit, just deduct the IDR 3M from it! This is a scam!'
                                ),
                            ],
                        ],
                    ],
                    'turn2_fake_proof' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Kami pakai bot AI Arbitrage berlisensi Bappebti & OJK kak! Kalau tidak mau titip modal uang dulu, kakak pasang aplikasi bot trading otomatis kami ini dan hubungkan dompet/m-Banking kakak buat dapat bonus saldo awal Rp500.000 gratis:',
                                    'We use an AI Arbitrage bot licensed by Bappebti & OJK! If you do not want to send capital upfront, install our automated trading bot app below and link your wallet/mobile banking for a free IDR 500,000 starter bonus:'
                                ),
                                'time' => '22:07',
                                'attachment' => [
                                    'type' => 'apk',
                                    'name' => 'Bot_AI_Crypto_VIP_OJK.apk',
                                    'size' => '5.8 MB',
                                    'subtitle' => $t('APK · Bot Trading', 'APK · Trading Bot'),
                                ],
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Pasang Bot_AI_Crypto_VIP_OJK.apk untuk mengambil bonus saldo awal Rp500.000.',
                                    'A. Install Bot_AI_Crypto_VIP_OJK.apk to claim the free IDR 500,000 starter bonus.'
                                ),
                                'next' => 'end_danger_crypto',
                                'user_text' => $t(
                                    'Lumayan dapat modal gratis Rp500.000, saya install aplikasi bot-nya ya.',
                                    'Nice, a free IDR 500,000 starter bonus! Let me install the bot app.'
                                ),
                            ],
                            [
                                'label' => $t(
                                    'B. Tolak file .apk dan catut nama OJK/Bappebti palsu tersebut!',
                                    'B. Reject the .apk file and expose their fake OJK/Bappebti license claim!'
                                ),
                                'next' => 'turn3_crypto_close',
                                'user_text' => $t(
                                    'OJK dan Bappebti sudah melarang keras skema titip modal bergaransi profit pasti dan tidak pernah menyebarkan file .apk lewat WhatsApp!',
                                    'OJK and Bappebti strictly prohibit guaranteed-return capital pooling schemes and never distribute .apk files via WhatsApp!'
                                ),
                            ],
                        ],
                    ],
                    'turn3_crypto_close' => [
                        'messages' => [
                            [
                                'text' => $t(
                                    'Terserah kalau nggak mau kaya cepat! Jangan menyesal lihat member lain beli mobil baru minggu ini!',
                                    'Whatever if you do not want to get rich quick! Do not regret it when you see other members buying new cars this week!'
                                ),
                                'time' => '22:09',
                            ],
                        ],
                        'choices' => [
                            [
                                'label' => $t(
                                    'A. Laporkan nomor ke Satgas PASTI OJK (157) dan blokir penipu investasi bodong ini!',
                                    'A. Report the number to OJK Anti-Scam Task Force (157) and block this investment scammer!'
                                ),
                                'next' => 'end_safe_crypto',
                                'user_text' => $t(
                                    'Prinsip investasi sehat: High Return selalu High Risk, dan yang menjanjikan untung ribuan persen dalam hitungan jam pasti 100% penipuan. Saya laporkan ke OJK 157 dan blokir!',
                                    'Sound investment rule: High Return always carries High Risk, and anyone promising 1,500% in 3 hours is 100% a scammer. Reporting to OJK 157 and blocking!'
                                ),
                            ],
                        ],
                    ],
                ],
                'endings' => [
                    'end_danger_crypto' => [
                        'type' => 'danger',
                        'score' => 0,
                        'title' => $t('TERJEBAK INVESTASI BODONG TITIP MODAL & PAJAK PENCAIRAN PALSU!', 'FELL FOR FAKE CRYPTO INVESTMENT & WITHDRAWAL TAX SCAM!'),
                        'summary' => $t(
                            'Dalam modus Titip Modal, angka "profit Rp15 juta" hanyalah gambar rekayasa agar korban terus mentransfer biaya pajak pencairan, biaya admin, dan biaya verifikasi tanpa henti.',
                            'In capital-pooling scams, the "IDR 15M profit" is a fabricated screenshot designed to trick victims into paying endless upfront withdrawal taxes and admin fees.'
                        ),
                        'steps' => [
                            $t('Hentikan seluruh transfer tambahan; tidak ada dana Rp15 juta yang benar-benar ada.', 'Stop all additional transfers immediately; the IDR 15M profit never existed.'),
                            $t('Laporkan rekening penipu ke cekrekening.id, Satgas PASTI OJK (157), dan IASC (iasc.ojk.go.id).', 'Report the scammer account to cekrekening.id, OJK (157), and the Indonesia Anti-Scam Center (iasc.ojk.go.id).'),
                        ],
                    ],
                    'end_safe_crypto' => [
                        'type' => 'safe',
                        'score' => 100,
                        'title' => $t('AMAN SEMPURNA: Berpikir Kritis dengan Prinsip 2L (Legal & Logis)!', 'PERFECT SAFETY: Applied the 2L Rule (Legal & Logical)!'),
                        'summary' => $t(
                            'Luar biasa! Anda menerapkan prinsip 2L OJK (Legal dan Logis): menolak iming-iming profit 1.500% dalam 3 jam serta menolak memasang aplikasi .apk bot trading palsu.',
                            'Brilliant! You applied the OJK 2L principle (Legal & Logical): rejecting the absurd 1,500% 3-hour return promise and refusing the malicious trading bot .apk.'
                        ),
                        'steps' => [
                            $t('Selalu cek legalitas entitas investasi melalui Kontak OJK 157 sebelum menempatkan dana.', 'Always verify investment entity licenses via OJK Hotline 157 before investing any funds.'),
                        ],
                    ],
                ],
            ],
        ];
    }
}
