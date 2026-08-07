# Helvetica POS — Kasir MVP

## Cara pasang di project Laravel 11 baru

```bash
composer create-project laravel/laravel helvetica-pos
cd helvetica-pos
```

1. **Users migration**: hapus/skip file default
   `database/migrations/0001_01_01_000000_create_users_table.php` bagian
   `create('users', ...)` — proyek ini menggantinya dengan migration
   PIN-based. Boleh tetap simpan tabel `password_reset_tokens` dan
   `sessions` dari file itu kalau masih dipakai.

2. Copy folder ini ke project:
   - `database/migrations/*` → `database/migrations/`
   - `database/seeders/DatabaseSeeder.php` → timpa yang lama
   - `app/Models/*` → `app/Models/`
   - `app/Http/Controllers/*` → `app/Http/Controllers/`
   - `app/Http/Middleware/*` → `app/Http/Middleware/`
   - `app/Http/Requests/*` → `app/Http/Requests/`
   - `resources/views/pos/*` → `resources/views/pos/`
   - `routes/web.php` → timpa yang lama
   - Merge isi `bootstrap/app.php` (alias middleware `shift.active`) ke
     `bootstrap/app.php` project kamu — jangan timpa seluruh file.

3. Set `.env` ke database MySQL kamu, lalu:

```bash
php artisan migrate:fresh --seed
php artisan serve
```

4. Buka `http://localhost:8000/login`, pilih **Kasir Satu**, PIN `112233`.

## Kredensial dummy

| Nama | Role | PIN |
|---|---|---|
| Admin Helvetica | admin | 123456 |
| Kasir Satu | cashier | 112233 |

## Alur logika penting

- **Shift wajib buka dulu** — middleware `EnsureShiftIsOpen` menolak
  `POST /orders` (403) kalau user belum punya shift `status=open`.
  Ini dicek di server, bukan cuma disembunyikan di UI.
- **Harga selalu diambil ulang dari DB** di `OrderController::store()`,
  bukan dari payload frontend — jadi request yang dimodifikasi tidak
  bisa mengubah harga.
- **Perhitungan**: `tax = subtotal * 10%`, `total = subtotal + tax`,
  `pg_fee = QRIS ? total * 0.7% : 0`, `net_received = total - pg_fee`.
- **Nomor invoice** dibuat dari `id` auto-increment row yang baru saja
  di-insert (`HLV-YYYYMMDD-000{id}`), bukan dari `COUNT(*)` — supaya
  tidak race condition kalau ada dua kasir checkout bersamaan.
- **Cash reconciliation**: `Shift::expected_cash` = modal awal + semua
  penjualan CASH yang `status=paid` selama shift itu (QRIS tidak masuk
  laci fisik). `variance` = uang fisik dihitung − expected_cash.

## Catatan produksi (di luar scope MVP ini)

- Ganti Tailwind/Alpine CDN dengan Vite build untuk production.
- Tambahkan rate limiting di route login PIN.
- Pertimbangkan Laravel Sanctum kalau POS UI dipisah jadi SPA/mobile app.
- `is_available` di skema saat ini boolean sederhana — belum ada
  pengurangan stok otomatis (itu bagian dari modul Inventory/Resep
  yang lebih besar, di luar MVP kasir ini).
