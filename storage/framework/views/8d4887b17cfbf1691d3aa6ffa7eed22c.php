

<?php $__env->startSection('title', 'Edit Disposisi'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6">

    <!-- Flash Error Notification (Blade Validation) -->
    <?php if($errors->any()): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-xs transition duration-200">
            <div class="flex items-start gap-3">
                <div class="p-1.5 bg-rose-100 rounded-xl text-rose-600 shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xs sm:text-sm font-bold">Gagal menyimpan pembaruan disposisi:</h3>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Action Bar Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('disposisi.index')); ?>" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shrink-0" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-400 font-medium mb-1">
                    <a href="<?php echo e(route('disposisi.index')); ?>" class="hover:text-slate-600 transition">Disposisi</a>
                    <span>/</span>
                    <span class="text-slate-600">Edit Disposisi</span>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Edit Disposisi Surat</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('disposisi.index')); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                <span>Batal</span>
            </a>
        </div>
    </div>

    <!-- Form Container Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        
        <!-- Header Referensi Dokumen -->
        <?php if(isset($disposisi->suratMasuk)): ?>
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-5 sm:p-6 text-white border-b border-slate-800">
            <div class="flex items-start gap-4">
                <div class="p-2.5 bg-white/10 rounded-xl text-indigo-400 shrink-0 backdrop-blur-xs border border-white/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="space-y-1 min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Referensi Surat</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-white/10 text-indigo-200 border border-white/10">
                            #<?php echo e($disposisi->suratMasuk->nomor_agenda ? 'AG/' . $disposisi->suratMasuk->nomor_agenda : $disposisi->suratMasuk->id); ?>

                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-white leading-snug break-words">
                        <?php echo e($disposisi->suratMasuk->perihal ?? 'Tanpa Perihal'); ?>

                    </h2>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-300 font-medium pt-0.5">
                        <span>No. Surat: <strong class="text-white"><?php echo e($disposisi->suratMasuk->nomor_surat ?? '-'); ?></strong></span>
                        <span class="text-slate-500">&bull;</span>
                        <span>Pengirim: <strong class="text-white"><?php echo e($disposisi->suratMasuk->pengirim ?? '-'); ?></strong></span>
                        <a href="<?php echo e(route('surat-masuk.show', $disposisi->suratMasuk)); ?>" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-300 hover:text-indigo-200 font-semibold ml-auto transition">
                            <span>Lihat Surat</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form Edit Disposisi -->
        <form action="<?php echo e(route('disposisi.update', $disposisi->id)); ?>" method="POST" class="p-6 sm:p-8 space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <!-- Penerima Disposisi -->
            <div class="space-y-2">
                <label for="kepada_user_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Disposisikan Kepada <span class="text-rose-500">*</span>
                </label>
                
                <?php if(isset($users) && count($users) > 0): ?>
                    <select id="kepada_user_id" name="kepada_user_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-3 px-4 transition text-slate-800 shadow-2xs font-medium">
                        <option value="" disabled>-- Pilih Pejabat / Staf Tujuan --</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>" <?php if(old('kepada_user_id', $disposisi->kepada_user_id ?? $disposisi->kepada?->id) == $u->id): echo 'selected'; endif; ?>>
                                <?php echo e($u->name); ?> <?php echo e(isset($u->jabatan) && $u->jabatan ? '('.$u->jabatan.')' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php else: ?>
                    <input type="text" id="penerima" name="penerima" 
                        value="<?php echo e(old('penerima', $disposisi->penerima ?? $disposisi->tujuan ?? $disposisi->kepada?->name)); ?>" required 
                        placeholder="Contoh: Sekuriti / Staff Keuangan" 
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-3 px-4 transition text-slate-800 shadow-2xs font-medium">
                <?php endif; ?>
                <p class="text-[11px] text-slate-400 font-medium">Perbarui personil atau bagian yang ditugaskan.</p>
            </div>

            <!-- Instruksi Penanganan -->
            <div class="space-y-2">
                <label for="instruksi" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Instruksi Penanganan <span class="text-rose-500">*</span>
                </label>
                <textarea id="instruksi" name="instruksi" rows="3" required placeholder="Contoh: Mohon ditindaklanjuti segera dan koordinasikan dengan tim terkait." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm p-4 transition text-slate-800 placeholder-slate-400 shadow-2xs leading-relaxed"><?php echo e(old('instruksi', $disposisi->instruksi ?? $disposisi->isi_disposisi)); ?></textarea>
            </div>

            <!-- Catatan Tambahan -->
            <div class="space-y-2">
                <label for="catatan" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Catatan Tambahan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                </label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Tuliskan catatan khusus atau petunjuk teknis tambahan..." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm p-4 transition text-slate-800 placeholder-slate-400 shadow-2xs leading-relaxed"><?php echo e(old('catatan', $disposisi->catatan)); ?></textarea>
            </div>

            <!-- Grid Sifat, Batas Waktu, & Status Pekerjaan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                
                <!-- Sifat Disposisi -->
                <?php if(isset($disposisi->sifat) || Schema::hasColumn('disposisi', 'sifat')): ?>
                <div class="space-y-2">
                    <label for="sifat" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Sifat Disposisi</label>
                    <select id="sifat" name="sifat" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-3 px-4 transition text-slate-800 shadow-2xs font-medium">
                        <?php $__currentLoopData = ['biasa' => 'Biasa', 'penting' => 'Penting', 'segera' => 'Segera', 'rahasia' => 'Rahasia']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php if(old('sifat', strtolower($disposisi->sifat ?? 'biasa')) == $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Batas Waktu -->
                <div class="space-y-2">
                    <label for="batas_waktu" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Batas Waktu Penyelesaian
                    </label>
                    <?php
                        $formattedDate = old('batas_waktu');
                        if (!$formattedDate && isset($disposisi->batas_waktu)) {
                            $formattedDate = $disposisi->batas_waktu instanceof \Carbon\Carbon 
                                ? $disposisi->batas_waktu->format('Y-m-d') 
                                : \Carbon\Carbon::parse($disposisi->batas_waktu)->format('Y-m-d');
                        }
                    ?>
                    <input type="date" id="batas_waktu" name="batas_waktu" value="<?php echo e($formattedDate); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-3 px-4 transition text-slate-800 shadow-2xs font-medium">
                </div>

                <!-- Status Pekerjaan (Diperbaiki dari 'proses' menjadi 'diproses') -->
                <div class="space-y-2 sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Status Pekerjaan <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        <?php $__currentLoopData = ['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stKey => $stLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center justify-center p-3 border rounded-xl text-xs sm:text-sm font-semibold cursor-pointer transition has-[:checked]:bg-purple-50 has-[:checked]:border-purple-500 has-[:checked]:text-purple-700 text-slate-600 bg-slate-50/50 border-slate-200 hover:bg-slate-100">
                                <input type="radio" name="status" value="<?php echo e($stKey); ?>" <?php if(old('status', strtolower($disposisi->status ?? 'menunggu')) == $stKey): echo 'checked'; endif; ?> class="hidden">
                                <span><?php echo e($stLabel); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

            </div>

            <!-- Footer Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 border-t border-slate-100 mt-6">
                <a href="<?php echo e(route('disposisi.index')); ?>" class="w-full sm:w-auto text-center px-5 py-2.5 text-xs font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-bold rounded-xl bg-purple-600 hover:bg-purple-700 text-white shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Perbarui Disposisi</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/disposisi/edit.blade.php ENDPATH**/ ?>