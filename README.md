# Kasir Warkop Djoeragan

Aplikasi point-of-sale untuk transaksi, produk, stok, laporan, dan cetak struk dengan dukungan PWA.

## Teknologi

PHP 8.1+, MySQL / MariaDB, JavaScript, PWA

## Menjalankan proyek

1. Jalankan Apache dan MySQL. Impor `database/kasir_warkop.sql` ke database pengembangan baru. Skema ini menghapus dan membuat tabel; jangan jalankan pada database operasional.
2. Salin `config/production.example.php` menjadi `config/production.php`, lalu isi konfigurasi database serta `base_url`. Pastikan port MySQL sesuai (umumnya 3306).
3. Atur INITIAL_OWNER_PASSWORD minimal 12 karakter pada environment, lalu jalankan `php scripts/create-owner.php`. Opsional: INITIAL_OWNER_USERNAME dan INITIAL_OWNER_NAME. Tidak ada akun default.
4. Buka aplikasi, masuk sebagai owner, lalu kelola akun kasir dan produk.
5. Folder `uploads/products` harus dapat ditulis PHP.
6. Sesuaikan path di `manifest.json` dan `service-worker.js` dengan lokasi deployment. PWA memerlukan HTTPS atau localhost.

## Catatan proyek

Transaksi dan stok memakai database transaction. Migrasi tambahan tersedia pada direktori `database`.

## Isi repository

Repository berisi kode sumber dan aset presentasi. Konfigurasi produksi, database operasional, backup, data pelanggan, session, dan upload privat tidak disertakan.
