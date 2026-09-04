<?php $__env->startSection('title', 'Data Instansi'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-4 sm:space-y-6">

    <!-- HEADER & TOMBOL TAMBAH -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Data Instansi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Kelola data instansi internal dan eksternal.</p>
        </div>

        <button type="button" 
                onclick="document.getElementById('modalTambah').classList.remove('hidden')" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl transition-colors shadow-sm w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Instansi</span>
        </button>
    </div>

    <!-- FILTER & SEARCH SECTION -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-3 sm:p-4">
        <form method="GET" action="<?php echo e(route('instansi.index')); ?>" class="flex flex-col sm:flex-row items-center gap-2.5 sm:gap-3">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                       placeholder="Cari nama instansi..." 
                       class="w-full pl-9 pr-3 py-2 rounded-lg border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-initial px-5 py-2 bg-slate-900 text-white rounded-lg text-xs sm:text-sm font-medium hover:bg-slate-800 transition-colors shadow-sm text-center">
                    Cari
                </button>

                <?php if(request()->filled('search')): ?>
                    <a href="<?php echo e(route('instansi.index')); ?>" class="px-3.5 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs sm:text-sm font-medium hover:bg-slate-200 transition-colors border border-slate-200 text-center">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] sm:text-xs tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4 min-w-[160px]">Nama Instansi</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4">Jenis</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4">Kontak & Email</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4">Alamat</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $instansis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instansi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Nama & Kontak Person -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4">
                            <div class="font-semibold text-slate-800"><?php echo e($instansi->nama_instansi); ?></div>
                            <?php if($instansi->kontak_person): ?>
                                <div class="text-[11px] sm:text-xs text-slate-400 mt-0.5">PIC: <?php echo e($instansi->kontak_person); ?></div>
                            <?php endif; ?>
                        </td>

                        <!-- Jenis -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                            <?php
                                $jenisClass = match(strtolower($instansi->jenis ?? '')) {
                                    'internal'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'eksternal' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    default     => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                            ?>
                            <span class="text-[11px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full border <?php echo e($jenisClass); ?>">
                                <?php echo e(ucfirst($instansi->jenis)); ?>

                            </span>
                        </td>

                        <!-- Kontak & Email -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                            <div class="text-slate-700 text-[11px] sm:text-xs"><?php echo e($instansi->email ?? '-'); ?></div>
                            <div class="text-slate-400 text-[11px] sm:text-xs mt-0.5"><?php echo e($instansi->telepon ?? '-'); ?></div>
                        </td>

                        <!-- Alamat -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4">
                            <div class="text-[11px] sm:text-xs text-slate-600 truncate max-w-xs" title="<?php echo e($instansi->alamat); ?>">
                                <?php echo e($instansi->alamat ?? '-'); ?>

                            </div>
                        </td>

                        <!-- Aksi -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1">
                                <!-- Edit Button -->
                                <button type="button" 
                                        onclick="document.getElementById('modalEdit<?php echo e($instansi->id); ?>').classList.remove('hidden')" 
                                        class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" 
                                        title="Ubah Instansi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Delete Form (SweetAlert2) -->
                                <form action="<?php echo e(route('instansi.destroy', $instansi)); ?>" method="POST" class="inline delete-form">
                                    <?php echo csrf_field(); ?> 
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="button" 
                                            class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors delete-btn" 
                                            title="Hapus Instansi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center py-12 sm:py-16 px-4">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3 sm:mb-4 border border-slate-200">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <h3 class="text-sm sm:text-base font-semibold text-slate-800">Belum ada data instansi</h3>
                                <p class="text-slate-500 text-xs mt-1 mb-4 sm:mb-5 text-center">Silakan tambahkan data instansi baru.</p>
                                <button type="button" 
                                        onclick="document.getElementById('modalTambah').classList.remove('hidden')" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Tambah Instansi</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    <?php if(isset($instansis) && method_exists($instansis, 'links')): ?>
    <div class="pt-2">
        <?php echo e($instansis->withQueryString()->links()); ?>

    </div>
    <?php endif; ?>

</div>

<!-- ========================================== -->
<!-- MODAL EDIT (LOOP DI LUAR TABEL) -->
<!-- ========================================== -->
<?php $__currentLoopData = $instansis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instansi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="modalEdit<?php echo e($instansi->id); ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden transform transition-all my-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="p-1.5 sm:p-2 bg-amber-50 text-amber-600 rounded-xl border border-amber-100 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-800">Ubah Data Instansi</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500">Perbarui rincian informasi instansi.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalEdit<?php echo e($instansi->id); ?>').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Form Edit -->
        <form method="POST" action="<?php echo e(route('instansi.update', $instansi)); ?>">
            <?php echo csrf_field(); ?> 
            <?php echo method_field('PUT'); ?>
            
            <div class="p-4 sm:p-6 space-y-3.5 sm:space-y-4">
                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Nama Instansi <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_instansi" value="<?php echo e(old('nama_instansi', $instansi->nama_instansi)); ?>" required class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Jenis <span class="text-rose-500">*</span></label>
                        <select name="jenis" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="internal" <?php if(old('jenis', $instansi->jenis) == 'internal'): echo 'selected'; endif; ?>>Internal</option>
                            <option value="eksternal" <?php if(old('jenis', $instansi->jenis) == 'eksternal'): echo 'selected'; endif; ?>>Eksternal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Telepon</label>
                        <input type="text" name="telepon" value="<?php echo e(old('telepon', $instansi->telepon)); ?>" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email', $instansi->email)); ?>" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Kontak Person (PIC)</label>
                        <input type="text" name="kontak_person" value="<?php echo e(old('kontak_person', $instansi->kontak_person)); ?>" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Alamat</label>
                    <textarea name="alamat" rows="2" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"><?php echo e(old('alamat', $instansi->alamat)); ?></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 px-4 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 bg-slate-50/50">
                <button type="button" onclick="document.getElementById('modalEdit<?php echo e($instansi->id); ?>').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors text-center">Batal</button>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl transition-all shadow-sm">Simpan Perubahan</button>
            </div>
        </form>

    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<!-- ========================================== -->
<!-- MODAL TAMBAH -->
<!-- ========================================== -->
<div id="modalTambah" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden transform transition-all my-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="p-1.5 sm:p-2 bg-blue-50 text-blue-600 rounded-xl border border-blue-100 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-800">Tambah Instansi Baru</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500">Isi formulir untuk menambahkan instansi.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Form Tambah -->
        <form method="POST" action="<?php echo e(route('instansi.store')); ?>">
            <?php echo csrf_field(); ?>
            
            <div class="p-4 sm:p-6 space-y-3.5 sm:space-y-4">
                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Nama Instansi <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_instansi" value="<?php echo e(old('nama_instansi')); ?>" placeholder="Contoh: Dinas Pendidikan" required class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Jenis <span class="text-rose-500">*</span></label>
                        <select name="jenis" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="internal" <?php if(old('jenis') == 'internal'): echo 'selected'; endif; ?>>Internal</option>
                            <option value="eksternal" <?php if(old('jenis') == 'eksternal'): echo 'selected'; endif; ?>>Eksternal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Telepon</label>
                        <input type="text" name="telepon" value="<?php echo e(old('telepon')); ?>" placeholder="021-xxxxxxx" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="email@instansi.com" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Kontak Person (PIC)</label>
                        <input type="text" name="kontak_person" value="<?php echo e(old('kontak_person')); ?>" placeholder="Nama PIC" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Alamat</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap instansi..." class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"><?php echo e(old('alamat')); ?></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 px-4 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 bg-slate-50/50">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors text-center">Batal</button>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl transition-all shadow-sm">Simpan Data</button>
            </div>
        </form>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- SweetAlert2 Script untuk Konfirmasi Hapus -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data instansi yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        confirmButton: 'px-4 py-2.5 rounded-xl font-semibold text-sm',
                        cancelButton: 'px-4 py-2.5 rounded-xl font-semibold text-sm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/instansi/index.blade.php ENDPATH**/ ?>