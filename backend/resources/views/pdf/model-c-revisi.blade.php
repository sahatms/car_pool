
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Model C - Permohonan Angkutan Bermotor</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 15mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #000;
        }

        h2, h3, p {
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h2 {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 9px;
        }

        .section-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .form-table td,
        .form-table th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .label {
            width: 32%;
        }

        .signature {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        .signature-space {
            height: 55px;
        }

        .small {
            font-size: 9px;
        }

        .document-code {
            text-align: right;
            font-size: 9px;
            margin-bottom: 8px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    {{-- BAGIAN C: PERMOHONAN ANGKUTAN BERMOTOR --}}

    <div class="header">
        <h2>PERUMDA AIR MINUM TIRTA HITA BULELENG</h2>
        <p>FORMULIR MODEL C</p>
        <p>PERMOHONAN ANGKUTAN BERMOTOR</p>
    </div>

    <div class="document-code">
        Nomor Booking: {{ $booking->booking_code }}
    </div>

    <div class="section-title">
        C. Permohonan Angkutan Bermotor
    </div>

    <p style="margin-bottom: 8px;">
        Kepada Yth. Bagian Umum
        <br>
        Mohon disediakan kendaraan bermotor untuk keperluan berikut:
    </p>

    <table class="form-table">
        <tr>
            <td class="label">Nama peminjam</td>
            <td>{{ $booking->borrower->full_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nomor pegawai</td>
            <td>{{ $booking->borrower->employee_number ?? '-' }}</td>
        </tr>
        <tr>
            <td>Bagian / departemen</td>
            <td>{{ $booking->borrower->department ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jumlah penumpang</td>
            <td>{{ $booking->passenger_count ?? '-' }} orang</td>
        </tr>
        <tr>
            <td>Nama pengemudi</td>
            <td>{{ $booking->driver_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tujuan perjalanan</td>
            <td>{{ $booking->destination }}</td>
        </tr>
        <tr>
            <td>Keperluan perjalanan</td>
            <td>{{ $booking->purpose }}</td>
        </tr>
        <tr>
            <td>Tanggal dan waktu berangkat</td>
            <td>
                {{ $booking->start_datetime
                    ? \Carbon\Carbon::parse($booking->start_datetime)->format('d-m-Y H:i')
                    : '-' }}
            </td>
        </tr>
        <tr>
            <td>Tanggal dan waktu kembali</td>
            <td>
                {{ $booking->end_datetime
                    ? \Carbon\Carbon::parse($booking->end_datetime)->format('d-m-Y H:i')
                    : '-' }}
            </td>
        </tr>
        <tr>
            <td>Kendaraan</td>
            <td>
                {{ $booking->vehicle->brand ?? '-' }}
                /
                {{ $booking->vehicle->vehicle_category ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>Nomor polisi</td>
            <td>{{ $booking->vehicle->license_plate ?? '-' }}</td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td>
                Pemohon,
                <div class="signature-space"></div>
                <strong>
                    ({{ $booking->borrower->full_name ?? '........................' }})
                </strong>
            </td>
            <td>
                Mengetahui / Menyetujui,
                <div class="signature-space"></div>
                <strong>(....................................)</strong>
            </td>
        </tr>
    </table>

    {{-- BAGIAN PERINTAH JALAN --}}

    <div class="section-title" style="margin-top: 25px;">
        Perintah Jalan
    </div>

    <table class="form-table">
        <tr>
            <td class="label">Nomor booking</td>
            <td>{{ $booking->booking_code }}</td>
        </tr>
        <tr>
            <td>Nama pengemudi</td>
            <td>{{ $booking->driver_name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jumlah penumpang</td>
            <td>{{ $booking->passenger_count ?? '-' }} orang</td>
        </tr>
        <tr>
            <td>Jenis / merek kendaraan</td>
            <td>
                {{ $booking->vehicle->vehicle_category ?? '-' }}
                /
                {{ $booking->vehicle->brand ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>Nomor polisi</td>
            <td>{{ $booking->vehicle->license_plate ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tujuan</td>
            <td>{{ $booking->destination }}</td>
        </tr>
        <tr>
            <td>Keperluan</td>
            <td>{{ $booking->purpose }}</td>
        </tr>
        <tr>
            <td>Waktu berangkat</td>
            <td>
                {{ $booking->start_datetime
                    ? \Carbon\Carbon::parse($booking->start_datetime)->format('d-m-Y H:i')
                    : '-' }}
            </td>
        </tr>
        <tr>
            <td>Waktu kembali</td>
            <td>
                {{ $booking->end_datetime
                    ? \Carbon\Carbon::parse($booking->end_datetime)->format('d-m-Y H:i')
                    : '-' }}
            </td>
        </tr>
        <tr>
            <td>Odometer awal / akhir</td>
            <td>................................................</td>
        </tr>
        <tr>
            <td>BBM</td>
            <td>................................................</td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td>
                Pengemudi,
                <div class="signature-space"></div>
                <strong>
                    ({{ $booking->driver_name ?? '........................' }})
                </strong>
            </td>
            <td>
                Kepala Bagian Umum,
                <div class="signature-space"></div>
                <strong>(....................................)</strong>
            </td>
        </tr>
    </table>

    <p class="small" style="margin-top: 15px;">
        Catatan: Dokumen ini merupakan hasil cetak pengajuan booking.
        Persetujuan resmi mengikuti proses persetujuan perusahaan.
    </p>

</body>
</html>
