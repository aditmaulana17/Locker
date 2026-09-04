

<?php $__env->startSection('title', 'Edit Pengguna'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-6 pb-12">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-6 rounded-xl sm:rounded-2xl shadow-sm border border-slate-200/80">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit Pengguna: <?php echo e($user->name); ?></h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Perbarui informasi akun, hak akses, dan status pengguna di dalam sistem.</p>
        </div>
        <a href="<?php echo e(route('users.index')); ?>" class="inline-flex items-center justify-center px-4 py-2 sm:py-2 text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-xl shadow-xs hover:bg-slate-200 focus:outline-none transition w-full sm:w-auto shrink-0">
            <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <form method="POST" action="<?php echo e(route('users.update', $user->id)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="p-4 sm:p-8 space-y-5 sm:space-y-6">
                
                <!-- Section 1: Informasi Personal -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Informasi Personal
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <!-- Nama Lengkap -->
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="<?php echo e(old('name', $user->name)); ?>" required placeholder="Contoh: Budi Santoso, S.Kom"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-500 bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="<?php echo e(old('email', $user->email)); ?>" required placeholder="budi@domain.com"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-500 bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Jabatan -->
                        <div>
                            <label for="jabatan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Jabatan / Posisi
                            </label>
                            <input type="text" name="jabatan" id="jabatan" value="<?php echo e(old('jabatan', $user->jabatan)); ?>" placeholder="Contoh: Kepala Bagian / Staf Agenda"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['jabatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-500 bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['jabatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Section 2: Hak Akses & Keamanan -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3 sm:mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Hak Akses & Keamanan Akun
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mb-4 sm:mb-5">
                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Role / Hak Akses <span class="text-rose-500">*</span>
                            </label>
                            <select name="role" id="role" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium">
                                <option value="admin" <?php echo e(old('role', $user->role) == 'admin' ? 'selected' : ''); ?>>Admin (Akses Penuh)</option>
                                <option value="pimpinan" <?php echo e(old('role', $user->role) == 'pimpinan' ? 'selected' : ''); ?>>Pimpinan (Akses Eksekutif)</option>
                                <option value="staf" <?php echo e(in_array(old('role', $user->role), ['staf', 'staff']) ? 'selected' : ''); ?>>Staf (Akses Operator)</option>
                            </select>
                            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Status Akun <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium">
                                <option value="aktif" <?php echo e(old('status', $user->status ?? 'aktif') == 'aktif' ? 'selected' : ''); ?>>Aktif (Dapat Login)</option>
                                <option value="nonaktif" <?php echo e(old('status', $user->status) == 'nonaktif' ? 'selected' : ''); ?>>Nonaktif (Diblokir)</option>
                            </select>
                            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Password Baru dengan Toggle Show/Hide -->
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Password Baru <span class="text-slate-400 font-normal lowercase">(kosongkan jika tidak ingin mengubah)</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" placeholder="••••••••"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-3.5 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-500 bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.027 10.027 0 014.132-5.411m3.618-1.516A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                            <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> 
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

            </div>

            <!-- Action Buttons Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 sm:gap-3 px-4 sm:px-8 py-3.5 sm:py-4 bg-slate-50 border-t border-slate-100">
                <a href="<?php echo e(route('users.index')); ?>" class="w-full sm:w-auto px-5 py-2.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 hover:text-slate-800 transition shadow-xs text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-md shadow-blue-600/30 transition">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                eyeOpen.classList.toggle('hidden');
                eyeClosed.classList.toggle('hidden');
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/users/edit.blade.php ENDPATH**/ ?>