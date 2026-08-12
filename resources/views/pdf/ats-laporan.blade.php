<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Penanganan Anak Tidak Sekolah (ATS)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 5px 0 0 0;
            font-size: 12pt;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 9pt;
            font-style: italic;
        }
        .title {
            text-align: center;
            margin-bottom: 20px;
        }
        .title h4 {
            margin: 0;
            font-size: 12pt;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .title p {
            margin: 5px 0 0 0;
            font-size: 10pt;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #444;
            padding: 6px 8px;
            font-size: 9.5pt;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .signature-container {
            width: 100%;
            margin-top: 30px;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="header">
        <h2>PEMERINTAH PROVINSI SULAWESI TENGAH</h2>
        <h3>DINAS PENDIDIKAN DAN KEBUDAYAAN</h3>
        <p>Jl. Setia Budi No. 9, Palu, Sulawesi Tengah - Kode Pos 94111</p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="title">
        <h4>LAPORAN REKAPITULASI PENANGANAN ANAK TIDAK SEKOLAH (ATS)</h4>
        <p>Dicetak Pada: {{ date('d F Y') }}</p>
    </div>

    <!-- TABEL DATA ATS -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="15%">Nama Anak</th>
                <th width="12%">NIK / NISN</th>
                <th width="8%">Gender</th>
                <th width="18%">Wilayah (Kab/Kec/Desa)</th>
                <th width="13%">Status Penanganan</th>
                <th width="15%">Program Intervensi</th>
                <th width="15%">Catatan Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                @php
                    $tindakLanjut = $item->tindakLanjuts->last();
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->nama }}</strong></td>
                    <td class="text-center">
                        {{ $item->nik ?? '-' }}<br>
                        <small style="color: #666;">NISN: {{ $item->nisn ?? '-' }}</small>
                    </td>
                    <td class="text-center">{{ $item->jenis_kelamin }}</td>
                    <td>
                        {{ $item->kabupaten }}<br>
                        <small>Kec: {{ $item->kecamatan }} | Desa: {{ $item->desa_kelurahan }}</small>
                    </td>
                    <td class="text-center">
                        @if($tindakLanjut)
                            <span style="color: green; font-weight: bold;">{{ $tindakLanjut->keterangan }}</span>
                        @else
                            <span style="color: red;">Belum Ditindaklanjuti</span>
                        @endif
                    </td>
                    <td>{{ $tindakLanjut ? ($tindakLanjut->program_intervensi ?? '-') : '-' }}</td>
                    <td>{{ $tindakLanjut ? ($tindakLanjut->alasan ?? '-') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">
                        <em>Tidak ada data Anak Tidak Sekolah yang sesuai dengan filter pencarian.</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN PEJABAT -->
    <div class="signature-container">
        <div class="signature-box">
            <p>Palu, {{ date('d F Y') }}<br><strong>Kepala Dinas / Pejabat Berwenang</strong></p>
            <div class="signature-space"></div>
            <p><u>________________________</u><br>NIP. ........................................</p>
        </div>
    </div>

</body>
</html>
