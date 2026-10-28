<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Model C - Permohonan Angkutan Bermotor</title>
    <style>
        @page { size: A4 portrait; margin: 9mm 11mm 10mm; }
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #000; margin: 0; }
        .header { width: 100%; border-collapse: collapse; border-bottom: 3px double #000; margin-bottom: 8px; }
        .header td { vertical-align: middle; padding: 2px 3px 5px; }
        .logo { width: 78px; text-align: center; }
        .logo-box { width: 65px; height: 67px; border: 1px solid #000; margin: auto; text-align: center; padding-top: 19px; font-size: 7px; font-weight: bold; }
        .heading { text-align: center; }
        .government { font-size: 13px; font-weight: normal; }
        .company { font-size: 13px; font-weight: bold; margin-top: 2px; }
        .address { font-size: 8px; margin-top: 2px; line-height: 1.35; }
        .request-title { text-align: center; font-size: 13px; font-weight: bold; margin: 7px 0 3px; }
        .purpose-title { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 8px; }
        .big-c { font-size: 36px; vertical-align: -5px; margin-right: 5px; }
        .fields { width: 100%; border-collapse: collapse; }
        .fields td { padding: 2.4px 1px; vertical-align: bottom; }
        .field-label { width: 39%; }
        .colon { width: 12px; text-align: center; }
        .line { border-bottom: 1px solid #000; height: 17px; padding: 1px 3px 2px; }
        .line-wrap { border-bottom: 1px solid #000; min-height: 17px; padding: 1px 3px 2px; }
        .inline-line { display: inline-block; border-bottom: 1px solid #000; min-height: 15px; padding: 1px 3px; }
        .small { font-size: 8px; }
        .signatures { width: 100%; border-collapse: collapse; margin-top: 11px; margin-bottom: 7px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 0 5px; }
        .sign-label { height: 17px; }
        .sign-space { height: 38px; }
        .sign-name { min-height: 18px; }
        .divider { border-top: 2px solid #000; margin: 4px 0 5px; }
        .road-title { text-align: center; font-size: 13px; font-weight: bold; text-decoration: underline; margin: 3px 0 0; }
        .road-subtitle { text-align: center; font-size: 9px; margin-bottom: 4px; }
        .road-fields { width: 100%; border-collapse: collapse; }
        .road-fields td { padding: 2px 1px; vertical-align: bottom; }
        .road-label { white-space: nowrap; }
        .road-line { border-bottom: 1px solid #000; height: 17px; padding: 1px 3px; }
        .travel-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .travel-table th { border-top: 2px solid #000; border-bottom: 1px solid #000; padding: 3px; font-size: 9px; }
        .travel-table td { padding: 3px 4px; height: 18px; vertical-align: bottom; }
        .travel-table .left { width: 50%; border-right: 1px solid #000; }
        .travel-table .right { width: 50%; }
        .blank { display: inline-block; border-bottom: 1px solid #000; height: 13px; }
        .approval { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .approval td { padding: 3px 1px; vertical-align: top; }
        .copies { width: 50%; }
        .head-sign { width: 50%; text-align: center; }
        .head-space { height: 37px; }
        .booking-code { text-align: right; font-size: 7.5px; margin-top: 2px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="logo">
                {{-- Ganti kotak ini dengan logo resmi apabila file logo sudah tersedia. --}}
                <div class="logo-box">LAMBANG<br>KABUPATEN<br> BULELENG</div>
            </td>
            <td class="heading">
                <div class="government">PEMERINTAH KABUPATEN BULELENG</div>
                <div class="company">PERUMDA AIR MINUM TIRTA HITA BULELENG</div>
                <div class="address">
                    Jl. Melati No. 10, Telp. (0362) 22107, 22174 Fax. 22022<br>
                    Dinas Gangguan Telp. 21140 - Singaraja 81113 - BALI
                </div>
            </td>
        </tr>
    </table>

    <div class="request-title"><span class="big-c">C</span> PERMOHONAN ANGKUTAN BERMOTOR</div>
    <div class="purpose-title">UNTUK KEPERLUAN : DINAS</div>

    <table class="fields">
        <tr>
            <td class="field-label">Nama pemohon</td><td class="colon">:</td>
            <td class="line">{{ $booking->borrower->full_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="field-label">Bagian</td><td class="colon">:</td>
            <td class="line">{{ $booking->borrower->department ?? '-' }}</td>
            <td style="width: 18%; padding-left: 8px;">Macam Kendaraan</td>
            <td class="line">{{ $booking->vehicle->vehicle_category ?? '-' }}</td>
        </tr>
        <tr>
            <td class="field-label">Banyaknya penumpang yang ikut</td><td class="colon">:</td>
            <td class="line" colspan="2">{{ $booking->passenger_count ?? '-' }}</td>
            <td style="width: 9%; padding-left: 4px;">Orang</td>
        </tr>
        <tr>
            <td class="field-label">Tujuan</td><td class="colon">:</td>
            <td class="line" colspan="3">{{ $booking->destination ?? '-' }}</td>
        </tr>
        <tr>
            <td class="field-label">Keperluan</td><td class="colon">:</td>
            <td class="line" colspan="3">{{ $booking->purpose ?? '-' }}</td>
        </tr>
        <tr>
            <td class="field-label">Tanggal yang diinginkan</td><td class="colon">:</td>
            <td class="line" colspan="2">
                {{ $booking->start_datetime ? \Carbon\Carbon::parse($booking->start_datetime)->translatedFormat('j F Y') : '-' }}
            </td>
            <td style="padding-left: 4px;">Jam</td>
        </tr>
        <tr>
            <td class="field-label">Kendaraan yang akan digunakan kira-kira</td><td class="colon">:</td>
            <td class="line" colspan="3">{{ ($booking->vehicle->brand ?? '-') . ' / ' . ($booking->vehicle->license_plate ?? '-') }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-label">Menyetujui,</div>
                <div class="sign-space"></div>
                <div class="sign-name">(........................................)</div>
            </td>
            <td>
                <div class="sign-label">Singaraja, {{ $booking->start_datetime ? \Carbon\Carbon::parse($booking->start_datetime)->translatedFormat('j F Y') : '....................' }}</div>
                <div class="sign-space">Tanda tangan Pemohon,</div>
                <div class="sign-name">({{ $booking->borrower->full_name ?? '........................................' }})</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>
    <div class="road-title">PERINTAH JALAN</div>
    <div class="road-subtitle">Kepala Bag. Pemeliharaan</div>

    <table class="road-fields">
        <tr>
            <td class="road-label" style="width: 14%;">Tanggal</td>
            <td style="width: 2%;">:</td>
            <td class="road-line" style="width: 27%;">{{ $booking->start_datetime ? \Carbon\Carbon::parse($booking->start_datetime)->format('d-m-Y') : '' }}</td>
            <td class="road-label" style="width: 7%; text-align: right; padding-right: 4px;">Mobil</td>
            <td style="width: 2%;">:</td>
            <td class="road-line" style="width: 24%;">{{ $booking->vehicle->brand ?? '' }}</td>
            <td class="road-label" style="width: 7%; text-align: right; padding-right: 4px;">No. Pol.</td>
            <td style="width: 2%;">:</td>
            <td class="road-line">{{ $booking->vehicle->license_plate ?? '' }}</td>
        </tr>
        <tr>
            <td class="road-label">Pengemudi</td><td>:</td>
            <td class="road-line" colspan="7">{{ $booking->driver_name ?? '' }}</td>
        </tr>
        <tr>
            <td class="road-label">Tujuan</td><td>:</td>
            <td class="road-line" colspan="7">{{ $booking->destination ?? '' }}</td>
        </tr>
        <tr>
            <td class="road-label">Keperluan</td><td>:</td>
            <td class="road-line" colspan="7">{{ $booking->purpose ?? '' }}</td>
        </tr>
    </table>

    <table class="travel-table">
        <tr>
            <th class="left">W A K T U</th>
            <th class="right">STAND KILOMETER</th>
        </tr>
        <tr>
            <td class="left">Berangkat jam&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="blank" style="width: 145px;"></span></td>
            <td class="right">Berangkat&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="blank" style="width: 125px;"></span> Km.</td>
        </tr>
        <tr>
            <td class="left">Kembali jam&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="blank" style="width: 145px;"></span></td>
            <td class="right">Kembali&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span class="blank" style="width: 125px;"></span> Km.</td>
        </tr>
        <tr>
            <td class="left">Lamanya perjalanan : <span class="blank" style="width: 110px;"></span> Jam</td>
            <td class="right">Jarak yang ditempuh : <span class="blank" style="width: 90px;"></span> Km.</td>
        </tr>
        <tr>
            <td class="left" style="border-bottom: 2px solid #000;">&nbsp;<span class="blank" style="width: 145px;"></span> hari</td>
            <td class="right" style="border-bottom: 2px solid #000;"></td>
        </tr>
        <tr>
            <td class="left">Setuju diberikan BBM : <span class="blank" style="width: 130px;"></span> L</td>
            <td class="right"></td>
        </tr>
    </table>

    <table class="approval">
        <tr>
            <td class="copies">
                <strong><u>TEMBUSAN :</u></strong><br>
                1.&nbsp; Kepala Pool<br>
                2.&nbsp; Komandan Satpam
            </td>
            <td class="head-sign">
                Singaraja, {{ $booking->start_datetime ? \Carbon\Carbon::parse($booking->start_datetime)->translatedFormat('j F Y') : '........................' }}
                <div class="head-space"></div>
                Kepala Bagian Umum,
                <div style="height: 8px;"></div>
                (........................................)
            </td>
        </tr>
    </table>

    <div class="booking-code">Kode booking: {{ $booking->booking_code }}</div>
</body>
</html>
