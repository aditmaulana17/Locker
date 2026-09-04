<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Laporan Surat Masuk</title>
<style>
    body{ font-family: sans-serif; font-size: 11px; }
    h2{ text-align:center; margin-bottom:4px; }
    p.sub{ text-align:center; color:#666; margin-top:0; }
    table{ width:100%; border-collapse: collapse; margin-top:15px; }
    th,td{ border:1px solid #999; padding:5px 8px; text-align:left; }
    th{ background:#eee; }
</style>
</head>
<body>
    <h2>LAPORAN SURAT MASUK</h2>
    <p class="sub">Dicetak pada {{ now()->format('d-m-Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Agenda</th>
                <th>No. Surat</th>
                <th>Tgl Surat</th>
                <th>Tgl Terima</th>
                <th>Pengirim</th> <!-- Diubah dari Instansi -->
                <th>Perihal</th>
                <th>Kategori</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suratMasuks as $i => $s)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $s->nomor_agenda }}</td>
                <td>{{ $s->nomor_surat }}</td>
                <td>{{ $s->tanggal_surat->format('d-m-Y') }}</td>
                <td>{{ $s->tanggal_terima->format('d-m-Y') }}</td>
                <td>{{ $s->pengirim }}</td> <!-- Diubah dari $s->instansi->nama_instansi -->
                <td>{{ $s->perihal }}</td>
                <td>{{ $s->kategori->nama_kategori }}</td>
                <td>{{ ucfirst($s->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>