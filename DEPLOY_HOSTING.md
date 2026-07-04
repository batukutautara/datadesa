# Panduan Hosting Aplikasi

Proyek ini adalah aplikasi PHP + MySQL. GitHub hanya menyimpan source code, sedangkan aplikasi live perlu hosting yang mendukung PHP dan MySQL.

## Opsi Hosting

Gunakan salah satu:
- Shared hosting/cPanel berbayar: paling stabil.
- InfinityFree atau hosting gratis PHP/MySQL sejenis: cukup untuk demo, tetapi biasanya ada batasan.

## File Yang Perlu Diupload

Upload semua isi folder proyek ke folder public hosting:
- cPanel biasanya: `public_html`
- InfinityFree biasanya: `htdocs`

Jangan upload folder `.git`.

File `.env` wajib dibuat di hosting dengan isi seperti `.env.example`.

Contoh:

```env
DB_HOST=sqlXXX.infinityfree.com
DB_USER=if0_XXXXXXX
DB_PASS=password_database
DB_NAME=if0_XXXXXXX_datadesa
```

## Import Database

1. Buka phpMyAdmin di hosting.
2. Buat database baru jika belum ada.
3. Import file `sql/database.sql`.
4. Pastikan nama database, user, password, dan host sama dengan isi `.env`.

## Login Awal

Admin:

```text
Username: admin
Password: admin123
```

User keluarga:

```text
Username: nomor KK
Password: kk + nomor KK
```

Contoh:

```text
Username: 5201030603081917
Password: kk5201030603081917
```

## Catatan Keamanan

`sql/database.sql` berisi data penduduk. Jangan jadikan repository GitHub public jika data asli tetap berada di file SQL.

Setelah berhasil online, segera ganti password admin dari database atau tambahkan fitur ubah password.
