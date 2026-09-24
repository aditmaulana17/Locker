@extends('layouts.app')

@section('title', 'Disposisi Surat')

@section('content')
@php
    use Illuminate\Support\Carbon;

    $user = auth()->user();
    $userRole = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
    if ($userRole === 'staff') {
        $userRole = 'staf';
    }

    $canManage = in_array($userRole, ['admin', 'pimpinan'], true);

    $statusOptions = [
        'menunggu' => 'Menunggu',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
    ];

    $statusStyles = [
        'menunggu' => [
            'accent' => '#f59e0b',
            'soft' => '#fffbeb',
            'text' => '#b45309',
            'label' => 'Menunggu',
        ],
        'diproses' => [
            'accent' => '#2563eb',
            'soft' => '#eff6ff',
            'text' => '#1d4ed8',
            'label' => 'Diproses',
        ],
        'selesai' => [
            'accent' => '#059669',
            'soft' => '#ecfdf5',
            'text' => '#047857',
            'label' => 'Selesai',
        ],
    ];

    $rawStatuses = request('status', []);
    if (is_scalar($rawStatuses) && trim((string) $rawStatuses) !== '') {
        $rawStatuses = [$rawStatuses];
    }
    if (!is_array($rawStatuses)) {
        $rawStatuses = [];
    }

    $selectedStatus = collect($rawStatuses)
        ->flatten()
        ->filter(fn ($status) => is_scalar($status))
        ->map(fn ($status) => strtolower(trim((string) $status)))
        ->filter(fn ($status) => array_key_exists($status, $statusOptions))
        ->unique()
        ->values()
        ->all();

    $dariTanggal = request('dari_tanggal');
    $sampaiTanggal = request('sampai_tanggal');

    $hasFilters = request()->filled('search')
        || !empty($selectedStatus)
        || request()->filled('dari_tanggal')
        || request()->filled('sampai_tanggal');

    $formatDate = static function ($value, string $fallback = '-') {
        if (!$value) {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->format('d M Y');
        } catch (Throwable $e) {
            return $fallback;
        }
    };

    $getNomorSurat = static function ($disposisi) {
        return data_get($disposisi, 'suratMasuk.nomor_surat')
            ?? data_get($disposisi, 'surat_masuk.nomor_surat')
            ?? data_get($disposisi, 'surat.nomor_surat')
            ?? data_get($disposisi, 'nomor_surat')
            ?? '-';
    };

    $getPenerima = static function ($disposisi) {
        $penerima = data_get($disposisi, 'kepada');

        if (!$penerima) {
            return ['nama' => '-', 'jabatan' => null];
        }

        return [
            'nama' => $penerima->name ?? $penerima->nama ?? '-',
            'jabatan' => $penerima->jabatan ?? null,
        ];
    };

    $getInstruksi = static function ($disposisi) {
        $value = data_get($disposisi, 'isi_disposisi')
            ?? data_get($disposisi, 'instruksi')
            ?? data_get($disposisi, 'isi_instruksi')
            ?? data_get($disposisi, 'catatan')
            ?? '-';

        return trim((string) $value) !== '' ? trim((string) $value) : '-';
    };

    $getTanggalDisposisi = static function ($disposisi) use ($formatDate) {
        return $formatDate(
            data_get($disposisi, 'tanggal_disposisi')
                ?? data_get($disposisi, 'created_at')
        );
    };

    $getBatasWaktu = static function ($disposisi) use ($formatDate) {
        return $formatDate(
            data_get($disposisi, 'batas_waktu')
                ?? data_get($disposisi, 'tanggal_batas')
        );
    };

    $totalDisposisi = (int) ($totalDisposisi ?? 0);
    $disposisiMenunggu = (int) ($disposisiMenunggu ?? 0);
    $disposisiDiproses = (int) ($disposisiDiproses ?? 0);
    $disposisiSelesai = (int) ($disposisiSelesai ?? 0);

    $exportFilters = request()->query();

    $boardCollections = collect($boardDisposisis ?? []);
    if ($boardCollections->isEmpty() && isset($disposisis)) {
        $boardCollections = collect($disposisis)->groupBy(function ($item) use ($statusOptions) {
            $status = strtolower(trim((string) ($item->status ?? 'menunggu')));
            return in_array($status, array_keys($statusOptions), true) ? $status : 'menunggu';
        });
    }
@endphp

@push('styles')
<style>
.disposition-page{min-width:0;color:#172033;}
.disposition-header{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:16px;}
.disposition-header-copy{min-width:0;}
.disposition-kicker{display:inline-flex;align-items:center;gap:7px;margin-bottom:6px;color:#2563eb;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;}
.disposition-kicker-dot{width:7px;height:7px;border-radius:999px;background:#2563eb;}
.disposition-title{margin:0;color:#172554;font-size:28px;font-weight:800;line-height:1.1;letter-spacing:-.025em;}
.disposition-subtitle{margin-top:5px;color:#64748b;font-size:12px;line-height:1.5;}
.disposition-header-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;}
.disposition-action{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:40px;padding:0 13px;border-radius:11px;font-size:11px;font-weight:800;text-decoration:none;transition:.15s ease;white-space:nowrap;}
.disposition-action.secondary{border:1px solid #dbe4f0;background:#fff;color:#475569;box-shadow:0 2px 8px rgba(15,23,42,.03);}
.disposition-action.secondary:hover{border-color:#bfdbfe;background:#eff6ff;color:#2563eb;transform:translateY(-1px);}
.disposition-action.primary{border:1px solid #2563eb;background:#2563eb;color:#fff;box-shadow:0 8px 20px rgba(37,99,235,.18);}
.disposition-action.primary:hover{border-color:#1d4ed8;background:#1d4ed8;transform:translateY(-1px);}

.disposition-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px;}
.disposition-stat{position:relative;min-width:0;overflow:hidden;padding:16px 17px;border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 4px 16px rgba(15,23,42,.04);transition:.15s ease;}
.disposition-stat:hover{transform:translateY(-1px);border-color:#cbd5e1;box-shadow:0 9px 22px rgba(15,23,42,.06);}
.disposition-stat::before{content:'';position:absolute;left:0;top:13px;bottom:13px;width:3px;border-radius:0 6px 6px 0;background:#2563eb;}
.disposition-stat.waiting::before{background:#f59e0b;}
.disposition-stat.process::before{background:#2563eb;}
.disposition-stat.done::before{background:#059669;}
.disposition-stat-top{display:flex;align-items:center;justify-content:space-between;gap:10px;}
.disposition-stat-label{margin:0;color:#64748b;font-size:10px;font-weight:800;}
.disposition-stat-icon{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:11px;background:#eff6ff;color:#2563eb;}
.disposition-stat-icon.waiting{background:#fffbeb;color:#d97706;}
.disposition-stat-icon.process{background:#eff6ff;color:#2563eb;}
.disposition-stat-icon.done{background:#ecfdf5;color:#059669;}
.disposition-stat-value{margin:12px 0 0;color:#0f172a;font-size:25px;font-weight:900;line-height:1;}
.disposition-stat-note{margin-top:6px;color:#94a3b8;font-size:9px;line-height:1.35;}

.disposition-panel{overflow:visible;border:1px solid #dbe4f0;border-radius:18px;background:#fff;box-shadow:0 4px 16px rgba(15,23,42,.04);}
.disposition-filter{padding:14px 15px 12px;border-bottom:1px solid #edf2f7;}
.disposition-filter-grid{display:grid;grid-template-columns:minmax(0,1fr) 180px 180px auto;gap:8px;align-items:center;}
.disposition-search,.disposition-date{width:100%;height:42px;border:1px solid #d6e0ec;border-radius:11px;outline:none;background:#fff;color:#334155;font-size:11px;transition:.15s ease;}
.disposition-search{padding:0 12px 0 38px;}
.disposition-date{padding:0 12px;}
.disposition-search::placeholder,.disposition-date::placeholder{color:#94a3b8;}
.disposition-search:focus,.disposition-date:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.08);}
.disposition-search-wrap{position:relative;}
.disposition-search-icon{position:absolute;left:12px;top:50%;color:#94a3b8;transform:translateY(-50%);pointer-events:none;}
.disposition-filter-actions{display:flex;align-items:center;gap:6px;}
.disposition-filter-button{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-width:92px;height:42px;padding:0 13px;border:1px solid #2563eb;border-radius:11px;background:#2563eb;color:#fff;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 5px 14px rgba(37,99,235,.14);}
.disposition-filter-reset{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border:1px solid #d6e0ec;border-radius:11px;background:#fff;color:#64748b;text-decoration:none;}
.disposition-filter-reset:hover{border-color:#fecdd3;background:#fff1f2;color:#e11d48;}

.disposition-filter-bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:9px;}
.disposition-filter-label{color:#64748b;font-size:9px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;}
.disposition-status-chips{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.disposition-status-chip{display:inline-flex;align-items:center;gap:6px;min-height:30px;padding:0 10px;border:1px solid #e2e8f0;border-radius:999px;background:#fff;color:#64748b;font-size:9px;font-weight:800;text-decoration:none;transition:.15s ease;}
.disposition-status-chip:hover{border-color:#bfdbfe;background:#eff6ff;color:#2563eb;}
.disposition-status-chip.active{border-color:#2563eb;background:#eff6ff;color:#1d4ed8;}
.disposition-status-dot{width:7px;height:7px;border-radius:999px;}
.disposition-status-dot.waiting{background:#f59e0b;}.disposition-status-dot.process{background:#2563eb;}.disposition-status-dot.done{background:#059669;}
.disposition-active-note{color:#94a3b8;font-size:9px;white-space:nowrap;}

.disposition-board{padding:14px;}
.disposition-board-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;}
.disposition-board-title{margin:0;color:#172033;font-size:14px;font-weight:900;}
.disposition-board-subtitle{margin-top:2px;color:#94a3b8;font-size:9px;}
.disposition-board-sort{display:inline-flex;align-items:center;gap:6px;min-height:30px;padding:0 9px;border:1px solid #dbe4f0;border-radius:9px;background:#fff;color:#64748b;font-size:9px;font-weight:800;}
.disposition-columns{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;align-items:start;}
.disposition-column{min-width:0;padding:10px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc;}
.disposition-column.waiting{background:linear-gradient(180deg,#fffbeb 0,#fff 180px);}
.disposition-column.process{background:linear-gradient(180deg,#eff6ff 0,#fff 180px);}
.disposition-column.done{background:linear-gradient(180deg,#ecfdf5 0,#fff 180px);}
.disposition-column-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:9px;}
.disposition-column-title{display:flex;align-items:center;gap:7px;color:#334155;font-size:10px;font-weight:900;}
.disposition-column-count{display:inline-flex;align-items:center;justify-content:center;min-width:23px;height:21px;padding:0 7px;border-radius:999px;background:#fff;color:#475569;font-size:8px;font-weight:900;border:1px solid #e2e8f0;}
.disposition-cards{display:flex;flex-direction:column;gap:8px;}
.disposition-card{position:relative;display:block;min-width:0;padding:12px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;box-shadow:0 2px 10px rgba(15,23,42,.035);text-decoration:none;color:inherit;transition:.15s ease;}
.disposition-card:hover{border-color:#bfdbfe;box-shadow:0 8px 18px rgba(15,23,42,.07);transform:translateY(-1px);}
.disposition-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;}
.disposition-card-number{overflow:hidden;color:#64748b;font-size:8px;font-weight:800;letter-spacing:.03em;text-overflow:ellipsis;white-space:nowrap;}
.disposition-card-menu{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:7px;color:#94a3b8;flex:none;}
.disposition-card-title{margin-top:5px;overflow:hidden;color:#1e293b;font-size:11px;font-weight:900;line-height:1.35;text-overflow:ellipsis;white-space:nowrap;}
.disposition-card-recipient{margin-top:4px;display:flex;align-items:center;gap:6px;min-width:0;color:#475569;font-size:9px;font-weight:700;}
.disposition-card-recipient span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.disposition-card-instruction{margin-top:8px;display:-webkit-box;overflow:hidden;color:#64748b;font-size:9px;line-height:1.45;-webkit-line-clamp:2;-webkit-box-orient:vertical;}
.disposition-card-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:10px;padding-top:8px;border-top:1px solid #f1f5f9;}
.disposition-card-meta{display:flex;align-items:center;gap:9px;min-width:0;color:#94a3b8;font-size:8px;font-weight:700;}
.disposition-card-meta span{display:inline-flex;align-items:center;gap:4px;min-width:0;}
.disposition-card-meta .overdue{color:#e11d48;}
.disposition-status{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;font-size:8px;font-weight:900;white-space:nowrap;}
.disposition-status.waiting{background:#fffbeb;color:#b45309;}
.disposition-status.process{background:#eff6ff;color:#1d4ed8;}
.disposition-status.done{background:#ecfdf5;color:#047857;}
.disposition-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:185px;padding:18px;text-align:center;border:1px dashed #dbe4f0;border-radius:12px;background:rgba(255,255,255,.7);}
.disposition-empty-icon{display:flex;align-items:center;justify-content:center;width:42px;height:42px;margin-bottom:9px;border-radius:12px;background:#fff;color:#94a3b8;border:1px solid #e2e8f0;}
.disposition-empty-title{color:#475569;font-size:10px;font-weight:800;}
.disposition-empty-text{margin-top:3px;color:#94a3b8;font-size:8.5px;line-height:1.4;}
.disposition-pagination{padding:11px 14px;border-top:1px solid #edf2f7;}

@media(max-width:1050px){
    .disposition-header{align-items:flex-start;flex-direction:column;}
    .disposition-header-actions{width:100%;}
    .disposition-filter-grid{grid-template-columns:minmax(0,1fr) 160px 160px auto;}
    .disposition-columns{grid-template-columns:repeat(2,minmax(0,1fr));}
    .disposition-column.done{grid-column:1/-1;}
}
@media(max-width:780px){
    .disposition-title{font-size:24px;}
    .disposition-header-actions{display:grid;grid-template-columns:1fr 1fr;width:100%;}
    .disposition-action{width:100%;}
    .disposition-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;}
    .disposition-stat{padding:13px;min-height:92px;}
    .disposition-stat-value{font-size:22px;}
    .disposition-filter-grid{grid-template-columns:1fr 1fr;}
    .disposition-search-wrap{grid-column:1/-1;}
    .disposition-filter-actions{grid-column:1/-1;}
    .disposition-filter-button{flex:1;}
    .disposition-filter-bottom{align-items:flex-start;flex-direction:column;}
    .disposition-columns{grid-template-columns:1fr;}
    .disposition-column.done{grid-column:auto;}
}
@media(max-width:480px){
    .disposition-header-actions{grid-template-columns:1fr;}
    .disposition-stats{grid-template-columns:1fr 1fr;}
    .disposition-filter-grid{grid-template-columns:1fr;}
    .disposition-search-wrap,.disposition-filter-actions{grid-column:auto;}
    .disposition-status-chips{display:grid;grid-template-columns:1fr 1fr;width:100%;}
    .disposition-status-chip{justify-content:center;}
}
</style>
@endpush

<div class="disposition-page space-y-4">
    <header class="disposition-header">
        <div class="disposition-header-copy">
            <div class="disposition-kicker"><span class="disposition-kicker-dot"></span>Alur Tindak Lanjut</div>
            <h1 class="disposition-title">Disposisi Surat</h1>
            <p class="disposition-subtitle">Pantau instruksi, penerima, dan progres tindak lanjut surat dalam satu tempat.</p>
        </div>

        <div class="disposition-header-actions">
            @if(Route::has('export.disposisi.excel'))
                <a href="{{ route('export.disposisi.excel', $exportFilters) }}" class="disposition-action secondary" title="Export Excel">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h10v18H7zM9 7h6M9 11h6M9 15h4"/></svg>
                    Excel
                </a>
            @endif

            @if(Route::has('export.disposisi.pdf'))
                <a href="{{ route('export.disposisi.pdf', $exportFilters) }}" target="_blank" rel="noopener noreferrer" class="disposition-action secondary" title="Export PDF">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h7l4 4v14H6V3h1zm7 0v5h5M9 13h6M9 16h5"/></svg>
                    PDF
                </a>
            @endif

            @if($canManage && Route::has('surat-masuk.index'))
                <a href="{{ route('surat-masuk.index') }}" class="disposition-action primary" title="Pilih surat masuk untuk membuat disposisi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Disposisi Baru
                </a>
            @endif
        </div>
    </header>

    <section class="disposition-stats" aria-label="Statistik disposisi">
        <a href="{{ route('disposisi.index') }}" class="disposition-stat">
            <div class="disposition-stat-top">
                <p class="disposition-stat-label">Total Disposisi</p>
                <div class="disposition-stat-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2zM8 9h8M8 13h8M8 17h5"/></svg>
                </div>
            </div>
            <div class="disposition-stat-value">{{ number_format($totalDisposisi, 0, ',', '.') }}</div>
            <div class="disposition-stat-note">Seluruh disposisi yang dapat Anda akses</div>
        </a>

        <a href="{{ route('disposisi.index', array_merge(request()->except(['status','page']), ['status' => ['menunggu']])) }}" class="disposition-stat waiting">
            <div class="disposition-stat-top">
                <p class="disposition-stat-label">Menunggu</p>
                <div class="disposition-stat-icon waiting">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M12 7.5v5l3 2"/></svg>
                </div>
            </div>
            <div class="disposition-stat-value">{{ number_format($disposisiMenunggu, 0, ',', '.') }}</div>
            <div class="disposition-stat-note">Perlu segera ditindaklanjuti</div>
        </a>

        <a href="{{ route('disposisi.index', array_merge(request()->except(['status','page']), ['status' => ['diproses']])) }}" class="disposition-stat process">
            <div class="disposition-stat-top">
                <p class="disposition-stat-label">Diproses</p>
                <div class="disposition-stat-icon process">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M12 8v4l2.5 2"/></svg>
                </div>
            </div>
            <div class="disposition-stat-value">{{ number_format($disposisiDiproses, 0, ',', '.') }}</div>
            <div class="disposition-stat-note">Sedang dikerjakan oleh penerima</div>
        </a>

        <a href="{{ route('disposisi.index', array_merge(request()->except(['status','page']), ['status' => ['selesai']])) }}" class="disposition-stat done">
            <div class="disposition-stat-top">
                <p class="disposition-stat-label">Selesai</p>
                <div class="disposition-stat-icon done">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.4 2.4 4.7-5"/></svg>
                </div>
            </div>
            <div class="disposition-stat-value">{{ number_format($disposisiSelesai, 0, ',', '.') }}</div>
            <div class="disposition-stat-note">Tindak lanjut telah dituntaskan</div>
        </a>
    </section>

    <section class="disposition-panel">
        <div class="disposition-filter">
            <form id="filterForm" method="GET" action="{{ route('disposisi.index') }}">
                <div class="disposition-filter-grid">
                    <div class="disposition-search-wrap">
                        <span class="disposition-search-icon"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/></svg></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="disposition-search" placeholder="Cari nomor surat, penerima, atau instruksi..." autocomplete="off">
                    </div>
                    <input type="date" name="dari_tanggal" value="{{ $dariTanggal }}" class="disposition-date" aria-label="Tanggal mulai">
                    <input type="date" name="sampai_tanggal" value="{{ $sampaiTanggal }}" class="disposition-date" aria-label="Tanggal akhir">
                    <div class="disposition-filter-actions">
                        <button type="submit" class="disposition-filter-button">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707v3.414L13 17v4l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Filter
                        </button>
                        @if($hasFilters)
                            <a href="{{ route('disposisi.index') }}" class="disposition-filter-reset" title="Reset Filter" aria-label="Reset Filter">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="disposition-filter-bottom">
                    <div>
                        <div class="disposition-filter-label">Status</div>
                        <div class="disposition-status-chips mt-1.5">
                            <a href="{{ route('disposisi.index', request()->except(['status', 'page'])) }}" class="disposition-status-chip {{ empty($selectedStatus) ? 'active' : '' }}">Semua</a>
                            @foreach($statusOptions as $value => $label)
                                <a href="{{ route('disposisi.index', array_merge(request()->except(['status', 'page']), ['status' => [$value]])) }}" class="disposition-status-chip {{ in_array($value, $selectedStatus, true) ? 'active' : '' }}">
                                    <span class="disposition-status-dot {{ $value === 'menunggu' ? 'waiting' : ($value === 'diproses' ? 'process' : 'done') }}"></span>
                                    {{ $label }}
                                    @if($value === 'menunggu') ({{ $disposisiMenunggu }}) @elseif($value === 'diproses') ({{ $disposisiDiproses }}) @else ({{ $disposisiSelesai }}) @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @if($hasFilters)
                        <div class="disposition-active-note">Filter aktif • hasil mengikuti parameter yang dipilih</div>
                    @endif
                </div>
            </form>
        </div>

        <div class="disposition-board">
            <div class="disposition-board-head">
                <div>
                    <h2 class="disposition-board-title">Daftar Disposisi</h2>
                    <p class="disposition-board-subtitle">Kelompok tugas berdasarkan status agar mudah dipantau dan ditindaklanjuti.</p>
                </div>
                <div class="disposition-board-sort">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h12M8 12h8M8 18h5M4 6v12m0 0l-2-2m2 2l2-2"/></svg>
                    Terbaru
                </div>
            </div>

            <div class="disposition-columns">
                @foreach(['menunggu', 'diproses', 'selesai'] as $columnStatus)
                    @php
                        $items = $boardCollections->get($columnStatus, collect());
                        $style = $statusStyles[$columnStatus];
                    @endphp

                    <section class="disposition-column {{ $columnStatus === 'menunggu' ? 'waiting' : ($columnStatus === 'diproses' ? 'process' : 'done') }}">
                        <div class="disposition-column-head">
                            <div class="disposition-column-title">
                                <span style="width:8px;height:8px;border-radius:999px;background:{{ $style['accent'] }};"></span>
                                {{ $style['label'] }}
                            </div>
                            <span class="disposition-column-count">{{ $items->count() }}</span>
                        </div>

                        <div class="disposition-cards">
                            @forelse($items as $d)
                                @php
                                    $status = strtolower(trim((string) ($d->status ?? 'menunggu')));
                                    $nomorSurat = $getNomorSurat($d);
                                    $nomorSurat = trim((string) $nomorSurat) !== '' ? trim((string) $nomorSurat) : '-';
                                    $penerima = $getPenerima($d);
                                    $instruksi = $getInstruksi($d);
                                    $tanggalDisposisi = $getTanggalDisposisi($d);
                                    $batasWaktu = $getBatasWaktu($d);
                                    $rawBatasWaktu = data_get($d, 'batas_waktu') ?? data_get($d, 'tanggal_batas');
                                    $isLate = false;
                                    if ($rawBatasWaktu) {
                                        try {
                                            $isLate = Carbon::parse($rawBatasWaktu)->isPast() && $status !== 'selesai';
                                        } catch (Throwable $e) {
                                            $isLate = false;
                                        }
                                    }
                                    $suratMasuk = data_get($d, 'suratMasuk') ?? data_get($d, 'surat_masuk') ?? data_get($d, 'surat');
                                    $perihal = trim((string) data_get($suratMasuk, 'perihal', ''));
                                    $perihal = $perihal !== '' ? $perihal : 'Tanpa perihal';
                                @endphp

                                <article class="disposition-card">
                                    <div class="disposition-card-top">
                                        <span class="disposition-card-number">No. {{ $nomorSurat }}</span>
                                    </div>

                                    <h3 class="disposition-card-title" title="{{ $perihal }}">{{ $perihal }}</h3>

                                    <div class="disposition-card-recipient">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-8 0v2M12 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                                        <span title="{{ $penerima['nama'] }}">{{ $penerima['nama'] }}</span>
                                    </div>

                                    <div class="disposition-card-instruction" title="{{ $instruksi }}">{{ $instruksi }}</div>

                                    <div class="disposition-card-footer">
                                        <div class="disposition-card-meta">
                                            <span><svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"/></svg>{{ $tanggalDisposisi }}</span>
                                            @if($batasWaktu !== '-')
                                                <span class="{{ $isLate ? 'overdue' : '' }}"><svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" d="M12 7.5v5l3 2"/></svg>{{ $batasWaktu }}</span>
                                            @endif
                                        </div>
                                        <span class="disposition-status {{ $columnStatus === 'menunggu' ? 'waiting' : ($columnStatus === 'diproses' ? 'process' : 'done') }}">
                                            <span style="width:6px;height:6px;border-radius:999px;background:{{ $style['accent'] }};"></span>
                                            {{ $style['label'] }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-2">
                                        @if($suratMasuk && Route::has('surat-masuk.show'))
                                            <a href="{{ route('surat-masuk.show', $suratMasuk) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[8px] font-bold text-slate-400 hover:text-blue-600">Lihat surat <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                                        @else
                                            <span></span>
                                        @endif

                                        <div class="flex items-center gap-1">
                                            @if(Route::has('disposisi.show'))
                                                <a href="{{ route('disposisi.show', $d) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600" title="Lihat detail" aria-label="Lihat detail">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                                </a>
                                            @endif
                                            @if($canManage && Route::has('disposisi.edit'))
                                                <a href="{{ route('disposisi.edit', $d) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600" title="Ubah disposisi" aria-label="Ubah disposisi">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                            @endif
                                            @if($canManage && Route::has('disposisi.destroy'))
                                                <form action="{{ route('disposisi.destroy', $d) }}" method="POST" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 delete-btn" title="Hapus disposisi" aria-label="Hapus disposisi">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12m-8 4v6m4-6v6M9 7V4h6v3m-9 0l1 13h10l1-13"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="disposition-empty">
                                    <div class="disposition-empty-icon"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 3h8l3 3v15H5V3h3zm0 7h8M8 14h8M8 17h5"/></svg></div>
                                    <div class="disposition-empty-title">Tidak ada disposisi {{ strtolower($style['label']) }}</div>
                                    <div class="disposition-empty-text">Belum ada tugas dengan status ini pada hasil yang sedang ditampilkan.</div>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

        @php
            $boardVisibleCount = $boardCollections->flatten(1)->count();
        @endphp
        @if($boardVisibleCount > 0)
            <div class="disposition-pagination flex items-center justify-between gap-3">
                <span>Menampilkan {{ number_format($boardVisibleCount, 0, ',', '.') }} disposisi pada hasil saat ini.</span>
                @if($hasFilters)
                    <a href="{{ route('disposisi.index') }}">Tampilkan semua</a>
                @endif
            </div>
        @endif
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
    'use strict';

    const form = document.getElementById('filterForm');
    const from = document.querySelector('input[name="dari_tanggal"]');
    const to = document.querySelector('input[name="sampai_tanggal"]');

    form?.addEventListener('submit', function(event){
        if(from?.value && to?.value && from.value > to.value){
            event.preventDefault();
            if(typeof Swal !== 'undefined'){
                Swal.fire({
                    icon:'warning',
                    title:'Rentang tanggal tidak valid',
                    text:'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                    confirmButtonText:'Mengerti',
                    confirmButtonColor:'#2563eb'
                });
            }else{
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
            }
        }
    });

    document.querySelectorAll('.delete-btn').forEach(function(button){
        button.addEventListener('click', function(event){
            event.preventDefault();
            const targetForm = this.closest('.delete-form');
            if(!targetForm) return;

            if(typeof Swal !== 'undefined'){
                Swal.fire({
                    title:'Hapus Disposisi?',
                    text:'Data disposisi akan dipindahkan ke tempat sampah.',
                    icon:'warning',
                    showCancelButton:true,
                    confirmButtonColor:'#ef4444',
                    cancelButtonColor:'#64748b',
                    confirmButtonText:'Ya, Hapus!',
                    cancelButtonText:'Batal',
                    reverseButtons:true
                }).then(function(result){
                    if(result.isConfirmed) targetForm.submit();
                });
            }else if(window.confirm('Yakin ingin menghapus disposisi ini?')){
                targetForm.submit();
            }
        });
    });
})();
</script>
@endpush
@endsection
