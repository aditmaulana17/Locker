<?php $__env->startSection('title', 'Manajemen Pengguna'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-4 sm:space-y-6 pb-12">

    <!-- Header Section (Judul & Tombol Tambah) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola akun pengguna sistem, hak akses, dan peranan jabatan.</p>
        </div>

        <a href="<?php echo e(route('users.create')); ?>" 
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all duration-200 shrink-0 w-full sm:w-auto">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    <!-- Filter & Search Bar Container -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="<?php echo e(route('users.index')); ?>" class="flex flex-col md:flex-row items-center gap-2.5 sm:gap-3">
            
            <!-- Search Input -->
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="<?php echo e(request('search')); ?>" 
                       placeholder="Cari nama, email, atau jabatan..." 
                       class="w-full pl-10 pr-4 py-2 sm:py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all duration-150">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <!-- Filter Role (Admin, Pimpinan, Staf) -->
                <div class="flex-1 md:w-48 shrink-0">
                    <select name="role" onchange="this.form.submit()" class="w-full px-3.5 py-2 sm:py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-medium">
                        <option value="">Semua Role</option>
                        <option value="admin" <?php echo e(request('role') == 'admin' ? 'selected' : ''); ?>>Admin</option>
                        <option value="pimpinan" <?php echo e(request('role') == 'pimpinan' ? 'selected' : ''); ?>>Pimpinan</option>
                        <option value="staf" <?php echo e(in_array(request('role'), ['staf', 'staff']) ? 'selected' : ''); ?>>Staf</option>
                    </select>
                </div>

                <!-- Button Submit Search -->
                <button type="submit" class="px-4 sm:px-5 py-2 sm:py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs sm:text-sm rounded-xl transition duration-150 shadow-sm shrink-0">
                    Cari
                </button>

                <!-- Reset Button -->
                <?php if(request('search') || request('role')): ?>
                    <a href="<?php echo e(route('users.index')); ?>" class="px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-600 font-semibold text-xs sm:text-sm rounded-xl transition duration-150 text-center shrink-0 shadow-xs">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Table Data Pengguna -->
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px] sm:min-w-full">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 sm:py-3.5 px-4 sm:px-6">Pengguna</th>
                        <th class="py-3 sm:py-3.5 px-4 sm:px-6">Role</th>
                        <th class="py-3 sm:py-3.5 px-4 sm:px-6">Jabatan</th>
                        <th class="py-3 sm:py-3.5 px-4 sm:px-6">Status</th>
                        <th class="py-3 sm:py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/60 transition-colors duration-150">
                            <!-- Pengguna (Avatar, Nama, Email) -->
                            <td class="py-3 sm:py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-2.5 sm:gap-3">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 font-bold text-xs sm:text-sm shadow-xs shrink-0">
                                        <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800 leading-snug"><?php echo e($user->name); ?></span>
                                        <span class="text-[11px] sm:text-xs text-slate-400 font-medium"><?php echo e($user->email); ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge (Admin, Pimpinan, Staf) -->
                            <td class="py-3 sm:py-4 px-4 sm:px-6 whitespace-nowrap">
                                <?php if(strtolower($user->role) === 'admin'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                                        Admin
                                    </span>
                                <?php elseif(strtolower($user->role) === 'pimpinan'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        Pimpinan
                                    </span>
                                <?php elseif(in_array(strtolower($user->role), ['staf', 'staff'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                        Staf
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/80">
                                        <?php echo e(ucfirst($user->role ?? 'User')); ?>

                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Jabatan -->
                            <td class="py-3 sm:py-4 px-4 sm:px-6 text-slate-600 font-medium whitespace-nowrap">
                                <?php echo e($user->jabatan ?? '-'); ?>

                            </td>

                            <!-- Status Badge -->
                            <td class="py-3 sm:py-4 px-4 sm:px-6 whitespace-nowrap">
                                <?php if(isset($user->is_active) ? $user->is_active : (strtolower($user->status ?? 'aktif') === 'aktif')): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3 sm:py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Edit Button -->
                                    <a href="<?php echo e(route('users.edit', $user->id)); ?>" 
                                       title="Edit User"
                                       class="p-1.5 sm:p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all duration-150">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Delete Button (Cegah Hapus Akun Sendiri) -->
                                    <?php if(auth()->id() !== $user->id): ?>
                                        <form method="POST" action="<?php echo e(route('users.destroy', $user->id)); ?>" class="inline-block delete-form">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="button" 
                                                title="Hapus User"
                                                class="p-1.5 sm:p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all duration-150 delete-btn">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="py-10 sm:py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="font-medium text-xs sm:text-sm">Tidak ada data pengguna yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <?php if(method_exists($users, 'hasPages') && $users->hasPages()): ?>
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-slate-100 bg-slate-50/50">
                <?php echo e($users->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/users/index.blade.php ENDPATH**/ ?>