# Product Requirement Document (PRD) & API Integration Spec
## Sistem Informasi Anak Tidak Sekolah (ATS) & Penanganan Tindak Lanjut

* **Nama Proyek**: ATS User Backend (Laravel REST API)
* **Target Auditor / User**: Frontend Developer (Web / Mobile) & Backend Developer
* **Versi Dokumen**: 1.4.0 (Termasuk Tanggal Tindak Lanjut & Program Intervensi)
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

* **Contoh Response `200 OK`**:
```json
{
  "success": true,
  "message": "Daftar data Anak Tidak Sekolah berhasil diambil.",
  "is_admin": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "nik": "3507123456780001",
        "nisn": "0051234567",
        "nama": "Budi Santoso",
        "jenis_kelamin": "Laki-laki",
        "nama_ibu_kandung": "Siti Aminah",
        "kabupaten": "KAB. MALANG",
        "kecamatan": "KEPANJEN",
        "desa_kelurahan": "ARDIREJO",
        "status": "Belum Sekolah",
        "tindak_lanjuts": [
          {
            "id": 5,
            "keterangan": "Kembali Sekolah",
            "alasan": "Anak telah didaftarkan ke SMP Negeri 1 Kepanjen",
            "program_intervensi": "Program Bantuan Beasiswa KIP & Paket B",
            "dokumen_pendukung_path": "tindak_lanjut/dokumen/surat_beasiswa.pdf",
            "foto_dokumentasi_path": "tindak_lanjut/foto/bukti_kunjungan.jpg",
            "tanggal_tindak_lanjut": "2026-08-12",
            "created_at": "2026-08-12T13:16:00.000000Z"
          }
        ]
      }
    ]
  }
}
```

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
| `tanggal_tindak_lanjut` | Date (YYYY-MM-DD) | Tidak | **[DIKEMBALIKAN]** Tanggal pelaksanaan kunjungan/tindak lanjut |
| `dokumen_pendukung` | File (PDF, DOC, DOCX, PNG, JPG) | Tidak | File Surat/Dokumen Pendukung (**Max 10 MB = 10,240 KB**) |
| `foto_dokumentasi` | File (JPG, JPEG, PNG, WEBP) | Tidak | Foto Bukti Kunjungan Lapangan (**Max 10 MB = 10,240 KB**) |

* **Response `201 Created`**:
```json
{
  "success": true,
  "message": "Data Tindak Lanjut berhasil disimpan.",
  "data": {
    "id": 12,
    "anak_tidak_sekolah_id": 1,
    "user_id": 3,
    "keterangan": "Kembali Sekolah",
    "alasan": "Telah didaftarkan kembali ke sekolah Paket B",
    "program_intervensi": "Bantuan Kartu Indonesia Pintar (KIP) & Pendampingan Belajar Paket B",
    "tanggal_tindak_lanjut": "2026-08-12",
    "dokumen_pendukung_path": "tindak_lanjut/dokumen/abc123.pdf",
    "foto_dokumentasi_path": "tindak_lanjut/foto/xyz789.jpg",
    "created_at": "2026-08-12T13:16:00.000000Z"
  }
}
```

---

#### B. PUT `/api/tindak-lanjut/{id}` *(Khusus Admin)*
* **Fungsi**: Mengubah/Memperbarui form tindak lanjut & mengganti file lampiran / program intervensi / tanggal.

---

#### C. DELETE `/api/tindak-lanjut/{id}` *(Khusus Admin)*
* **Fungsi**: Menghapus data tindak lanjut (Permanen / Hard Delete).

---

## 5. Handling File Upload & Akses Media

File yang diunggah (`dokumen_pendukung_path` & `foto_dokumentasi_path`) tersimpan pada direktori public backend.

Frontend dapat menampilkan/mengunduh file menggunakan URL:
`http://<domain-backend>/storage/<path_file>`

*Contoh*:
`http://localhost:8000/storage/tindak_lanjut/foto/xyz789.jpg`
