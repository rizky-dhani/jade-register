<?php

return [
    // Navigation Groups
    'navigation' => [
        'data' => 'Data',
        'settings' => 'Pengaturan',
        'events' => 'Acara',
    ],

    // Resources - Seminar Registrations
    'seminar_registration' => [
        'label' => 'Pendaftaran Seminar',
        'plural_label' => 'Pendaftaran Seminar',
        'form' => [
            'email_plataran' => 'Email sesuai Plataran Sehat',
            'name_str' => 'Nama sesuai STR (tanpa gelar)',
            'name_plataran' => 'Nama sesuai Plataran Sehat',
            'nik' => 'NIK',
            'pdgi_branch' => 'PDGI Cabang',
            'competency' => 'Kompetensi',
            'whatsapp_number' => 'Nomor WhatsApp',
            'payment_proof_helper' => 'Unggah bukti pembayaran dalam format JPG, PNG, atau PDF (maks. 5MB).',
        ],
        'table' => [
            'registration_code' => 'Kode Pendaftaran',
            'name' => 'Nama',
            'email' => 'Email',
            'phone' => 'Telepon',
            'selected_seminar' => 'Seminar Dipilih',
            'payment_status' => 'Status Pembayaran',
            'created_at' => 'Dibuat Pada',
        ],
    ],

    // Resources - Users
    'user' => [
        'label' => 'Pengguna',
        'plural_label' => 'Pengguna',
        'form' => [
            'section_user_info' => 'Informasi Pengguna',
            'name' => 'Nama',
            'name_license' => 'Nama Lisensi',
            'nik' => 'NIK',
            'pdgi_branch' => 'PDGI Cabang',
            'kompetensi' => 'Kompetensi',
            'email' => 'Alamat Email',
            'section_roles' => 'Peran',
            'roles_placeholder' => 'Pilih peran...',
        ],
        'table' => [
            'name' => 'Nama',
            'email' => 'Email',
            'roles' => 'Peran',
            'created_at' => 'Dibuat Pada',
        ],
    ],

    // Resources - Seminars
    'seminar' => [
        'label' => 'Seminar',
        'plural_label' => 'Seminar',
        'form' => [
            'section_package_info' => 'Informasi Paket',
            'name' => 'Nama Paket',
            'name_placeholder' => 'contoh: Early Bird - Snack + Lunch',
            'code' => 'Kode',
            'code_placeholder' => 'contoh: local_early_bird_lunch',
            'code_helper' => 'Otomatis dibuat dari nama paket saat membuat. Dapat diedit di halaman edit.',
            'description' => 'Deskripsi',
            'description_placeholder' => 'Deskripsi opsional tentang isi paket ini',
            'section_pricing' => 'Harga & Tipe',
            'applies_to' => 'Berlaku Untuk',
            'applies_to_local' => 'Lokal (Indonesia)',
            'applies_to_international' => 'Internasional',
            'applies_to_all' => 'Semua Peserta',
            'applies_to_helper' => 'Pilih tipe peserta yang dapat menggunakan paket ini',
            'original_price' => 'Harga Normal',
            'original_price_placeholder' => 'contoh: 1000000',
            'original_price_helper' => 'Harga reguler sebelum diskon',
            'discounted_price' => 'Harga Diskon (Early Bird)',
            'discounted_price_placeholder' => 'contoh: 900000',
            'discounted_price_helper' => 'Harga promo early bird (kosongkan jika tidak ada diskon)',
            'max_seats' => 'Maksimal Kursi',
            'max_seats_placeholder' => 'contoh: 100',
            'max_seats_helper' => 'Jumlah maksimal pendaftaran yang diizinkan (kosongkan untuk tidak terbatas)',
            'early_bird_deadline' => 'Batas Waktu Early Bird',
            'early_bird_deadline_helper' => 'Batas waktu untuk harga early bird (kosongkan untuk hanya menggunakan toggle)',
            'currency' => 'Mata Uang',
            'currency_idr' => 'IDR - Rupiah Indonesia',
            'currency_usd' => 'USD - Dolar Amerika',
            'section_features' => 'Fitur Paket',
            'includes_lunch' => 'Termasuk Makan Siang',
            'includes_lunch_helper' => 'Centang jika paket ini termasuk makan siang (bukan hanya snack)',
            'is_early_bird' => 'Harga Early Bird',
            'is_early_bird_helper' => 'Centang jika ini adalah harga promo early bird',
            'is_active' => 'Aktif',
            'is_active_helper' => 'Paket tidak aktif tidak akan ditampilkan kepada pengguna',
            'section_display_order' => 'Urutan Tampilan',
            'sort_order' => 'Urutan',
            'sort_order_helper' => 'Angka lebih kecil muncul lebih dulu. Gunakan ini untuk mengatur urutan tampilan.',
        ],
        'table' => [
            'name' => 'Nama',
            'code' => 'Kode',
            'applies_to' => 'Berlaku Untuk',
            'price' => 'Harga',
            'is_active' => 'Aktif',
            'sort_order' => 'Urutan',
        ],
    ],

    // Resources - Hands Ons
    'hands_on' => [
        'label' => 'Hands On',
        'plural_label' => 'Hands On',
    ],

    // Resources - Hands On Registrations
    'hands_on_registration' => [
        'label' => 'Pendaftaran Hands On',
        'plural_label' => 'Pendaftaran Hands On',
    ],

    // Resources - Visitors
    'visitor' => [
        'label' => 'Pengunjung',
        'plural_label' => 'Pengunjung',
        'form' => [
            'name' => 'Nama',
            'email' => 'Email',
            'phone' => 'Telepon',
            'affiliation' => 'Afiliasi',
            'section_attendance' => 'Status Kehadiran',
            'section_attendance_description' => 'Informasi check-in pengunjung',
            'is_scanned' => 'Sudah Check-in',
            'scanned_at' => 'Waktu Check-in',
            'not_scanned' => 'Belum check-in',
        ],
        'table' => [
            'name' => 'Nama',
            'email' => 'Email',
            'phone' => 'Telepon',
            'affiliation' => 'Afiliasi',
            'checked_in' => 'Check-in',
        ],
    ],

    // Resources - Poster Submissions
    'poster_submission' => [
        'label' => 'Pengumpulan Poster',
        'plural_label' => 'Pengumpulan Poster',
    ],

    // Resources - Poster Evaluations
    'poster_evaluation' => [
        'label' => 'Evaluasi Poster',
        'plural_label' => 'Evaluasi Poster',
    ],

    // Resources - Countries
    'country' => [
        'label' => 'Negara',
        'plural_label' => 'Negara',
        'form' => [
            'name' => 'Nama Negara',
            'code' => 'Kode Negara',
            'is_indonesia' => 'Indonesia',
        ],
        'table' => [
            'name' => 'Nama',
            'code' => 'Kode',
            'is_indonesia' => 'Indonesia',
        ],
    ],

    // Resources - Roles
    'role' => [
        'label' => 'Peran',
        'plural_label' => 'Peran',
        'form' => [
            'name' => 'Nama Peran',
            'permissions' => 'Izin',
        ],
        'table' => [
            'name' => 'Nama',
            'permissions_count' => 'Jumlah Izin',
        ],
    ],

    // Resources - Permissions
    'permission' => [
        'label' => 'Izin',
        'plural_label' => 'Izin',
        'form' => [
            'name' => 'Nama Izin',
        ],
        'table' => [
            'name' => 'Nama',
        ],
    ],

    // Notifications
    'notifications' => [
        'created_title' => 'Data berhasil dibuat',
        'created_body' => 'Data telah berhasil disimpan.',
        'updated_title' => 'Data berhasil diperbarui',
        'updated_body' => 'Perubahan telah berhasil disimpan.',
        'deleted_title' => 'Data berhasil dihapus',
        'deleted_body' => 'Data telah berhasil dihapus.',
    ],

    // Common Actions
    'actions' => [
        'create' => 'Buat',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Lihat',
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'back' => 'Kembali',
        'confirm' => 'Konfirmasi',
    ],

    // Common Fields
    'fields' => [
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'id' => 'ID',
    ],
];
