<?php $__env->startSection('title', 'Edit Surat Keluar'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-12">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Surat Keluar</h1>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui informasi data arsip surat keluar yang tersimpan.</p>
        </div>
        <a href="<?php echo e(route('surat-keluar.index')); ?>" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl shadow-xs hover:bg-slate-50 hover:text-slate-800 focus:outline-none transition w-full sm:w-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <form method="POST" action="<?php echo e(route('surat-keluar.update', $suratKeluar->id)); ?>" enctype="multipart/form-data" id="form-surat">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

            <div class="p-6 sm:p-8 space-y-8">

                <!-- Section 1: Detail Surat -->
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <div class="w-2 h-5 bg-blue-600 rounded-full"></div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Informasi Utama Surat</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nomor Surat -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Surat <span class="text-rose-500">*</span></label>
                            <input type="text" name="nomor_surat" value="<?php echo e(old('nomor_surat', $suratKeluar->nomor_surat)); ?>" required placeholder="Contoh: 005/B/I/2026"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['nomor_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['nomor_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Pengirim -->
                        <div class="relative">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Instansi Tujuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="pengirim" id="pengirim" value="<?php echo e(old('pengirim', $suratKeluar->pengirim)); ?>" required placeholder="Ketik nama pengirim..." autocomplete="off"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['pengirim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            
                            <div id="pengirim-suggestions" class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto hidden"></div>
                            <?php $__errorArgs = ['pengirim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Tanggal Surat -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Surat <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_surat" value="<?php echo e(old('tanggal_surat', \Carbon\Carbon::parse($suratKeluar->tanggal_surat)->format('Y-m-d'))); ?>" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium <?php $__errorArgs = ['tanggal_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['tanggal_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Tanggal Dikirim -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Dikirim <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_keluar" value="<?php echo e(old('tanggal_keluar', \Carbon\Carbon::parse($suratKeluar->tanggal_keluar)->format('Y-m-d'))); ?>" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium <?php $__errorArgs = ['tanggal_keluar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['tanggal_keluar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Kategori Surat -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kategori Surat <span class="text-rose-500">*</span></label>
                            <select name="kategori_surat_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium <?php $__errorArgs = ['kategori_surat_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="" disabled>Pilih kategori surat</option>
                                <?php $__currentLoopData = $kategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($k->id); ?>" <?php echo e(old('kategori_surat_id', $suratKeluar->kategori_surat_id) == $k->id ? 'selected' : ''); ?>>
                                        <?php echo e($k->nama_kategori); ?> <?php if(isset($k->sifat)): ?>(<?php echo e(ucfirst($k->sifat)); ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['kategori_surat_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Surat <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="draft" <?php echo e(old('status', $suratKeluar->status) == 'draft' ? 'selected' : ''); ?>>Konsep / Draft</option>
                                <option value="diproses" <?php echo e(old('status', $suratKeluar->status) == 'diproses' ? 'selected' : ''); ?>>Diproses</option>
                                <option value="disetujui" <?php echo e(old('status', $suratKeluar->status) == 'disetujui' ? 'selected' : ''); ?>>Disetujui</option>
                                <option value="dikirim" <?php echo e(old('status', $suratKeluar->status) == 'dikirim' ? 'selected' : ''); ?>>Dikirim</option>
                                <option value="diarsipkan" <?php echo e(old('status', $suratKeluar->status) == 'diarsipkan' ? 'selected' : ''); ?>>Diarsipkan</option>
                            </select>
                            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Perihal -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Perihal / Isi Ringkas <span class="text-rose-500">*</span></label>
                        <textarea name="perihal" rows="3" required placeholder="Tuliskan perihal atau isi ringkas surat secara jelas..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['perihal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('perihal', $suratKeluar->perihal)); ?></textarea>
                        <?php $__errorArgs = ['perihal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <!-- Section 2: Lampiran Dokumen -->
                <div class="space-y-5 pt-2">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <div class="w-2 h-5 bg-indigo-600 rounded-full"></div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Lampiran Dokumen Surat</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                        <!-- Kolom Kiri: File Saat Ini (Jika Ada) -->
                        <?php if($suratKeluar->lampiran_file): ?>
                            <div class="space-y-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">File Lampiran Saat Ini</label>
                                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs h-[46px]">
                                    <div class="flex items-center space-x-2 text-slate-600 truncate mr-2">
                                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span class="truncate font-medium"><?php echo e(basename($suratKeluar->lampiran_file)); ?></span>
                                    </div>
                                    <a href="<?php echo e(route('surat-keluar.preview-lampiran', $suratKeluar)); ?>" target="_blank" class="text-blue-600 hover:underline font-semibold shrink-0">Lihat File</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Kolom Kanan / Full: Upload File Baru -->
                        <div class="space-y-2 <?php echo e($suratKeluar->lampiran_file ? '' : 'md:col-span-2'); ?>">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Upload File Lampiran Baru</label>
                            <label class="relative flex items-center justify-between border border-slate-200 rounded-xl cursor-pointer bg-slate-50/50 hover:bg-slate-100/80 px-4 py-3 transition duration-150 h-[46px] <?php $__errorArgs = ['lampiran_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <div class="flex items-center space-x-3 overflow-hidden pointer-events-none">
                                    <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="text-xs text-slate-600 truncate font-medium" id="file-label-text">Pilih file PDF, JPG, atau PNG (opsional)...</span>
                                </div>
                                <input type="file" name="lampiran_file" id="lampiran_file" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="updateFileName(this)">
                            </label>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400">Kosongkan lampiran jika tidak ingin mengganti file yang sudah ada.</p>
                    <?php $__errorArgs = ['lampiran_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

            </div>

            <!-- Action Buttons Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 px-6 sm:px-8 py-4 bg-slate-50/80 border-t border-slate-100">
                <a href="<?php echo e(route('surat-keluar.index')); ?>" class="w-full sm:w-auto px-5 py-2.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 hover:text-slate-800 transition shadow-xs text-center">
                    Batal
                </a>
                <button type="submit" id="submit-btn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-md shadow-blue-600/30 transition disabled:opacity-50">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Perbarui Surat Keluar
                </button>
            </div>

        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function updateFileName(input) {
        const textElement = document.getElementById('file-label-text');
        if (input.files && input.files[0]) {
            textElement.textContent = "Terpilih: " + input.files[0].name;
            textElement.classList.add('text-blue-600', 'font-bold');
        } else {
            textElement.textContent = "Pilih file PDF, JPG, atau PNG (opsional)...";
            textElement.classList.remove('text-blue-600', 'font-bold');
        }
    }

    document.getElementById('form-surat').addEventListener('submit', function() {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memperbarui...
        `;
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/surat_keluar/edit.blade.php ENDPATH**/ ?>