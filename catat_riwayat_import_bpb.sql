INSERT INTO `riwayat_import` (
    `user_id`, `periode_data`, `nama_berkas`, `data_sukses`, `data_duplikat`, `status`, `catatan`, `created_at`, `updated_at`
)
SELECT 
    (SELECT id FROM users ORDER BY id ASC LIMIT 1), 
    "Periode Baru (Data BPB)", 
    "backbone_ats_kec (3).sql", 
    (SELECT COUNT(*) FROM anak_tidak_sekolah WHERE status = "BPB"), 
    0, 
    "Selesai", 
    "Impor Data Terpadu BPB (Belum Pernah Bersekolah) se-Provinsi Sulawesi Tengah", 
    NOW(), 
    NOW();