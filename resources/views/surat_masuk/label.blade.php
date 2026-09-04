<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Arsip - {{ $suratMasuk->nomor_agenda }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-h-screen;
            padding: 20px;
        }
        .label-container {
            width: 380px;
            background-color: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        /* Header Label */
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .brand-title {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #475569;
            text-transform: uppercase;
        }
        .label-title {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }
        .agenda-badge {
            background-color: #0f172a;
            color: #ffffff;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        /* Body Label Grid */
        .label-body {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: start;
        }
        .info-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }
        .info-value {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.3;
        }
        .info-value.highlight {
            color: #2563eb;
            font-family: monospace;
            font-size: 13px;
        }

        /* QR Code Box */
        .qr-box {
            text-align: center;
            border: 1px border-dashed #cbd5e1;
            padding: 4px;
            border-radius: 8px;
            background-color: #f8fafc;
        }
        .qr-box img {
            width: 75px;
            height: 75px;
            display: block;
        }
        .qr-sub {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
            font-family: monospace;
        }

        /* Footer Lokasi Arsip */
        .label-footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f1f5f9;
            padding: 8px 10px;
            border-radius: 6px;
        }
        .location-label {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }
        .location-value {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Setelan Khusus Cetak/Printer */
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .label-container {
                box-shadow: none;
                border: 2px solid #000;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label-container">
        <!-- Header -->
        <div class="label-header">
            <div>
                <p class="brand-title">E-ARSIP DIGITAL</p>
                <h1 class="label-title">LABEL SURAT MASUK</h1>
            </div>
            <div class="agenda-badge">
                #{{ $suratMasuk->nomor_agenda }}
            </div>
        </div>

        <!-- Body & Details -->
        <div class="label-body">
            <div class="info-group">
                <div class="info-item">
                    <span class="info-label">Nomor Surat</span>
                    <span class="info-value highlight">{{ $suratMasuk->nomor_surat }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Pengirim / Instansi</span>
                    <span class="info-value">{{ $suratMasuk->instansi->nama_instansi ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Perihal</span>
                    <span class="info-value">{{ $suratMasuk->perihal }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Diterima</span>
                    <span class="info-value">{{ optional($suratMasuk->tanggal_terima)->format('d-m-Y') ?? '-' }}</span>
                </div>
            </div>

            <!-- QR Code Otomatis -->
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($suratMasuk->nomor_agenda . ' - ' . $suratMasuk->nomor_surat) }}" alt="QR Code Agenda">
                <p class="qr-sub">{{ $suratMasuk->kategori->nama_kategori ?? 'Umum' }}</p>
            </div>
        </div>

        <!-- Footer Lokasi Arsip Fisik -->
        <div class="label-footer">
            <span class="location-label">📍 Lokasi Fisik:</span>
            <span class="location-value">{{ $suratMasuk->lokasi_arsip_fisik ?? 'Belum Diarsip' }}</span>
        </div>
    </div>

</body>
</html>