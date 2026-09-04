<?php $__env->startSection('title', 'Surat Keluar'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $userRole = strtolower(auth()->user()->role ?? auth()->user()->jabatan ?? '');
    $userRole = $userRole === 'staff' ? 'staf' : $userRole;
    $selectedKategori = collect(request('kategori_id', []))->filter(fn($id) => is_scalar($id))->map(fn($id) => (string)$id)->unique()->values()->all();
    $selectedStatus = collect(request('status', []))->filter(fn($status) => is_scalar($status))->map(fn($status) => strtolower(trim((string)$status)))->unique()->values()->all();
    $statusOptions = [
        'draf' => 'Draf',
        'diproses' => 'Diproses',
        'disetujui' => 'Disetujui',
        'dikirim' => 'Dikirim',
        'diarsipkan' => 'Diarsipkan',
    ];
    $statusBadgeClasses = [
        'draf' => 'bg-slate-50 text-slate-700 border-slate-200',
        'diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
        'disetujui' => 'bg-blue-50 text-blue-700 border-blue-200',
        'dikirim' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'diarsipkan' => 'bg-purple-50 text-purple-700 border-purple-200',
    ];
    $dariTanggal = request('dari_tanggal');
    $sampaiTanggal = request('sampai_tanggal');
    $hasFilters = request()->filled('search') || request()->filled('kategori_id') || request()->filled('status') || request()->filled('dari_tanggal') || request()->filled('sampai_tanggal');
?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
.date-range-input{cursor:pointer}.date-range-input::selection{background:transparent}
.daterangepicker{z-index:99999!important;width:auto;border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 20px 50px rgba(15,23,42,.14),0 8px 20px rgba(15,23,42,.06);font-family:inherit;padding:10px}
.daterangepicker .calendar-table{border:0;background:transparent}.daterangepicker .calendar-table table{border-collapse:separate;border-spacing:2px}
.daterangepicker .calendar-table th{color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase}
.daterangepicker .calendar-table td{width:34px;height:34px;border-radius:10px;color:#475569;font-size:11px;font-weight:500;transition:background-color .15s ease,color .15s ease}
.daterangepicker td.available:hover,.daterangepicker th.available:hover{background:#eff6ff;color:#2563eb}
.daterangepicker td.in-range{background:#eff6ff;color:#2563eb}
.daterangepicker td.active,.daterangepicker td.active:hover{background:#2563eb;color:#fff;border-radius:999px}
.daterangepicker td.start-date{border-radius:999px 0 0 999px}.daterangepicker td.end-date{border-radius:0 999px 999px 0}.daterangepicker td.start-date.end-date{border-radius:999px}
.daterangepicker td.off,.daterangepicker td.off.in-range{background:transparent;color:#cbd5e1}
.daterangepicker .prev,.daterangepicker .next{border-radius:8px;color:#64748b;transition:background-color .15s ease,color .15s ease}
.daterangepicker .prev:hover,.daterangepicker .next:hover{background:#f1f5f9;color:#2563eb}
.daterangepicker select.monthselect,.daterangepicker select.yearselect{width:auto;border:0;border-radius:8px;background:#f8fafc;color:#334155;font-size:12px;font-weight:700;padding:5px 8px;outline:none;cursor:pointer}
.daterangepicker select.monthselect:focus,.daterangepicker select.yearselect:focus{background:#eff6ff;color:#2563eb}
.daterangepicker .drp-buttons{border-top:1px solid #e2e8f0;margin-top:8px;padding:10px 5px 2px}
.daterangepicker .drp-selected{color:#64748b;font-size:11px;font-weight:600}
.daterangepicker .applyBtn{border:0;border-radius:10px;background:#2563eb;color:#fff;font-size:11px;font-weight:700;padding:8px 14px;transition:background-color .15s ease}
.daterangepicker .applyBtn:hover{background:#1d4ed8}
.daterangepicker .cancelBtn{border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#475569;font-size:11px;font-weight:700;padding:8px 14px;transition:background-color .15s ease,color .15s ease}
.daterangepicker .cancelBtn:hover{background:#f1f5f9;color:#334155}
@media(max-width:767px){
    .daterangepicker{position:fixed!important;top:50%!important;left:50%!important;right:auto!important;bottom:auto!important;width:calc(100vw - 20px)!important;max-width:420px;max-height:calc(100vh - 30px);overflow-y:auto;transform:translate(-50%,-50%)}
    .daterangepicker .calendar{float:none!important;width:100%!important;max-width:100%!important;margin:0!important}
    .daterangepicker .calendar.right{margin-top:10px!important}
    .daterangepicker .calendar-table td{width:36px;height:36px}
    body.daterangepicker-open{overflow:hidden}
}
</style>
<?php $__env->stopPush(); ?>

<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">Surat Keluar</h1>
            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Kelola dan pantau seluruh arsip surat keluar organisasi Anda.</p>
        </div>
        <div class="grid w-full grid-cols-3 gap-2 sm:flex sm:w-auto">
            <a href="<?php echo e(route('export.surat-keluar.excel', request()->query())); ?>" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 sm:px-3.5">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Excel</span>
            </a>
            <a href="<?php echo e(route('export.surat-keluar.pdf', request()->query())); ?>" target="_blank" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-2 text-xs font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100 sm:px-3.5">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>PDF</span>
            </a>
            <?php if(in_array($userRole, ['admin', 'pimpinan'])): ?>
                <a href="<?php echo e(route('surat-keluar.create')); ?>" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Surat Keluar</span>
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm sm:p-5">
        <form method="GET" action="<?php echo e(route('surat-keluar.index')); ?>" id="filterForm" class="space-y-3">
            <div class="grid grid-cols-1 gap-2.5 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <label for="search" class="sr-only">Pencarian</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari perihal, nomor surat, atau instansi..." autocomplete="off" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="text" id="date-range" name="date_range" value="" readonly autocomplete="off" placeholder="Pilih rentang tanggal..." class="date-range-input h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-10 text-xs font-medium text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
                        <button type="button" id="clearDateRange" title="Hapus tanggal" class="absolute inset-y-0 right-0 z-10 hidden w-10 items-center justify-center text-slate-400 transition hover:text-rose-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="dari_tanggal" id="dari_tanggal" value="<?php echo e($dariTanggal); ?>">
                    <input type="hidden" name="sampai_tanggal" id="sampai_tanggal" value="<?php echo e($sampaiTanggal); ?>">
                </div>

                <div class="lg:col-span-3">
                    <div class="flex h-11 gap-2">
                        <button type="submit" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:text-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707L13 17v4l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            <span>Filter</span>
                        </button>
                        <?php if($hasFilters): ?>
                            <a href="<?php echo e(route('surat-keluar.index')); ?>" title="Reset Filter" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="flex flex-col justify-between gap-2 border-b border-slate-200 bg-slate-50/80 px-3 py-2.5 sm:flex-row sm:items-center sm:px-3.5">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[11px] font-bold text-slate-700 sm:text-xs">Kategori Surat</h3>
                            <p class="text-[9px] text-slate-400 sm:text-[10px]">Pilih satu atau beberapa kategori</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pl-8 sm:pl-0">
                        <span id="kategoriCount" class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[9px] font-semibold text-blue-600"><?php echo e(count($selectedKategori)); ?> dipilih</span>
                        <button type="button" id="selectAllKategori" class="text-[10px] font-semibold text-blue-600 transition hover:text-blue-700">Pilih Semua</button>
                        <span class="text-[10px] text-slate-300">|</span>
                        <button type="button" id="clearAllKategori" class="text-[10px] font-semibold text-slate-500 transition hover:text-slate-700">Batalkan</button>
                    </div>
                </div>
                <div class="p-2.5 sm:p-3">
                    <?php if(isset($kategoris) && $kategoris->count()): ?>
                        <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            <?php $__currentLoopData = $kategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="group flex h-9 min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 transition hover:border-blue-200 hover:bg-blue-50/60">
                                    <input type="checkbox" name="kategori_id[]" value="<?php echo e($k->id); ?>" class="kategori-checkbox h-3.5 w-3.5 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-1 focus:ring-blue-500/30" <?php if(in_array((string)$k->id, $selectedKategori, true)): echo 'checked'; endif; ?>>
                                    <span class="truncate text-[11px] font-medium text-slate-700 transition group-hover:text-blue-700" title="<?php echo e($k->nama_kategori); ?>"><?php echo e($k->nama_kategori); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="rounded-lg border border-dashed border-slate-200 px-3 py-3 text-center text-[10px] text-slate-400">Belum ada kategori surat.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="flex flex-col justify-between gap-2 border-b border-slate-200 bg-slate-50/80 px-3 py-2.5 sm:flex-row sm:items-center sm:px-3.5">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-[11px] font-bold text-slate-700 sm:text-xs">Status Surat</h3>
                            <p class="text-[9px] text-slate-400 sm:text-[10px]">Pilih satu atau beberapa status</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pl-8 sm:pl-0">
                        <span id="statusCount" class="inline-flex items-center rounded-full border border-amber-100 bg-amber-50 px-2 py-0.5 text-[9px] font-semibold text-amber-600"><?php echo e(count($selectedStatus)); ?> dipilih</span>
                        <button type="button" id="selectAllStatus" class="text-[10px] font-semibold text-amber-600 transition hover:text-amber-700">Pilih Semua</button>
                        <span class="text-[10px] text-slate-300">|</span>
                        <button type="button" id="clearAllStatus" class="text-[10px] font-semibold text-slate-500 transition hover:text-slate-700">Batalkan</button>
                    </div>
                </div>
                <div class="p-2.5 sm:p-3">
                    <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="group flex h-9 min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 transition hover:border-blue-200 hover:bg-blue-50/60">
                                <input type="checkbox" name="status[]" value="<?php echo e($value); ?>" class="status-checkbox h-3.5 w-3.5 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-1 focus:ring-blue-500/30" <?php if(in_array($value, $selectedStatus, true)): echo 'checked'; endif; ?>>
                                <span class="truncate text-[11px] font-medium text-slate-700 transition group-hover:text-blue-700"><?php echo e($label); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] border-collapse whitespace-nowrap text-left text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-[11px]">
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Tanggal Keluar</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Instansi Tujuan</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Perihal</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Kategori</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Status</th>
                        <th class="px-4 py-3 text-center sm:px-6 sm:py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php $__empty_1 = true; $__currentLoopData = $suratKeluars ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $status = strtolower($s->status ?? 'draf');
                            $badgeClass = $statusBadgeClasses[$status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                        ?>
                        <tr class="transition duration-150 hover:bg-slate-50/60">
                            <td class="px-4 py-3.5 text-slate-500 sm:px-6 sm:py-4"><?php echo e(optional($s->tanggal_surat)->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-4 py-3.5 text-slate-700 sm:px-6 sm:py-4">
                                <span class="inline-block max-w-[220px] truncate rounded-lg border border-slate-200/60 bg-slate-100 px-2.5 py-1 font-medium text-slate-700" title="<?php echo e($s->instansi_tujuan ?? '-'); ?>"><?php echo e($s->instansi_tujuan ?? '-'); ?></span>
                            </td>
                            <td class="max-w-xs truncate px-4 py-3.5 font-medium text-slate-800 sm:px-6 sm:py-4" title="<?php echo e($s->perihal); ?>"><?php echo e($s->perihal ?? '-'); ?></td>
                            <td class="px-4 py-3.5 text-slate-500 sm:px-6 sm:py-4"><?php echo e($s->kategori->nama_kategori ?? '-'); ?></td>
                            <td class="px-4 py-3.5 sm:px-6 sm:py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold <?php echo e($badgeClass); ?>"><?php echo e($statusOptions[$status] ?? ucfirst($status)); ?></span>
                            </td>
                            <td class="px-4 py-3.5 text-center sm:px-6 sm:py-4">
                                <div class="inline-flex items-center gap-1">
                                    <a href="<?php echo e(route('surat-keluar.show', $s)); ?>" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600" title="Lihat Detail">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0a3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7c-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <?php if(in_array($userRole, ['admin', 'pimpinan'])): ?>
                                        <a href="<?php echo e(route('surat-keluar.edit', $s)); ?>" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600" title="Ubah Data">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="<?php echo e(route('surat-keluar.destroy', $s)); ?>" method="POST" class="delete-form inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="button" class="delete-btn rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus Surat">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 01-1-1h-4a1 1 0 01-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-10 text-center sm:py-12">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 sm:text-base">Belum ada data surat keluar</p>
                                    <p class="mt-0.5 max-w-md px-4 text-[11px] text-slate-400 sm:text-xs">Coba sesuaikan pencarian atau filter yang digunakan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(isset($suratKeluars) && method_exists($suratKeluars, 'hasPages') && $suratKeluars->hasPages()): ?>
            <div class="border-t border-slate-100 px-4 py-3 sm:px-6 sm:py-4"><?php echo e($suratKeluars->withQueryString()->links()); ?></div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function(){
    function loadScript(src){
        return new Promise(function(resolve,reject){
            const existingScript=document.querySelector('script[src="'+src+'"]');
            if(existingScript){
                if((src.includes('jquery')&&window.jQuery)||(src.includes('moment')&&window.moment)||(src.includes('daterangepicker')&&window.jQuery?.fn?.daterangepicker)){resolve();return}
                existingScript.addEventListener('load',resolve,{once:true});
                existingScript.addEventListener('error',reject,{once:true});
                return;
            }
            const script=document.createElement('script');
            script.src=src;
            script.async=false;
            script.onload=resolve;
            script.onerror=reject;
            document.head.appendChild(script);
        });
    }

    async function initializeSuratKeluarPage(){
        try{
            if(!window.jQuery) await loadScript('https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js');
            if(!window.moment) await loadScript('https://cdn.jsdelivr.net/momentjs/latest/moment.min.js');
            if(!window.jQuery.fn.daterangepicker) await loadScript('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js');
            initializePage();
        }catch(error){
            console.error('Gagal memuat komponen halaman Surat Keluar:',error);
        }
    }

    function initializePage(){
        const $=window.jQuery;
        const kategoriCheckboxes=document.querySelectorAll('.kategori-checkbox');
        const statusCheckboxes=document.querySelectorAll('.status-checkbox');
        const kategoriCount=document.getElementById('kategoriCount');
        const statusCount=document.getElementById('statusCount');

        function updateKategoriCount(){
            const count=document.querySelectorAll('.kategori-checkbox:checked').length;
            if(kategoriCount) kategoriCount.textContent=count+' dipilih';
        }

        function updateStatusCount(){
            const count=document.querySelectorAll('.status-checkbox:checked').length;
            if(statusCount) statusCount.textContent=count+' dipilih';
        }

        kategoriCheckboxes.forEach(function(checkbox){checkbox.addEventListener('change',updateKategoriCount)});
        statusCheckboxes.forEach(function(checkbox){checkbox.addEventListener('change',updateStatusCount)});

        document.getElementById('selectAllKategori')?.addEventListener('click',function(){
            kategoriCheckboxes.forEach(function(checkbox){checkbox.checked=true});
            updateKategoriCount();
        });

        document.getElementById('clearAllKategori')?.addEventListener('click',function(){
            kategoriCheckboxes.forEach(function(checkbox){checkbox.checked=false});
            updateKategoriCount();
        });

        document.getElementById('selectAllStatus')?.addEventListener('click',function(){
            statusCheckboxes.forEach(function(checkbox){checkbox.checked=true});
            updateStatusCount();
        });

        document.getElementById('clearAllStatus')?.addEventListener('click',function(){
            statusCheckboxes.forEach(function(checkbox){checkbox.checked=false});
            updateStatusCount();
        });

        const dateInput=$('#date-range');
        const dariTanggal=$('#dari_tanggal');
        const sampaiTanggal=$('#sampai_tanggal');
        const clearDateButton=$('#clearDateRange');

        if(dateInput.length&&$.fn.daterangepicker){
            const startValue=dariTanggal.val();
            const endValue=sampaiTanggal.val();
            const pickerOptions={
                autoUpdateInput:false,
                showDropdowns:true,
                minYear:2000,
                maxYear:new Date().getFullYear()+10,
                linkedCalendars:true,
                alwaysShowCalendars:true,
                autoApply:false,
                opens:'left',
                drops:'down',
                parentEl:'body',
                locale:{
                    format:'DD/MM/YYYY',
                    separator:' - ',
                    applyLabel:'Terapkan',
                    cancelLabel:'Bersihkan',
                    fromLabel:'Dari',
                    toLabel:'Sampai',
                    customRangeLabel:'Pilih Rentang',
                    weekLabel:'Mg',
                    daysOfWeek:['Mg','Sn','Sl','Rb','Km','Jm','Sb'],
                    monthNames:['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
                    firstDay:1
                }
            };

            if(startValue&&endValue){
                const startMoment=moment(startValue,'YYYY-MM-DD',true);
                const endMoment=moment(endValue,'YYYY-MM-DD',true);
                if(startMoment.isValid()&&endMoment.isValid()){
                    pickerOptions.startDate=startMoment;
                    pickerOptions.endDate=endMoment;
                }
            }

            dateInput.daterangepicker(pickerOptions);
            const picker=dateInput.data('daterangepicker');

            function updateVisibleDate(){
                const start=dariTanggal.val();
                const end=sampaiTanggal.val();
                if(start&&end){
                    const startDate=moment(start,'YYYY-MM-DD',true);
                    const endDate=moment(end,'YYYY-MM-DD',true);
                    if(startDate.isValid()&&endDate.isValid()){
                        dateInput.val(startDate.format('DD/MM/YYYY')+' - '+endDate.format('DD/MM/YYYY'));
                        clearDateButton.css('display','flex');
                    }
                }else{
                    dateInput.val('');
                    clearDateButton.hide();
                }
            }

            updateVisibleDate();

            dateInput.on('apply.daterangepicker',function(event,selectedPicker){
                const start=selectedPicker.startDate;
                const end=selectedPicker.endDate;
                dariTanggal.val(start.format('YYYY-MM-DD'));
                sampaiTanggal.val(end.format('YYYY-MM-DD'));
                dateInput.val(start.format('DD/MM/YYYY')+' - '+end.format('DD/MM/YYYY'));
                clearDateButton.css('display','flex');
            });

            dateInput.on('cancel.daterangepicker',function(){
                dariTanggal.val('');
                sampaiTanggal.val('');
                dateInput.val('');
                clearDateButton.hide();
            });

            clearDateButton.on('click',function(event){
                event.preventDefault();
                dariTanggal.val('');
                sampaiTanggal.val('');
                dateInput.val('');
                clearDateButton.hide();
                if(picker){
                    picker.setStartDate(moment());
                    picker.setEndDate(moment());
                }
            });

            dateInput.on('show.daterangepicker',function(){
                if(window.innerWidth<=767) document.body.classList.add('daterangepicker-open');
            });

            dateInput.on('hide.daterangepicker',function(){
                document.body.classList.remove('daterangepicker-open');
            });
        }

        const filterForm=document.getElementById('filterForm');

        filterForm?.addEventListener('submit',function(event){
            const start=dariTanggal.val();
            const end=sampaiTanggal.val();

            if(start&&end&&start>end){
                event.preventDefault();
                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        icon:'warning',
                        title:'Rentang tanggal tidak valid',
                        text:'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                        confirmButtonText:'Mengerti',
                        confirmButtonColor:'#2563eb',
                        customClass:{
                            popup:'rounded-2xl',
                            confirmButton:'rounded-xl text-xs font-semibold px-4 py-2.5'
                        }
                    });
                }else{
                    alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
                }
            }
        });

        document.querySelectorAll('.delete-btn').forEach(function(button){
            button.addEventListener('click',function(){
                const form=this.closest('.delete-form');

                if(typeof Swal!=='undefined'){
                    Swal.fire({
                        title:'Hapus Surat Keluar?',
                        text:'Data yang dihapus akan dipindahkan ke tempat sampah.',
                        icon:'warning',
                        showCancelButton:true,
                        confirmButtonColor:'#ef4444',
                        cancelButtonColor:'#64748b',
                        confirmButtonText:'Ya, Hapus!',
                        cancelButtonText:'Batal',
                        reverseButtons:true,
                        customClass:{
                            popup:'rounded-2xl',
                            confirmButton:'rounded-xl text-xs font-semibold px-4 py-2.5',
                            cancelButton:'rounded-xl text-xs font-semibold px-4 py-2.5'
                        }
                    }).then(function(result){
                        if(result.isConfirmed&&form) form.submit();
                    });
                }else if(form&&confirm('Yakin ingin menghapus surat keluar ini?')){
                    form.submit();
                }
            });
        });

        updateKategoriCount();
        updateStatusCount();
    }

    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded',initializeSuratKeluarPage);
    }else{
        initializeSuratKeluarPage();
    }
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/surat_keluar/index.blade.php ENDPATH**/ ?>