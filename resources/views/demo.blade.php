<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem ATS - Panel Reporting & Download Laporan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .app-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            width: 100%;
            max-width: 580px;
            padding: 36px;
            margin: 20px;
        }
        .app-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .badge {
            background-color: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .app-header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        .app-header p {
            margin: 8px 0 0 0;
            color: #64748b;
            font-size: 14px;
        }
        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .field-group label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .select-input, .text-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            color: #0f172a;
            background-color: #ffffff;
            outline: none;
            transition: all 0.2s ease;
        }
        .select-input:focus, .text-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .download-action {
            margin-top: 12px;
        }
        .btn-download-pdf {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-download-pdf:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }
        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 12.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="app-container">
        <div class="app-header">
            <span class="badge">Sistem ATS Sulawesi Tengah</span>
            <h1>Panel Download Laporan PDF</h1>
            <p>Pilih filter kriteria data di bawah untuk men-download laporan resmi.</p>
        </div>

        <form action="/api/ats/export-pdf" method="GET" target="_blank" class="form-grid">
            
            <div class="field-group">
                <label for="filter_tindak_lanjut">Status Penanganan ATS</label>
                <select id="filter_tindak_lanjut" name="filter_tindak_lanjut" class="select-input">
                    <option value="">-- Semua Data Anak ATS --</option>
                    <option value="sudah_ditindaklanjuti">Sudah Ditindaklanjuti</option>
                    <option value="belum_ditindaklanjuti">Belum Ditindaklanjuti</option>
                </select>
            </div>

            <div class="field-group">
                <label for="keterangan_tindak_lanjut">Hasil Penanganan (Keterangan)</label>
                <select id="keterangan_tindak_lanjut" name="keterangan_tindak_lanjut" class="select-input">
                    <option value="">-- Semua Hasil Penanganan --</option>
                    <option value="Kembali Sekolah">Kembali Sekolah</option>
                    <option value="Bekerja">Bekerja</option>
                    <option value="Menikah">Menikah</option>
                    <option value="Pindah">Pindah Domisili</option>
                </select>
            </div>

            <div class="field-group">
                <label for="kabupaten">Kabupaten / Kota</label>
                <input type="text" id="kabupaten" name="kabupaten" class="text-input" placeholder="Contoh: KOTA PALU">
            </div>

            <div class="field-group">
                <label for="kecamatan">Kecamatan</label>
                <input type="text" id="kecamatan" name="kecamatan" class="text-input" placeholder="Contoh: MANTIKULORE">
            </div>

            <div class="download-action">
                <button type="submit" class="btn-download-pdf">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Laporan PDF Resmi
                </button>
            </div>
        </form>

        <div class="footer-note">
            Port Terhubung: <strong>http://localhost:8000</strong> &bull; Terintegrasi Langsung dengan Backend ATS
        </div>
    </div>

</body>
</html>
