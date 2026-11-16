# Dokumentasi REST API CargoTrace & Panduan Postman

Dokumen ini merupakan panduan pengujian dan integrasi endpoint REST API pada platform **CargoTrace** (Global Shipment Visibility & Tracking Platform) sesuai standar spesifikasi `PROJECT_SPEC.md` (Milestone P8).

---

## 1. Berkas Postman yang Disediakan

Folder ini memuat artefak pengujian API format standar Postman v2.1:
1. **Collection:** [`CargoTrace_Auth_API.postman_collection.json`](./CargoTrace_Auth_API.postman_collection.json)  
   Berisi endpoint autentikasi lengkap dengan script otomatisasi penyimpanan token (*auto-save token test script*).
2. **Environment:** [`CargoTrace_Local.postman_environment.json`](./CargoTrace_Local.postman_environment.json)  
   Menyimpan variabel `base_url` (`http://127.0.0.1:8000`) dan `auth_token`.

---

## 2. Panduan Import ke Aplikasi Postman

1. Buka aplikasi **Postman** (Desktop atau Web).
2. Klik tombol **Import** pada pojok kiri atas *workspace*.
3. Pilih file atau *drag & drop* kedua berkas berikut:
   - `docs/api/CargoTrace_Auth_API.postman_collection.json`
   - `docs/api/CargoTrace_Local.postman_environment.json`
4. Pada bagian kanan atas Postman, ganti pilihan *Environment* aktif menjadi:  
   **"CargoTrace Local Environment"**.
5. Pastikan server lokal aplikasi berjalan:
   ```bash
   php artisan serve
   ```
6. Jalankan salah satu request Login atau Register. Token Sanctum akan otomatis tersimpan pada variabel environment `auth_token` untuk request berikutnya (`GET /api/user` dan `POST /api/logout`).

---

## 3. Daftar Akun Demo Pengujian API

Semua akun pengujian berikut terdaftar secara bawaan di database lokal melalui seeder (`php artisan db:seed`):

| Peran (*Role*) | Nama Pengguna | Alamat Email | Kata Sandi (*Password*) | Status Akun | Hak Akses Utama |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Administrator** | System Administrator | `admin@cargotrace.com` | `password` | `active` | Master konfigurasi, user, carrier, audit log |
| **Logistics Operator** | Logistics Operations Officer | `operator@cargotrace.com` | `password` | `active` | Kelola shipment, tracking events, exceptions |
| **Customer** | Global Importer Client | `customer@cargotrace.com` | `password` | `active` | Pelacakan mandiri kargo & kontainer miliknya |

*Catatan:* Akun baru yang mendaftar via `POST /api/register` akan otomatis memiliki role `customer` dan status `active`.

---

## 4. Daftar Endpoint Autentikasi (Milestone P8)

| Method | Endpoint | Proteksi (*Auth*) | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | Publik | Pendaftaran akun customer mandiri dan penerbitan token Bearer |
| `POST` | `/api/login` | Publik | Autentikasi kredensial (admin, operator, customer) & penerbitan token |
| `GET` | `/api/user` | `Bearer Token` | Mengambil data profil dan role pengguna yang sedang login |
| `POST` | `/api/logout` | `Bearer Token` | Mencabut token akses aktif dari database (*revoke token*) |

---

## 5. Standar Format Respon JSON

Sesuai spesifikasi kontrak `PROJECT_SPEC.md` Bagian 3.7, seluruh pertukaran data menggunakan format JSON konsisten:

### 5.1 Sukses (HTTP 200 OK & HTTP 201 Created)

**Registrasi Akun Baru (HTTP 201):**
```json
{
  "success": true,
  "message": "Registrasi akun berhasil.",
  "data": {
    "user": {
      "id": 4,
      "name": "PT Samudera Pratama",
      "email": "samudera@example.test",
      "role": "customer"
    },
    "token": "1|qXyZ...",
    "token_type": "Bearer"
  }
}
```

**Login Sukses (HTTP 200):**
```json
{
  "success": true,
  "message": "Login API berhasil.",
  "data": {
    "user": {
      "id": 1,
      "name": "System Administrator",
      "email": "admin@cargotrace.com",
      "role": "admin"
    },
    "token": "2|AbCd...",
    "token_type": "Bearer"
  }
}
```

**Cek Profil Pengguna (HTTP 200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "System Administrator",
    "email": "admin@cargotrace.com",
    "role": "admin",
    "status": "active"
  }
}
```

---

### 5.2 Kesalahan Kredensial / Tidak Terotentikasi (HTTP 401 Unauthorized)

**Kredensial Tidak Cocok:**
```json
{
  "success": false,
  "message": "Kredensial email atau password salah."
}
```

**Akun Dinonaktifkan / Suspended:**
```json
{
  "success": false,
  "message": "Akun Anda dinonaktifkan."
}
```

**Akses Endpoint Terproteksi Tanpa Bearer Token:**
```json
{
  "message": "Unauthenticated."
}
```

---

### 5.3 Akses Dilarang / Otorisasi Role Gagal (HTTP 403 Forbidden)

```json
{
  "message": "Akses Ditolak: Anda tidak memiliki izin untuk mengakses area ini."
}
```

---

### 5.4 Kesalahan Validasi Input (HTTP 422 Unprocessable Entity)

```json
{
  "message": "Alamat email wajib diisi. (and 2 more errors)",
  "errors": {
    "name": [
      "Nama lengkap wajib diisi."
    ],
    "email": [
      "Alamat email wajib diisi."
    ],
    "password": [
      "Kata sandi wajib diisi."
    ]
  }
}
```
