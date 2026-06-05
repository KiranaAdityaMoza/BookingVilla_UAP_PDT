# 🏡 Villaku - Sistem Manajemen & Reservasi Villa Terintegrasi (Proyek UAP)

Sistem Informasi Manajemen dan Reservasi Unit Properti **Villaku** adalah aplikasi berbasis **PHP Native (PDO)** dan **MySQL** yang dirancang untuk mengotomatisasi operasional penyewaan unit villa. Aplikasi ini mendukung pengelolaan multi-role (Admin dan Customer), kalkulasi diskon otomatis via basis data, audit log pembatalan transaksi, hingga manajemen pemeliharaan (*database backup maintenance*).

---

## 🚀 Fitur Utama Sistem

### 👑 Sisi Admin (Back-Office)
*   **Manajemen Unit Properti (CRUD):** Pengelolaan penuh data villa menggunakan *Stored Procedure*.
*   **Executive Dashboard Financial Summary:** Penayangan laporan gabungan keuangan lintas wilayah berbasis *Database View*.
*   **Live Reservation Monitor & Audit Trail:** Pemantauan status pesanan real-time sekaligus pencatatan log otomatis finansial hangus akibat pembatalan sepihak via *Database Trigger*.
*   **Database Maintenance Kit:** Fasilitas ekspor database instan murni aman dari kendala *Form Resubmission* (PRG Pattern).

### 👤 Sisi Customer (Front-End)
*   **Smart Catalog Filter:** Menampilkan unit villa yang siap disewa secara adaptif berdasarkan wilayah klaster operasional.
*   **Automated Discounting Engine:** Pemotongan harga sewa otomatis langsung dari sistem database saat menyentuh nilai transaksi tertentu.
*   **Personal Reservation & Billing History:** Rekam jejak seluruh pesanan dan status nota pembayaran personal.

---

## 📌 Penerapan Konsep Advanced Database (UAP Checklist)

### 1. Stored Procedure: Enkapsulasi Logika CRUD
Seluruh operasi manipulasi data pada tabel `vila` diisolasi di dalam prosedur `sp_manage_vila` untuk menjaga keamanan dan konsistensi data dari manipulasi query luar.

*   **Nama Prosedur:** `sp_manage_vila(action_type, p_id, p_nama, p_alamat, p_klaster, p_harga, p_status)`
*   **Implementasi Kode PHP (PDO):**
```php
// Contoh pemanggilan Stored Procedure untuk menambahkan unit baru
$stmt = $pdo->prepare("CALL sp_manage_vila('INSERT', :id, :nama, :alamat, :klaster, :harga, :status)");
$stmt->execute([
    'id'      => 'V13',
    'nama'    => 'Villa Sunset Paradise',
    'alamat'  => 'Jl. Pesisir Barat No. 9, Lampung',
    'klaster' => 'Pantai',
    'harga'   => 1250000,
    'status'  => 'Tersedia'
]);
