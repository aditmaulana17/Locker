

<?php $__env->startSection('title', 'Detail Disposisi - Arsip Surat'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6">

    <!-- Flash Notification Success/Error -->
    <?php if(session('success')): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-xs transition duration-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-emerald-100 rounded-xl text-emerald-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-xs sm:text-sm font-semibold"><?php echo e(session('success')); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Action Bar Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('disposisi.index')); ?>" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shrink-0" title="Kembali ke Daftar Disposisi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-400 font-medium mb-1">
                    <a href="<?php echo e(route('disposisi.index')); ?>" class="hover:text-slate-600 transition">Disposisi</a>
                    <span>/</span>
                    <span class="text-slate-600">Detail Disposisi</span>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Detail Disposisi Surat</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('disposisi.index')); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                <span>Kembali</span>
            </a>
            <?php if(auth()->user()->isAdmin() || auth()->id() === $disposisi->dari_user_id): ?>
                <a href="<?php echo e(route('disposisi.edit', $disposisi)); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Disposisi</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Layout Grid Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kartu Detail Disposisi (Kiri/Utama) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8 space-y-6">
            
            <!-- Header Card & Status Badge -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Disposisi</span>
                <?php
                    $statusClasses = [
                        'menunggu' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                        'diproses' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                        'proses'   => 'bg-blue-50 text-blue-700 border-blue-200/80',
                        'selesai'  => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                    ];
                    $st = strtolower($disposisi->status);
                ?>
                <span class="px-3.5 py-1 rounded-full text-xs font-bold border capitalize shrink-0 <?php echo e($statusClasses[$st] ?? 'bg-slate-50 text-slate-600 border-slate-200'); ?>">
                    <?php echo e($disposisi->status); ?>

                </span>
            </div>

            <!-- Grid Metadata Pengirim & Penerima -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="min-w-0 bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengirim Disposisi</label>
                    <p class="mt-1 text-xs sm:text-sm font-bold text-slate-800 break-words">
                        <?php echo e($disposisi->dari?->name ?? $disposisi->pengirim ?? '-'); ?>

                    </p>
                    <?php if(isset($disposisi->dari?->jabatan)): ?>
                        <p class="text-[11px] text-slate-500 font-medium"><?php echo e($disposisi->dari->jabatan); ?></p>
                    <?php endif; ?>
                </div>

                <div class="min-w-0 bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penerima Disposisi</label>
                    <p class="mt-1 text-xs sm:text-sm font-bold text-slate-800 break-words">
                        <?php echo e($disposisi->kepada?->name ?? $disposisi->penerima ?? $disposisi->tujuan ?? '-'); ?>

                    </p>
                    <?php if(isset($disposisi->kepada?->jabatan)): ?>
                        <p class="text-[11px] text-slate-500 font-medium"><?php echo e($disposisi->kepada->jabatan); ?></p>
                    <?php endif; ?>
                </div>

                <div class="min-w-0 bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Batas Waktu / Tenggat</label>
                    <p class="mt-1 text-xs sm:text-sm font-bold text-slate-800">
                        <?php echo e($disposisi->batas_waktu ? \Carbon\Carbon::parse($disposisi->batas_waktu)->translatedFormat('d F Y') : '-'); ?>

                    </p>
                </div>

                <div class="min-w-0 bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Dibuat</label>
                    <p class="mt-1 text-xs sm:text-sm font-bold text-slate-800">
                        <?php echo e($disposisi->created_at ? $disposisi->created_at->translatedFormat('d F Y H:i') : '-'); ?>

                    </p>
                </div>
            </div>

            <!-- Instruksi / Catatan Disposisi -->
            <div class="space-y-2 pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Instruksi & Catatan Penanganan</label>
                <div class="p-4 sm:p-5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs sm:text-sm text-slate-800 leading-relaxed whitespace-pre-line break-words shadow-2xs font-medium">
                    <?php echo e($disposisi->instruksi ?? $disposisi->catatan ?? $disposisi->isi_disposisi ?? 'Tidak ada instruksi atau catatan khusus.'); ?>

                </div>
            </div>

            <!-- Form Update Status Cepat (Akses khusus Penerima / Admin) -->
            <?php if(auth()->user()->isAdmin() || auth()->id() === $disposisi->kepada_user_id): ?>
                <div class="pt-6 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Perbarui Status Pekerjaan</label>
                    <form action="<?php echo e(route('disposisi.status', $disposisi)); ?>" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <select name="status" class="w-full sm:w-auto rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-2.5 px-4 transition text-slate-800 shadow-2xs font-semibold">
                            <option value="menunggu" <?php if($st === 'menunggu'): echo 'selected'; endif; ?>>Menunggu</option>
                            <option value="diproses" <?php if($st === 'diproses' || $st === 'proses'): echo 'selected'; endif; ?>>Diproses</option>
                            <option value="selesai" <?php if($st === 'selesai'): echo 'selected'; endif; ?>>Selesai</option>
                        </select>
                        
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Status</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kartu Informasi Surat Masuk Terkait (Kanan) -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <!-- Header Card Surat -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-5 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Surat Masuk Terkait</h2>
                        <?php if($disposisi->suratMasuk): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-white/10 text-indigo-200 border border-white/10">
                                #<?php echo e($disposisi->suratMasuk->nomor_agenda ? 'AG/' . $disposisi->suratMasuk->nomor_agenda : $disposisi->suratMasuk->id); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <?php if($disposisi->suratMasuk): ?>
                        <div class="space-y-3.5">
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Perihal</span>
                                <h3 class="text-xs sm:text-sm text-slate-900 font-bold leading-snug break-words mt-0.5">
                                    <?php echo e($disposisi->suratMasuk->perihal ?? 'Tanpa Perihal'); ?>

                                </h3>
                            </div>

                            <div class="min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Surat</span>
                                <p class="text-xs font-semibold text-slate-700 break-all mt-0.5"><?php echo e($disposisi->suratMasuk->nomor_surat ?? '-'); ?></p>
                            </div>

                            <div class="min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Asal / Pengirim Surat</span>
                                <p class="text-xs font-semibold text-slate-700 break-words mt-0.5">
                                    <?php echo e($disposisi->suratMasuk->pengirim ?? ($disposisi->suratMasuk->instansi->nama_instansi ?? '-')); ?>

                                </p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <a href="<?php echo e(route('surat-masuk.show', $disposisi->suratMasuk)); ?>" 
                               class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-xs">
                                <span>Lihat Detail Surat</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="py-4 text-center">
                            <p class="text-xs text-slate-400 italic">Data surat masuk terkait tidak ditemukan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/disposisi/show.blade.php ENDPATH**/ ?>