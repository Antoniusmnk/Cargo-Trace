# Dokumentasi REST API CargoTrace & Panduan Postman

Dokumen ini merupakan panduan pengujian dan integrasi endpoint REST API pada platform **CargoTrace** (Global Shipment Visibility & Tracking Platform) sesuai standar spesifikasi `PROJECT_SPEC.md` (Milestone P8 & P9).

---

## 1. Berkas Postman yang Disediakan

Folder `docs/api/` memuat artefak pengujian API format standar Postman v2.1:
1. **Full Platform Collection:** [`CargoTrace_API.postman_collection.json`](./CargoTrace_API.postman_collection.json)  
   Memuat seluruh modul: Autentikasi Sanctum, Master Data Carriers & Locations, serta Shipment Management CRUD lengkap dengan manifes item.
2. **Auth Baseline Collection:** [`CargoTrace_Auth_API.postman_collection.json`](./CargoTrace_Auth_API.postman_collection.json)  
   Koleksi khusus endpoint autentikasi Milestone P8.
3. **Environment:** [`CargoTrace_Local.postman_environment.json`](./CargoTrace_Local.postman_environment.json)  
   Menyimpan variabel `base_url` (`http://127.0.0.1:8000`), `auth_token`, dan `shipment_id`.

---

## 2. Panduan Import ke Aplikasi Postman

1. Buka aplikasi **Postman** (Desktop atau Web).
2. Klik tombol **Import** pada pojok kiri atas *workspace*.
3. Pilih file atau *drag & drop* kedua berkas berikut:
   - `docs/api/CargoTrace_API.postman_collection.json`
   - `docs/api/CargoTrace_Local.postman_environment.json`
4. Pada bagian kanan atas Postman, ganti pilihan *Environment* aktif menjadi:  
   **"CargoTrace Local Environment"**.
5. Pastikan server lokal aplikasi berjalan:
   ```bash
   php artisan serve
   ```
6. Jalankan salah satu request Login (`Login Admin`, `Login Operator`, atau `Login Customer`). Token Sanctum akan otomatis tersimpan pada variabel environment `auth_token` untuk seluruh request berikutnya.

---

## 3. Daftar Akun Demo Pengujian API

Semua akun pengujian berikut terdaftar secara bawaan di database lokal melalui seeder (`php artisan db:seed`):

| Peran (*Role*) | Nama Pengguna | Alamat Email | Kata Sandi (*Password*) | Status Akun | Hak Akses Utama |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Administrator** | System Administrator | `admin@cargotrace.com` | `password` | `active` | Master konfigurasi, user, carrier, lokasi, audit log |
| **Logistics Operator** | Logistics Operations Officer | `operator@cargotrace.com` | `password` | `active` | Kelola shipment, manifes item, tracking events |
| **Customer** | Global Importer Client | `customer@cargotrace.com` | `password` | `active` | Pelacakan mandiri kargo & kontainer miliknya |

*Catatan:* Akun baru yang mendaftar via `POST /api/register` akan otomatis memiliki role `customer` dan status `active`.

---

## 4. Matriks Hak Akses & Endpoint API

### 4.1 Modul Autentikasi (Milestone P8)

| Method | Endpoint | Proteksi (*Auth*) | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | Publik | Pendaftaran akun customer mandiri dan penerbitan token Bearer |
| `POST` | `/api/login` | Publik | Autentikasi kredensial (admin, operator, customer) & penerbitan token |
| `GET` | `/api/user` | `Bearer Token` | Mengambil data profil dan role pengguna yang sedang login |
| `POST` | `/api/logout` | `Bearer Token` | Mencabut token akses aktif dari database (*revoke token*) |

### 4.2 Modul Master Data (Milestone P9.1)

| Method | Endpoint | Proteksi (*Auth*) | Izin Akses (*Policy*) | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/carriers` | `Bearer Token` | Seluruh Role Terotentikasi | Daftar operator pelayaran aktif (Maersk, MSC, CMA CGM) |
| `POST` | `/api/carriers` | `Bearer Token` | `admin`, `operator` | Registrasi carrier pelayaran baru |
| `GET` | `/api/locations` | `Bearer Token` | Seluruh Role Terotentikasi | Daftar pelabuhan, bandara, & hub logistik |
| `POST` | `/api/locations` | `Bearer Token` | `admin`, `operator` | Registrasi titik lokasi & koordinat GPS baru |

### 4.3 Modul Shipments & Cargo Tracking (Milestone P9.4)

| Method | Endpoint | Proteksi (*Auth*) | Izin Akses (*Policy*) | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/shipments` | `Bearer Token` | Seluruh Role (Tersaring) | Paginated list shipments. Customer hanya melihat kargo miliknya |
| `POST` | `/api/shipments` | `Bearer Token` | `admin`, `operator` | Membuat kargo baru beserta item manifes secara atomik |
| `GET` | `/api/shipments/{id}` | `Bearer Token` | Owner, `operator`, `admin` | Detail pengiriman, manifes item, carrier, dan lokasi |
| `PUT/PATCH` | `/api/shipments/{id}` | `Bearer Token` | `admin`, `operator` | Memperbarui kargo aktif (dilarang jika `DELIVERED`) |
| `DELETE` | `/api/shipments/{id}` | `Bearer Token` | `admin` (semua), `operator` (`CREATED`/`BOOKED` saja) | Menghapus pengiriman dan item manifes |

#### Parameter Filter Query pada `GET /api/shipments`:
- `status`: Filter berdasarkan status (`CREATED`, `BOOKED`, `IN_TRANSIT`, `CUSTOMS_HOLD`, `DELIVERED`, `CANCELLED`).
- `carrier_id`: Filter ID operator pelayaran.
- `search`: Pencarian resi kargo (*shipment_number*).
- `per_page`: Jumlah data per halaman (default: `10`).
- `page`: Nomor halaman aktif (default: `1`).

### 4.4 Modul Tracking Events & State Machine (Milestone P10)

| Method | Endpoint | Proteksi (*Auth*) | Izin Akses (*Policy*) | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/shipments/{shipment}/tracking` | `Bearer Token` | Owner, `operator`, `admin` | Mengambil seluruh linimasa riwayat status dan checkpoint kargo |
| `POST` | `/api/shipments/{shipment}/tracking` | `Bearer Token` | `operator`, `admin` (Customer ditolak) | Mencatat checkpoint baru & memperbarui status shipment sesuai State Machine |

### 4.5 Aturan State Machine Siklus Hidup Kargo (Linear Lifecycle)

Platform CargoTrace menerapkan mesin status (*State Machine*) linier yang ketat untuk menjamin integritas data audit jejak pengiriman:

```
[ CREATED ] ──> [ BOOKED ] ──> [ PICKED_UP ] ──> [ IN_TRANSIT ] ──> [ DELIVERED ] (Terminal)
                                                         │   ▲
                                                         └───┘
                                                (Checkpoint Pembaruan Waypoint)
```

1. **Urutan Wajib Linier:**
   - `CREATED`: Manifes pengiriman kargo baru terdaftar di sistem.
   - `BOOKED`: Slot kapal atau ruang muat telah dikonfirmasi oleh pihak pelayaran/carrier.
   - `PICKED_UP`: Kontainer/kargo fisik telah dijemput dan disegel dari depo pengirim.
   - `IN_TRANSIT`: Kapal pengangkut telah bertolak dari pelabuhan asal dan sedang berlayar.
   - `DELIVERED`: Kargo tiba di pelabuhan tujuan, dibongkar, dan diserahterimakan kepada consignee.
2. **Pengecualian Checkpoint Berulang (`IN_TRANSIT` &rarr; `IN_TRANSIT`):**
   - Saat kargo berada dalam tahap `IN_TRANSIT`, operator diizinkan mencatat checkpoint koordinat maritim berulang kali (misal: titik lapor Selat Malaka, Terusan Suez, dll) tanpa mengubah status kargo.
3. **Status Terminal Permanen (`DELIVERED`):**
   - Status `DELIVERED` bersifat final. State machine menolak (*throws `InvalidStatusTransitionException`* &rarr; `HTTP 422`) setiap upaya memutar balik status (`DELIVERED` &rarr; `CREATED`), memperbarui waypoint, atau transisi apa pun.
4. **Skenario Pengujian Sekuensial Postman:**
   - Folder **"04. Tracking & Lifecycle"** pada koleksi Postman menyediakan 5 request sekuensial yang dapat dijalankan secara bertahap untuk memverifikasi transisi penuh dari `BOOKED` hingga penolakan `DELIVERED`.

---

## 5. Standar Format Respon JSON

Sesuai spesifikasi kontrak `PROJECT_SPEC.md` Bagian 3.7, seluruh pertukaran data menggunakan format JSON standar:

### 5.1 Sukses (HTTP 200 OK & HTTP 201 Created)

**Daftar Shipments Paginated (`GET /api/shipments` - HTTP 200):**
```json
{
  "success": true,
  "message": "Daftar pengiriman kargo berhasil diambil.",
  "data": [
    {
      "id": 1,
      "shipment_number": "CT-202611-0001",
      "customer_id": 3,
      "carrier_id": 1,
      "origin_id": 1,
      "destination_id": 3,
      "cargo_type": "Telecommunication & Electronics",
      "total_weight_kg": 12500.00,
      "total_volume_cbm": 45.50,
      "expected_departure": "2026-11-17 22:30:00",
      "expected_arrival": "2026-11-20 22:30:00",
      "estimated_arrival": "2026-11-20 22:30:00",
      "current_status": "CREATED",
      "is_delayed": false,
      "delay_hours": 0,
      "delay_severity": "NONE",
      "notes": "High-priority networking hardware shipment to regional distribution hub."
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 3
  },
  "links": {
    "first": "http://127.0.0.1:8000/api/shipments?page=1",
    "last": "http://127.0.0.1:8000/api/shipments?page=1",
    "prev": null,
    "next": null
  }
}
```

**Pembuatan Shipment Baru dengan Manifes (`POST /api/shipments` - HTTP 201):**
```json
{
  "success": true,
  "message": "Pengiriman kargo berhasil dibuat.",
  "data": {
    "id": 4,
    "shipment_number": "CT-202611-4821",
    "customer_id": 3,
    "carrier_id": 1,
    "origin_id": 1,
    "destination_id": 3,
    "cargo_type": "Pharmaceutical Vaccines",
    "total_weight_kg": 1050.50,
    "total_volume_cbm": 12.50,
    "current_status": "CREATED",
    "items": [
      {
        "id": 5,
        "shipment_id": 4,
        "item_name": "Vaccine Vials Box A",
        "quantity": 100,
        "weight_kg": 450.50,
        "volume_cbm": 5.20,
        "package_type": "Cold Box",
        "hs_code": "3002.20"
      }
    ]
  }
}
```

---

### 5.2 Error Handling & Status Codes

- **HTTP 401 Unauthorized:** Permintaan tanpa header `Authorization: Bearer <token>` atau token kadaluarsa.
- **HTTP 403 Forbidden:** Customer mencoba membuat shipment, menghapus kargo, atau mengakses kargo customer lain; operator mencoba menghapus kargo `IN_TRANSIT` atau mengubah kargo `DELIVERED`.
- **HTTP 404 Not Found:** ID shipment tidak ditemukan di database.
- **HTTP 422 Unprocessable Content:** Validasi input gagal (misal: pelabuhan tujuan sama dengan pelabuhan asal, tanggal tiba sebelum tanggal berangkat, item manifes kosong).
