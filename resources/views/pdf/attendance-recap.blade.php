<!DOCTYPE html>
<html>

<head>
    <title>Rekap Kehadiran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Rekap Kehadiran Karyawan</h2>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Durasi Kerja</th>
                <th>Waktu Datang</th>
                <th>Waktu Pulang</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->created_at->format('d/m/Y') }}</td>
                    <td>{{ $record->user->name }}</td>
                    <td>{{ $record->isLate() ? 'Terlambat' : 'Tepat Waktu' }}</td>
                    <td>{{ $record->calculateWorkDuration() }}</td>
                    <td>{{ $record->waktu_datang }}</td>
                    <td>{{ $record->waktu_pulang }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>