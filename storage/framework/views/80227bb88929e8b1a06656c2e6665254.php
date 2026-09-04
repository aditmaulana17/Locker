<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $role = strtolower(auth()->user()->role ?? '');
    $isStaf = in_array($role, ['staf', 'staff']);
?>

<div class="space-y-6 sm:space-y-8">

    <!-- WELCOME BANNER -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl sm:rounded-3xl p-5 sm:p-8 text-white shadow-xl shadow-blue-500/10">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium text-blue-100 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem E-Arsip Aktif
                </div>
                <h1 class="text-xl sm:text-3xl font-bold tracking-tight leading-snug">Selamat Datang Kembali, <?php echo e(auth()->user()->name ?? 'Pengguna'); ?>!</h1>
                <p class="text-blue-100/90 text-xs sm:text-sm max-w-xl">
                    <?php if($isStaf): ?>
                        Kelola tugas disposisi surat masuk yang telah diteruskan kepada Anda dengan cepat dan terstruktur.
                    <?php else: ?>
                        Kelola arsip surat masuk, surat keluar, dan disposisi dokumen dengan cepat, terstruktur, dan aman.
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                <?php if(!$isStaf): ?>
                    <a href="<?php echo e(route('surat-masuk.create')); ?>" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-3.5 sm:px-4 py-2.5 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-semibold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Surat Masuk</span>
                    </a>
                    <a href="<?php echo e(route('surat-keluar.create')); ?>" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-3.5 sm:px-4 py-2.5 bg-blue-500/30 hover:bg-blue-500/40 text-white border border-white/20 rounded-xl text-xs font-semibold backdrop-blur-md transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Surat Keluar</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('disposisi.index')); ?>" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-4 py-2.5 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-semibold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Daftar Disposisi Tugas Saya</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- STATS GRID CARDS -->
    <div class="grid grid-cols-2 <?php echo e(!$isStaf ? 'lg:grid-cols-2 xl:grid-cols-4' : 'lg:grid-cols-2'); ?> gap-3 sm:gap-5">
        
        <?php if(!$isStaf): ?>
            <!-- Surat Masuk (Admin/Pimpinan) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Surat Masuk</span>
                    <div class="p-2 sm:p-2.5 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo e($totalSuratMasuk ?? 0); ?></span>
                    <span class="text-[10px] sm:text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg w-max">Arsip Masuk</span>
                </div>
            </div>

            <!-- Surat Keluar (Admin/Pimpinan) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Surat Keluar</span>
                    <div class="p-2 sm:p-2.5 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo e($totalSuratKeluar ?? 0); ?></span>
                    <span class="text-[10px] sm:text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg w-max">Terkirim</span>
                </div>
            </div>

            <!-- Belum Diproses (Admin/Pimpinan) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Belum Diproses</span>
                    <div class="p-2 sm:p-2.5 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo e($suratPending ?? 0); ?></span>
                    <span class="text-[10px] sm:text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg w-max">Pending</span>
                </div>
            </div>

            <!-- Selesai Diproses (Admin/Pimpinan) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Selesai Diproses</span>
                    <div class="p-2 sm:p-2.5 rounded-xl bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo e($suratSelesai ?? 0); ?></span>
                    <span class="text-[10px] sm:text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg w-max">Selesai</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if($isStaf): ?>
            <!-- Disposisi Masuk (Khusus Staf) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Disposisi Masuk</span>
                    <div class="p-2 sm:p-2.5 rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo e($disposisiMenunggu ?? 0); ?></span>
                    <span class="text-[10px] sm:text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg w-max">Tindak Lanjut</span>
                </div>
            </div>

            <!-- Disposisi Selesai (Khusus Staf) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Disposisi Selesai</span>
                    <div class="p-2 sm:p-2.5 rounded-xl bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo e($disposisiSelesai ?? 0); ?></span>
                    <span class="text-[10px] sm:text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg w-max">Tuntas</span>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- MAIN CONTENT: CHART & DISPOSISI WIDGET -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        
        <!-- Chart Section (Hanya tampil untuk non-staf) -->
        <?php if(!$isStaf): ?>
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-6 shadow-sm flex flex-col justify-between">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-800">Statistik Surat 12 Bulan Terakhir</h2>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Grafik perbandingan volume surat masuk dan surat keluar.</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-medium shrink-0">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Masuk</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Keluar</span>
                    </div>
                </div>
                <div class="relative h-60 sm:h-72 w-full">
                    <canvas id="suratChart"></canvas>
                </div>
            </div>
        <?php endif; ?>

        <!-- Disposisi Widget (Diperbarui agar lebih cantik) -->
        <div class="<?php echo e(!$isStaf ? 'lg:col-span-1' : 'lg:col-span-3'); ?> bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div>
                <!-- Header Widget -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-800">Disposisi Tugas Untuk Saya</h2>
                    </div>
                    <a href="<?php echo e(route('disposisi.index')); ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">Lihat Semua →</a>
                </div>

                <!-- List Disposisi -->
                <div class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $listDisposisi ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $status = strtolower($d->status ?? 'menunggu');
                            $stConfig = [
                                'menunggu' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'border' => 'border-l-amber-500'],
                                'diproses' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'border' => 'border-l-blue-500'],
                                'selesai'  => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'border' => 'border-l-emerald-500'],
                            ];
                            $currentConfig = $stConfig[$status] ?? ['bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'border' => 'border-l-slate-400'];
                        ?>

                        <a href="<?php echo e(route('disposisi.show', $d->id)); ?>" class="group relative block p-4 bg-white hover:bg-slate-50/80 rounded-xl border border-slate-200/70 <?php echo e($currentConfig['border']); ?> border-l-4 shadow-xs hover:shadow-sm transition-all space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-blue-600 transition line-clamp-1">
                                    <?php echo e($d->suratMasuk->perihal ?? 'Surat Disposisi'); ?>

                                </h3>
                                <span class="text-[10px] <?php echo e($currentConfig['bg']); ?> px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wide shrink-0 border">
                                    <?php echo e(ucfirst($d->status ?? 'Menunggu')); ?>

                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-500">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Dari: <strong class="text-slate-700"><?php echo e($d->dari->name ?? '-'); ?></strong>
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="inline-flex items-center gap-1 font-mono">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <?php echo e($d->suratMasuk->nomor_agenda ?? '-'); ?>

                                </span>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-10 space-y-3">
                            <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-700">Tidak ada tugas baru</p>
                                <p class="text-[11px] text-slate-400">Belum ada disposisi surat masuk yang perlu ditindaklanjuti.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer Button -->
            <div class="pt-4 mt-4 border-t border-slate-100">
                <a href="<?php echo e(route('disposisi.index')); ?>" class="w-full py-2.5 px-4 text-center block text-xs font-bold text-slate-700 bg-slate-100 hover:bg-blue-600 hover:text-white rounded-xl shadow-xs transition-all duration-200">
                    Kelola Semua Disposisi →
                </a>
            </div>
        </div>

    </div>

    <!-- TABLE SECTION: SURAT MASUK TERBARU (Hanya Tampil Jika BUKAN Staf) -->
    <?php if(!$isStaf): ?>
        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm">
            <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-800">Surat Masuk Terbaru</h2>
                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Daftar arsip surat yang baru diterimakan ke sistem.</p>
                </div>
                <a href="<?php echo e(route('surat-masuk.index')); ?>" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition shrink-0">
                    Lihat Semua
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-4 sm:px-6">Nomor Agenda</th>
                            <th class="py-3 px-4 sm:px-6">Perihal</th>
                            <th class="py-3 px-4 sm:px-6">Instansi Pengirim</th>
                            <th class="py-3 px-4 sm:px-6">Kategori</th>
                            <th class="py-3 px-4 sm:px-6">Status</th>
                            <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        <?php $__empty_1 = true; $__currentLoopData = $suratMasukTerbaru ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 sm:px-6 font-bold text-blue-600 font-mono"><?php echo e($sm->nomor_agenda ?? '-'); ?></td>
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800 max-w-xs truncate"><?php echo e($sm->perihal ?? '-'); ?></td>
                                <td class="py-3.5 px-4 sm:px-6"><?php echo e($sm->instansi->nama_instansi ?? '-'); ?></td>
                                <td class="py-3.5 px-4 sm:px-6"><?php echo e($sm->kategori->nama_kategori ?? '-'); ?></td>
                                <td class="py-3.5 px-4 sm:px-6">
                                    <?php
                                        $statusClasses = [
                                            'proses' => 'bg-amber-50 text-amber-600 border-amber-200',
                                            'didisposisikan' => 'bg-purple-50 text-purple-600 border-purple-200',
                                            'selesai' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                        ];
                                        $currentStatus = strtolower($sm->status ?? 'baru');
                                        $badgeStyle = $statusClasses[$currentStatus] ?? 'bg-blue-50 text-blue-600 border-blue-200';
                                    ?>
                                    <span class="px-2.5 py-0.5 sm:py-1 rounded-full text-[10px] font-bold border <?php echo e($badgeStyle); ?>">
                                        <?php echo e(ucfirst($sm->status ?? 'Baru')); ?>

                                    </span>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-right">
                                    <a href="<?php echo e(route('surat-masuk.show', $sm->id)); ?>" class="text-blue-600 hover:text-blue-800 font-semibold text-xs inline-flex items-center gap-1">
                                        Detail →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                    Belum ada data surat masuk terbaru.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- SCRIPT CHART.JS GRAFIK -->
<?php if(!$isStaf): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const canvasElement = document.getElementById('suratChart');
            if (!canvasElement) return;
            
            const ctx = canvasElement.getContext('2d');

            const gradientMasuk = ctx.createLinearGradient(0, 0, 0, 300);
            gradientMasuk.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
            gradientMasuk.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

            const gradientKeluar = ctx.createLinearGradient(0, 0, 0, 300);
            gradientKeluar.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
            gradientKeluar.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            const chartLabels = <?php echo json_encode($chartLabels ?? [], 15, 512) ?>;
            const dataMasuk   = <?php echo json_encode($chartDataMasuk ?? [], 15, 512) ?>;
            const dataKeluar  = <?php echo json_encode($chartDataKeluar ?? [], 15, 512) ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Surat Masuk',
                            data: dataMasuk,
                            borderColor: '#2563eb',
                            backgroundColor: gradientMasuk,
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Surat Keluar',
                            data: dataKeluar,
                            borderColor: '#10b981',
                            backgroundColor: gradientKeluar,
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 11, weight: 'bold' },
                            bodyFont: { size: 11 },
                            padding: 8,
                            cornerRadius: 8,
                            displayColors: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8', font: { size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: '#94a3b8', font: { size: 10 } },
                            grid: { color: '#f1f5f9' }
                        }
                    }
                }
            });
        });
    </script>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/dashboard/index.blade.php ENDPATH**/ ?>