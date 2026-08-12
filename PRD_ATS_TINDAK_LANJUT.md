# Product Requirement Document (PRD) & API Integration Spec
## Sistem Informasi Anak Tidak Sekolah (ATS) & Penanganan Tindak Lanjut

* **Nama Proyek**: ATS User Backend (Laravel REST API)
* **Target Auditor / User**: Frontend Developer (Web / Mobile) & Backend Developer
* **Versi Dokumen**: 1.5.0 (Termasuk Kerangka Endpoint Export Excel & PDF Terfilter)
* **Status**: Ready for Integration

---

## 1. Ringkasan Sistem (*Executive Summary*)

Sistem Informasi ATS diciptakan untuk memfasilitasi pendataan, monitoring, dan pencatatan interaksi penanganan penanggulangan Anak Tidak Sekolah (*ATS*) oleh pemerintah/dinas terkait.

Sistem terdiri dari 2 modul utama:
1. **Modul Master Data ATS** (Profil identitas anak 43 kolom).
2. **Modul Tindak Lanjut** (Dokumentasi kunjungan lapangan, tanggal pelaksanaan, program intervensi yang disarankan, dokumen pendukung, dan foto bukti).

---

## 2. Hak Akses & Keamanan (*Role-Based Access Control*)

Sistem membedakan respon data dan izin aksi berdasarkan **Role User**:

| Fitur / Akses Data | Admin (`role: 'admin'`) | User Biasa / Publik |
| :--- | :--- | :--- |
| **Profil ATS (43 Kolom)** | **Full Biodata** (Termasuk NIK, No KK, Ibu Kandung, Tanggal Lahir, Alamat Jalan) | **Ringkas / Masking** (Hanya ID, Nama, Gender, Kab, Kec, Desa, Status) |
| **Melihat List/Detail ATS** | ✅ Diizinkan | ✅ Diizinkan (Data Ringkas) |
| **Membuka List/Detail Tindak Lanjut** | ✅ Diizinkan | ✅ Diizinkan |
| **Menambah/Edit/Hapus ATS & Impor Excel**| ✅ Diizinkan | ❌ **403 Forbidden** |
| **Mengisi Form Tindak Lanjut** | ✅ Diizinkan | ❌ **403 Forbidden** |
| **Export File (Excel / PDF Terfilter)** | ✅ Diizinkan | ✅ Diizinkan |

---

## 3. Spesifikasi Database & Relasi Model

```
+------------------------+           +-----------------------+
|   anak_tidak_sekolah   |           |     tindak_lanjut     |
+------------------------+           +-----------------------+
| id (PK)                |<---------1| id (PK)               |
| nik                    |           | anak_tidak_sekolah_id | (FK)
| nisn                   |           | user_id               | (FK)
| nama                   |           | keterangan            |
| jenis_kelamin          |           | alasan                |
| kabupaten              |           | program_intervensi    |
| kecamatan              |           | dokumen_pendukung_path|
| desa_kelurahan         |           | foto_dokumentasi_path |
| status                 |           | tanggal_tindak_lanjut |
| ... (Total 43 Kolom)   |           +-----------------------+
+------------------------+
```

---

## 4. Spesifikasi Integration API Endpoints

### 4.1. Endpoint Master ATS (`/api/ats`)

#### A. GET `/api/ats`
* **Fungsi**: Memuat daftar Anak Tidak Sekolah (dengan pagination).
* **Query Parameters**:
  - `search` *(string)*: Pencarian NIK, NISN, atau Nama.
  - `kecamatan` *(string)*: Filter Nama Kecamatan.
  - `kabupaten` *(string)*: Filter Nama Kabupaten.
  - `status` *(string)*: Filter Status ATS.
  - `filter_tindak_lanjut` *(string)*: 
    - `sudah_ditindaklanjuti`: Mengambil anak yang **sudah** pernah ditindaklanjuti.
    - `belum_ditindaklanjuti`: Mengambil anak yang **belum** pernah ditindaklanjuti.
  - `keterangan_tindak_lanjut` *(string)*: Filter hasil tindak lanjut (contoh: `Kembali Sekolah`, `Bekerja`, `Menikah`).
  - `per_page` *(int)*: Jumlah data per halaman (Default: 15).
  - `page` *(int)*: Nomor halaman.

---

#### B. GET `/api/ats/export-excel` *(BARU)*
* **Fungsi**: Mengunduh file Excel (`.xlsx`) langsung berdasarkan parameter filter yang sedang aktif di UI Frontend.
* **Query Parameters**: Sama persis dengan `GET /api/ats` (`search`, `kabupaten`, `kecamatan`, `filter_tindak_lanjut`, `keterangan_tindak_lanjut`).
* **Response**: File Download binary stream `.xlsx`.

---

#### C. GET `/api/ats/export-pdf` *(BARU)*
* **Fungsi**: Mengunduh file PDF resmi berdasarkan parameter filter yang sedang aktif di UI Frontend.
* **Query Parameters**: Sama persis dengan `GET /api/ats`.
* **Response**: Stream File PDF (atau JSON data siap cetak).

---

### 4.2. Endpoint Form Tindak Lanjut (`/api/tindak-lanjut`)

#### A. POST `/api/tindak-lanjut` *(Khusus Admin)*
* **Fungsi**: Menyimpan form penanganan tindak lanjut baru.
* **Headers**: `Content-Type: multipart/form-data`
* **Body Form Data**:

| Field Name | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `anak_tidak_sekolah_id` | Integer | **Ya** | ID anak dari tabel `anak_tidak_sekolah` |
| `keterangan` | String | **Ya** | Opsi/Pilihan Dropdown UI (*Kembali Sekolah*, *Bekerja*, *Menikah*, *Pindah*, dll) |
| `alasan` | String / Text | Tidak | Catatan/alasan rincian hasil kunjungan |
| `program_intervensi` | String / Text | Tidak | Program intervensi yang disarankan (misal: Beasiswa, PKH, Paket A/B/C, Pelatihan Kerja, KIP) |
| `tanggal_tindak_lanjut` | Date (YYYY-MM-DD) | Tidak | Tanggal pelaksanaan kunjungan/tindak lanjut |
| `dokumen_pendukung` | File (PDF, DOC, DOCX, PNG, JPG) | Tidak | File Surat/Dokumen Pendukung (**Max 10 MB = 10,240 KB**) |
| `foto_dokumentasi` | File (JPG, JPEG, PNG, WEBP) | Tidak | Foto Bukti Kunjungan Lapangan (**Max 10 MB = 10,240 KB**) |

---

## 5. Handling File Upload & Akses Media

File yang diunggah (`dokumen_pendukung_path` & `foto_dokumentasi_path`) tersimpan pada direktori public backend.

Frontend dapat menampilkan/mengunduh file menggunakan URL:
`http://<domain-backend>/storage/<path_file>`
