<?php $__env->startSection('title', 'Detail Surat Keluar'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6">

    
    <?php if(session('success')): ?>
        <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs sm:text-sm font-medium"><?php echo e(session('success')); ?></span>
            </div>
            <button onclick="this.closest('div').remove()" class="text-emerald-400 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-100/50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    <?php endif; ?>

    
    <?php if(session('error')): ?>
        <div class="flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs sm:text-sm font-medium"><?php echo e(session('error')); ?></span>
            </div>
            <button onclick="this.closest('div').remove()" class="text-rose-400 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-100/50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    <?php endif; ?>

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 p-6 sm:p-7 rounded-3xl border border-slate-800 shadow-md text-white">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('surat-keluar.index')); ?>" class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all duration-200 shrink-0 backdrop-blur-sm" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg sm:text-xl font-bold tracking-tight">Detail Surat Keluar</h1>
                <p class="text-xs text-slate-300 mt-0.5">Informasi lengkap dan arsip dokumen surat keluar.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <?php if(Route::has('surat-keluar.edit')): ?>
                <a href="<?php echo e(route('surat-keluar.edit', $suratKeluar)); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 rounded-2xl transition border border-amber-500/30 backdrop-blur-sm shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Arsip</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-gradient-to-b from-white via-slate-50/50 to-slate-100/60 rounded-3xl shadow-md border border-slate-200 p-6 sm:p-8 space-y-6">
        
        
        <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-6 border-b-2 border-slate-200 gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Arsip Surat Keluar</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug tracking-tight break-words">
                    <?php echo e($suratKeluar->perihal ?? 'Tanpa Perihal'); ?>

                </h2>
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                        #<?php echo e($suratKeluar->no_agenda ?? $suratKeluar->nomor_agenda ?? $suratKeluar->id); ?>

                    </span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-600">No. Surat: <strong class="text-slate-900 font-semibold break-all"><?php echo e($suratKeluar->nomor_surat ?? '-'); ?></strong></span>
                </div>
            </div>

            <?php
                $status = strtolower($suratKeluar->status ?? 'terkirim');
                $badgeClass = match($status) {
                    'konsep'    => 'bg-slate-200 text-slate-800 border-slate-300',
                    'diproses'  => 'bg-amber-100 text-amber-800 border-amber-300',
                    'disetujui' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'dikirim', 'terkirim' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                    default     => 'bg-slate-200 text-slate-800 border-slate-300'
                };
            ?>
            <div class="self-start sm:self-auto shrink-0">
                <span class="inline-flex items-center px-4 py-2 rounded-2xl text-xs font-bold border-2 shadow-xs <?php echo e($badgeClass); ?>">
                    <?php echo e(ucfirst($status)); ?>

                </span>
            </div>
        </div>

        
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2 p-5 rounded-2xl bg-white border-2 border-slate-200 shadow-sm">
                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Pengirim / Tujuan</dt>
                <dd class="text-sm font-bold text-slate-900 break-words">
                    <?php echo e($suratKeluar->pengirim ?? '-'); ?>

                </dd>
            </div>
            
            <div class="space-y-2 p-5 rounded-2xl bg-white border-2 border-slate-200 shadow-sm">
                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Kategori Surat</dt>
                <dd class="text-sm font-bold text-slate-900 break-words">
                    <?php echo e($suratKeluar->kategori->nama_kategori ?? $suratKeluar->kategori ?? '-'); ?>

                </dd>
            </div>

            <div class="space-y-2 p-5 rounded-2xl bg-white border-2 border-slate-200 shadow-sm">
                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Tanggal Surat</dt>
                <dd class="text-sm font-bold text-slate-900">
                    <?php echo e($suratKeluar->tanggal_surat ? \Carbon\Carbon::parse($suratKeluar->tanggal_surat)->format('d-m-Y') : '-'); ?>

                </dd>
            </div>

            <div class="space-y-2 p-5 rounded-2xl bg-white border-2 border-slate-200 shadow-sm">
                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Tanggal Dikirim</dt>
                <dd class="text-sm font-bold text-slate-900">
                    <?php echo e($suratKeluar->tanggal_keluar ? \Carbon\Carbon::parse($suratKeluar->tanggal_keluar)->format('d-m-Y') : '-'); ?>

                </dd>
            </div>

            <div class="sm:col-span-2 space-y-2 p-5 rounded-2xl bg-white border-2 border-slate-200 shadow-sm">
                <dt class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Dibuat Oleh</dt>
                <dd class="text-sm font-bold text-slate-900 truncate">
                    <?php echo e($suratKeluar->pembuat->name ?? '-'); ?>

                </dd>
            </div>
        </dl>

        
        <?php if(!empty($suratKeluar->perihal) || !empty($suratKeluar->ringkasan) || !empty($suratKeluar->isi_surat)): ?>
            <div class="pt-2">
                <div class="p-5 rounded-2xl bg-white border-2 border-slate-200 space-y-2 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Perihal / Isi Ringkas Surat</span>
                    <div class="text-sm text-slate-800 leading-relaxed whitespace-pre-line break-words font-medium">
                        <?php echo e($suratKeluar->perihal ?? ($suratKeluar->ringkasan ?? $suratKeluar->isi_surat)); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="pt-4 border-t-2 border-slate-200 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Berkas Lampiran Digital</span>
            </div>
            
            <?php
                $rawPath = $suratKeluar->lampiran_file ?? $suratKeluar->file_surat ?? null;
                $resolvedUrl = $fileUrl ?? (Route::has('surat-keluar.preview-lampiran') ? route('surat-keluar.preview-lampiran', $suratKeluar) : null);
                $extension = $rawPath ? strtolower(pathinfo($rawPath, PATHINFO_EXTENSION)) : '';
            ?>

            <?php if(!empty($rawPath) && !empty($resolvedUrl)): ?>
                <div class="space-y-4">
                    
                    <div class="flex flex-wrap items-center gap-2.5">
                        <a href="<?php echo e($resolvedUrl); ?>" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Buka / Layar Penuh</span>
                        </a>
                        <a href="<?php echo e($resolvedUrl); ?>" download class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-100 transition border-2 border-slate-200 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh Berkas</span>
                        </a>
                    </div>

                    
                    <div class="bg-slate-900/10 p-3 rounded-2xl border-2 border-slate-300 overflow-hidden backdrop-blur-sm">
                        <?php if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])): ?>
                            <div class="text-center py-2">
                                <img src="<?php echo e($resolvedUrl); ?>" alt="Lampiran Surat Keluar" class="max-h-[550px] mx-auto rounded-xl border border-white shadow-md object-contain">
                            </div>
                        <?php elseif($extension === 'pdf'): ?>
                            <iframe src="<?php echo e($resolvedUrl); ?>" class="w-full h-[550px] border-0 rounded-xl bg-white shadow-sm" title="Pratinjau PDF"></iframe>
                        <?php else: ?>
                            <div class="text-center py-10 bg-white rounded-xl border border-slate-200">
                                <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-xs text-slate-700 font-medium">Pratinjau langsung tidak tersedia untuk format file <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-900 font-bold">.<?php echo e($extension ?: 'dokumen'); ?></code>.</p>
                                <p class="text-[11px] text-slate-500 mt-1">Silakan gunakan tombol "Buka" atau "Unduh" di atas untuk melihat isi berkas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="w-full flex items-center justify-center gap-3 p-6 rounded-2xl bg-white text-slate-500 border-2 border-slate-200 shadow-sm">
                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span class="text-xs font-medium">Tidak ada berkas digital yang dilampirkan pada arsip surat ini.</span>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/surat_keluar/show.blade.php ENDPATH**/ ?>