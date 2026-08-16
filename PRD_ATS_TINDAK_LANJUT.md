# Product Requirement Document (PRD) & API Integration Spec
## Sistem Informasi Anak Tidak Sekolah (ATS) & Penanganan Tindak Lanjut

* **Nama Proyek**: ATS User Backend (Laravel REST API)
* **Target Auditor / User**: Frontend Developer (Web / Mobile) & Backend Developer
* **Versi Dokumen**: 1.6.0 (Termasuk Modul Tabel Riwayat Import & Log)
* **Status**: Ready for Integration

---

## 1. Ringkasan Sistem (*Executive Summary*)

Sistem Informasi ATS diciptakan untuk memfasilitasi pendataan, monitoring, pencatatan interaksi penanganan penanggulangan Anak Tidak Sekolah (*ATS*), serta audit log pengunggahan berkas Excel oleh dinas terkait.

Sistem terdiri dari 3 modul utama:
1. **Modul Master Data ATS** (Profil identitas anak 43 kolom).
2. **Modul Tindak Lanjut** (Dokumentasi kunjungan lapangan, tanggal pelaksanaan, program intervensi yang disarankan, dokumen pendukung, dan foto bukti).
3. **Modul Riwayat Import & Log** (Pencatatan tanggal/waktu, periode data, nama berkas, jumlah data sukses, dan duplikat/skip).

---

## 2. Spesifikasi Database & Relasi Model

```
+------------------------+           +-----------------------+
|   anak_tidak_sekolah   |           |     tindak_lanjut     |
+------------------------+           +-----------------------+
| id (PK)                |<---------1| id (PK)               |
| nik                    |           | anak_tidak_sekolah_id | (FK)
| nisn                   |           | user_id               | (FK)
| nama                   |           | keterangan            |
| ... (Total 43 Kolom)   |           | program_intervensi    |
+------------------------+           | tanggal_tindak_lanjut |
                                     +-----------------------+

+------------------------+
|     riwayat_import     |
+------------------------+
| id (PK)                |
| user_id (FK)           |
| periode_data           | (contoh: "Periode #1", "Periode #2")
| nama_berkas            | (contoh: "ats_sigi_gumbasa_val.csv")
| data_sukses            | (contoh: 1, 6)
| data_duplikat          | (contoh: 0, 1)
| status                 | (contoh: "Selesai", "Gagal")
| created_at             | (Tanggal & Waktu Import)
+------------------------+
```

---

## 3. Spesifikasi Integration API Endpoints

### 3.1. Endpoint Riwayat Import (`/api/ats/riwayat-import`)

#### A. GET `/api/ats/riwayat-import` *(BARU)*
* **Fungsi**: Memuat daftar Riwayat Import & Log untuk tabel tab *"Riwayat Import & Log"*.
* **Response `200 OK`**:
```json
{
  "success": true,
  "message": "Daftar riwayat import berhasil diambil.",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 2,
        "periode_data": "Periode #2",
        "nama_berkas": "ats_sigi_gumbasa_val.csv",
        "data_sukses": 1,
        "data_duplikat": 0,
        "status": "Selesai",
        "created_at": "2026-07-20T14:15:00.000000Z"
      },
      {
        "id": 1,
        "periode_data": "Periode #1",
        "nama_berkas": "ats_provinsi_sulteng_semester_1.xlsx",
        "data_sukses": 6,
        "data_duplikat": 1,
        "status": "Selesai",
        "created_at": "2026-06-15T09:30:00.000000Z"
      }
    ]
  }
}
```

---

### 3.2. Endpoint Form Tindak Lanjut (`/api/tindak-lanjut`)

| Field Name | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `anak_tidak_sekolah_id` | Integer | **Ya** | ID anak dari tabel `anak_tidak_sekolah` |
| `keterangan` | String | **Ya** | Opsi/Pilihan Dropdown UI (*Kembali Sekolah*, *Bekerja*, *Menikah*, *Pindah*, dll) |
| `alasan` | String / Text | Tidak | Catatan/alasan rincian hasil kunjungan |
| `program_intervensi` | String / Text | Tidak | Program intervensi yang disarankan |
| `tanggal_tindak_lanjut` | Date (YYYY-MM-DD) | Tidak | Tanggal pelaksanaan kunjungan/tindak lanjut |
| `dokumen_pendukung` | File | Tidak | File Surat/Dokumen Pendukung (**Max 10 MB**) |
| `foto_dokumentasi` | File | Tidak | Foto Bukti Kunjungan Lapangan (**Max 10 MB**) |
