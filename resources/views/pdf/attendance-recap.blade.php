<!DOCTYPE html>
<html>

<head>
    <title>Rekap Kehadiran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #2d3748;
            margin: 40px;
            background-color: #fff;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #1a73e8 0%, #0052cc 100%);
            color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .report-header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .report-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .company-details {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .company-info {
            flex: 1;
        }

        .report-info {
            flex: 1;
            text-align: right;
        }

        .info-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            flex: 1;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .stat-title {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1a73e8;
            margin-bottom: 4px;
        }

        .stat-subtitle {
            font-size: 11px;
            color: #94a3b8;
        }

        .attendance-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .attendance-table th {
            background: #1e293b;
            color: white;
            padding: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }

        .attendance-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }

        .attendance-table tr:last-child td {
            border-bottom: none;
        }

        .attendance-table tr:hover {
            background-color: #f8fafc;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }

        .status-hadir {
            background-color: #dcfce7;
            color: #15803d;
        }

        .status-tidak-checkout {
            background-color: #fef3c7;
            color: #b45309;
        }

        .status-alfa {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .report-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .signature-box {
            text-align: center;
            flex: 0 0 200px;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            margin: 50px 20px 10px;
        }

        .notes-section {
            margin: 30px 0;
            padding: 15px;
            background: #f8fafc;
            border-left: 4px solid #1a73e8;
            border-radius: 4px;
            font-size: 11px;
            color: #64748b;
        }

        .small-stats .stat-title {
            font-size: 10px;
        }

        .small-stats .stat-value {
            font-size: 18px;
        }

        .small-stats .stat-subtitle {
            font-size: 10px;
        }

        @page {
            margin: 40px;
        }
    </style>
</head>

<body>
    <h2 style="text-align: center; margin-bottom: 20px;">Rekap Kehadiran Karyawan</h2>

    <div class="report-header">
        <h1>REKAP KEHADIRAN KARYAWAN</h1>
        <p>PT. Git Solutions</p>
    </div>

    <div class="company-details">
        <div class="company-info">
            <div class="info-label">Alamat Perusahaan</div>
            <div class="info-value">Jl. Contoh No. 123, Jakarta</div>
            <div class="info-label" style="margin-top: 10px">Periode Laporan</div>
            <div class="info-value">{{ now()->startOfMonth()->format('d M Y') }} -
                {{ now()->endOfMonth()->format('d M Y') }}
            </div>
        </div>
        <div class="report-info">
            <div class="info-label">Nomor Dokumen</div>
            <div class="info-value">RK/{{ now()->format('Y/m') }}/001</div>
            <div class="info-label" style="margin-top: 10px">Tanggal Cetak</div>
            <div class="info-value">{{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-title">Total Kehadiran</div>
        <div class="stat-value">
            {{ $records->where('waktu_datang', '!=', null)->where('waktu_pulang', '!=', null)->count() }}
        </div>
        <div class="stat-subtitle">Hari Kerja</div>
    </div>
    <div class="stat-box">
        <div class="stat-title">Rata-rata Keterlambatan</div>
        <div class="stat-value">
            {{ number_format($records->where('is_late', true)->count() / max($records->count(), 1) * 100, 1) }}%
        </div>
        <div class="stat-subtitle">Dari Total Kehadiran</div>
    </div>
    <div class="stat-box">
        <div class="stat-title">Total Ketidakhadiran</div>
        <div class="stat-value">{{ $records->where('waktu_datang', null)->count() }}</div>
        <div class="stat-subtitle">Alfa</div>
    </div>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Karyawan</th>
                <th>Durasi Kerja</th>
                <th>Job Role</th>
                <th>Waktu Datang</th>
                <th>Waktu Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->created_at->format('d/m/Y') }}</td>
                    <td>{{ $record->user->name }}</td>
                    <td>{{ $record->calculateWorkDuration() }}</td>
                    <td>{{ $record->user->roles->first()->name ?? '-' }}</td>
                    <td>{{ $record->waktu_datang ?? '-' }}</td>
                    <td>{{ $record->waktu_pulang ?? '-' }}</td>
                    <td>
                        <span
                            class="status-badge status-{{ !$record->waktu_datang ? 'alfa' : (!$record->waktu_pulang ? 'tidak-checkout' : 'hadir') }}">
                            {{ !$record->waktu_datang ? 'Alfa' : (!$record->waktu_pulang ? 'Tidak Checkout' : 'Hadir') }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="notes-section">
        <strong>Keterangan:</strong>
        <ul>
            <li>Hadir: Karyawan melakukan check-in dan check-out sesuai jadwal</li>
            <li>Tidak Checkout: Karyawan hanya melakukan check-in tanpa check-out</li>
            <li>Alfa: Karyawan tidak melakukan check-in dan check-out</li>
        </ul>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Dibuat oleh</div>
            <div style="color: #64748b;">Admin HR</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Disetujui oleh</div>
            <div style="color: #64748b;">Manager HR</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Mengetahui</div>
            <div style="color: #64748b;">Direktur</div>
        </div>
    </div>

    <div class="report-footer">
        <div style="text-align: center; color: #94a3b8; font-size: 10px;">
            <p>Dokumen ini digenerate secara otomatis pada {{ now()->format('d M Y, H:i:s') }}</p>
            <p>© {{ now()->format('Y') }} PT. Git Solutions. All rights reserved.</p>
        </div>
    </div>
</body>

</html>