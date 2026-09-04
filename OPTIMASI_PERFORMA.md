# Optimasi Performa E-Arsip

Perubahan ini mempertahankan tampilan dan fitur utama, tetapi mengurangi beban awal browser dan query dashboard.

## Perubahan utama
- Menghapus Tailwind CSS CDN dan memakai hasil build lokal melalui Vite.
- Menghapus Alpine.js CDN; fungsi sidebar diganti JavaScript native yang jauh lebih kecil.
- Menghapus jsPDF global karena tidak dipakai oleh halaman saat ini.
- Chart.js hanya dimuat di halaman Dashboard.
- Notifikasi/konfirmasi global tidak lagi bergantung pada SweetAlert2.
- Dashboard: query grafik 12 bulan diubah dari banyak COUNT terpisah menjadi 2 query agregasi.
- Menambahkan index database untuk kolom yang sering dipakai dashboard/filter.
- Cache view lama dan folder development tidak disertakan dalam paket deploy.

## Setelah upload/deploy
Jalankan jika hosting menyediakan terminal:
```bash
php artisan migrate --force
php artisan optimize
```

Jika menjalankan build frontend dari source:
```bash
npm install
npm run build
```

Paket ini sengaja tidak menyertakan `.env`, `.git`, `node_modules`, cache view lama, dan cache bootstrap agar lebih aman dan ringan untuk distribusi.
