PERBAIKAN E-ARSIP - SUPABASE STORAGE
====================================

Masalah utama yang diperbaiki:
1. Project memakai Storage::disk('s3') / Supabase, tetapi config/filesystems.php belum membaca kredensial SUPABASE_*.
2. FILESYSTEM_DISK menunjuk ke 'supabase', tetapi disk 'supabase' belum didefinisikan.
3. Halaman detail/edit masih memiliki beberapa link asset('storage/...') yang mengarah ke storage lokal.
4. Route preview khusus lampiran belum terdaftar, sehingga file Supabase tidak dapat dibuka dari halaman aplikasi.
5. Upload file biasa dan hasil scan kamera sekarang memakai disk Supabase yang sama.
6. File private dibuka memakai temporary URL, bukan URL storage lokal.
7. Ditambahkan fallback untuk arsip lama yang masih tersimpan di storage lokal.

SETELAH MENGEKSTRAK PROJECT
---------------------------
1. Pastikan PHP, Composer, MySQL dan Node.js sudah terpasang.
2. Pastikan file .env berisi:
   FILESYSTEM_DISK=supabase
   SUPABASE_S3_ACCESS_KEY_ID=...
   SUPABASE_S3_SECRET_ACCESS_KEY=...
   SUPABASE_S3_REGION=...
   SUPABASE_STORAGE_BUCKET=arsip-surat
   AWS_USE_PATH_STYLE_ENDPOINT=true
   SUPABASE_S3_ENDPOINT=https://PROJECT_REF.storage.supabase.co/storage/v1/s3

3. Di Supabase:
   - Storage > S3 Configuration: pastikan S3 protocol aktif.
   - Pastikan bucket 'arsip-surat' sudah ada.
   - Gunakan S3 Access Key dan Secret Key dari konfigurasi S3 Supabase.

4. Di terminal project:
   composer install
   npm install
   php artisan optimize:clear
   php artisan migrate
   npm run build

5. Jalankan:
   php artisan serve

6. Uji dua fitur:
   - Surat Masuk > tambah > Upload File
   - Surat Masuk > tambah > Scan Kamera HP > Ambil Foto / Scan
   - Ulangi untuk Surat Keluar.

CATATAN KEAMANAN
----------------
File .env pada project asli berisi kredensial S3 Supabase. Karena kredensial tersebut sudah pernah dibagikan dalam arsip project, sangat disarankan membuat/rotate Access Key dan Secret Key baru di Supabase, kemudian mengganti nilainya di .env.

VALIDASI YANG SUDAH DILAKUKAN
-----------------------------
- PHP syntax check untuk app, config, routes, dan migrations: lulus.
- Route preview surat masuk dan surat keluar sudah terdaftar.
- Konfigurasi disk 'supabase' dan alias 's3' sudah tersedia.

Catatan: pengujian koneksi langsung ke Supabase dari lingkungan pemeriksaan ini tidak dapat dilakukan karena koneksi jaringan keluar ditolak. Jadi setelah project dijalankan di laptop/server Anda, lakukan upload satu file kecil untuk verifikasi akhir.
