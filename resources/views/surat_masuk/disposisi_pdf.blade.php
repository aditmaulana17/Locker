<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Disposisi - {{ $suratMasuk->nomor_agenda ?? 'Surat' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif, Arial, sans-serif;
            color: #000;
            background-color: #555;
            margin: 0;
            padding: 10px;
        }
        .page-container {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border-radius: 4px;
        }
        .header-surat {
            text-align: center;
            border-bottom: 2.5px double #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-surat h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-surat h3 {
            margin: 4px 0 0 0;
            font-size: 13px;
            font-weight: normal;
        }
        .table-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .table-info td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .table-info td.label {
            width: 32%;
            font-weight: bold;
        }
        .table-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        .table-grid th, .table-grid td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }
        .table-grid th {
            background-color: #f2f2f2;
            text-align: center;
            text-transform: uppercase;
            font-size: 11px;
        }
        /* Mengatur area isi agar memiliki ruang yang rapi */
        .content-cell {
            height: 120px; /* Tinggi minimal untuk ruang isi disposisi & catatan */
        }
        .disposisi-item {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .disposisi-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .box-paraf {
            height: 60px; /* Ruang untuk tanda tangan/paraf */
        }
        .btn-print {
            text-align: center;
            margin-top: 20px;
        }
        .btn-print button {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-print button:hover {
            background-color: #1d4ed8;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .page-container {
                box-shadow: none;
                padding: 10px;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="page-container">
        <!-- KOP / JUDUL LEMBAR DISPOSISI -->
        <div class="header-surat">
            <h2>PEMERINTAHAN / INSTANSI ANDA</h2>
            <h3>LEMBAR DISPOSISI SURAT MASUK</h3>
        </div>

        <!-- INFORMASI SURAT -->
        @php
            $tglDiterima = $suratMasuk->tanggal_diterima ?? $suratMasuk->tgl_diterima ?? $suratMasuk->created_at ?? null;
            $namaInstansi = $suratMasuk->instansi->nama_instansi ?? $suratMasuk->nama_instansi ?? $suratMasuk->pengirim ?? '-';
        @endphp

        <table class="table-info">
            <tr>
                <td class="label">Nomor Agenda</td>
                <td>: {{ $suratMasuk->nomor_agenda ?? $suratMasuk->no_agenda ?? '-' }}</td>
                <td class="label">Tanggal Diterima</td>
                <td>: {{ $tglDiterima ? \Carbon\Carbon::parse($tglDiterima)->format('d-m-Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Surat</td>
                <td>: {{ $suratMasuk->nomor_surat ?? $suratMasuk->no_surat ?? '-' }}</td>
                <td class="label">Sifat Surat</td>
                <td>: 
                    <span style="font-weight: bold; text-transform: uppercase;">
                        {{ $suratMasuk->sifat ?? 'Biasa' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Asal Instansi / Pengirim</td>
                <td colspan="3">: {{ $namaInstansi }}</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td colspan="3">: <strong>{{ $suratMasuk->perihal ?? '-' }}</strong></td>
            </tr>
        </table>

        <!-- KOTAK ISI DISPOSISI -->
        <table class="table-grid">
            <tr>
                <th style="width: 50%;">Diteruskan Kepada Yth:</th>
                <th style="width: 50%;">Dengan Hormat Catatan / Instruksi:</th>
            </tr>
            <!-- Baris Isi dengan tinggi yang diatur agar rapi -->
            <tr>
                <td class="content-cell">
                    @php
                        $listDispo = $suratMasuk->disposisi ?? $suratMasuk->disposisis ?? collect();
                    @endphp

                    @if($listDispo->count() > 0)
                        @foreach($listDispo as $d)
                            <div class="disposisi-item">
                                <strong>☑ {{ $d->tujuan->name ?? $d->tujuan_nama ?? 'Staf Terkait' }}</strong>
                            </div>
                        @endforeach
                    @else
                        <div style="color: #666; font-style: italic;">( Belum ada disposisi tercatat )</div>
                    @endif
                </td>
                <td class="content-cell">
                    @if($listDispo->count() > 0)
                        @foreach($listDispo as $d)
                            <div class="disposisi-item">
                                <span>{{ $d->catatan ?? '-' }}</span>
                            </div>
                        @endforeach
                    @else
                        <div style="color: #666; font-style: italic;">-</div>
                    @endif
                </td>
            </tr>
            <!-- Baris Penandatangan -->
            <tr>
                <td style="text-align: center; font-weight: bold;">Paraf Pimpinan</td>
                <td style="text-align: center; font-weight: bold;">Tanggal / Tanda Tangan</td>
            </tr>
            <tr>
                <td class="box-paraf"></td>
                <td class="box-paraf"></td>
            </tr>
        </table>

        <!-- TOMBOL CETAK -->
        <div class="btn-print">
            <button onclick="window.print()">🖨 Cetak Lembar Disposisi</button>
        </div>
    </div>

</body>
</html>