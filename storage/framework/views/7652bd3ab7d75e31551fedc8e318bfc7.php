<?php $__env->startSection('title', 'Catat Surat Masuk'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-12">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Catat Surat Masuk</h1>
            <p class="text-sm text-slate-500 mt-0.5">Isi formulir di bawah ini untuk menambahkan data arsip surat masuk baru.</p>
        </div>
        <a href="<?php echo e(route('surat-masuk.index')); ?>" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl shadow-xs hover:bg-slate-50 hover:text-slate-800 focus:outline-none transition w-full sm:w-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <form method="POST" action="<?php echo e(route('surat-masuk.store')); ?>" enctype="multipart/form-data" id="form-surat">
        <?php echo csrf_field(); ?>

        <!-- Input Hidden Nomor Agenda -->
        <input type="hidden" name="nomor_agenda" value="<?php echo e($nomorAgenda); ?>">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            <!-- Banner Nomor Agenda Otomatis -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50/50 border-b border-blue-100/80 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center text-blue-900 text-sm">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 mr-3 shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <span class="flex flex-col sm:flex-row sm:items-center gap-1">
                        <span>Nomor Agenda Sistem:</span> 
                        <strong class="font-bold text-blue-700 font-mono bg-white px-3 py-1 rounded-lg text-xs border border-blue-200 shadow-2xs w-fit"><?php echo e($nomorAgenda); ?></strong>
                    </span>
                </div>
            </div>

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
                            <input type="text" name="nomor_surat" value="<?php echo e(old('nomor_surat')); ?>" required placeholder="Contoh: 005/B/I/2026"
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

                        <!-- Instansi Pengirim -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Instansi Pengirim <span class="text-rose-500">*</span></label>
                            <input type="text" name="pengirim" value="<?php echo e(old('pengirim')); ?>" required placeholder="Masukkan nama pengirim surat..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['pengirim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
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
                            <input type="date" name="tanggal_surat" value="<?php echo e(old('tanggal_surat')); ?>" required
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

                        <!-- Tanggal Diterima -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Diterima <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_terima" value="<?php echo e(old('tanggal_terima', date('Y-m-d'))); ?>" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium <?php $__errorArgs = ['tanggal_terima'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['tanggal_terima'];
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
                                <option value="" disabled selected>Pilih kategori surat</option>
                                <?php $__currentLoopData = $kategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($k->id); ?>" <?php echo e(old('kategori_surat_id') == $k->id ? 'selected' : ''); ?>>
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

                        <!-- Status Surat -->
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
                                <option value="baru" <?php echo e(old('status', 'baru') == 'baru' ? 'selected' : ''); ?>>Baru</option>
                                <option value="diproses" <?php echo e(old('status') == 'diproses' ? 'selected' : ''); ?>>Diproses</option>
                                <option value="didisposisikan" <?php echo e(old('status') == 'didisposisikan' ? 'selected' : ''); ?>>Didisposisikan</option>
                                <option value="selesai" <?php echo e(old('status') == 'selesai' ? 'selected' : ''); ?>>Selesai</option>
                                <option value="diarsipkan" <?php echo e(old('status') == 'diarsipkan' ? 'selected' : ''); ?>>Diarsipkan</option>
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
unset($__errorArgs, $__bag); ?>"><?php echo e(old('perihal')); ?></textarea>
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

                <!-- Section 2: Lampiran & Lokasi Fisik -->
                <div class="space-y-5 pt-2">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <div class="w-2 h-5 bg-indigo-600 rounded-full"></div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Lampiran Dokumen & Arsip Fisik</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                        
                        <!-- Kolom Kiri: Berkas Digital (Upload / Scan Kamera) -->
                        <div class="border border-slate-200/80 bg-slate-50/40 rounded-2xl p-5 flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">
                                    Berkas Digital (Opsional)
                                </label>

                                <!-- Tab Toggle Mode -->
                                <div class="flex gap-1 p-1 bg-slate-200/60 rounded-xl w-fit text-xs font-semibold mb-3">
                                    <button type="button" onclick="switchMode('upload')" id="btn-upload" class="px-3 py-1.5 rounded-lg bg-white text-blue-600 shadow-xs transition">Upload File</button>
                                    <button type="button" onclick="switchMode('camera')" id="btn-camera" class="px-3 py-1.5 rounded-lg text-slate-600 transition">Scan Kamera</button>
                                </div>
                            </div>

                            <!-- Mode 1: Drag & Drop / Selection File Box -->
                            <div id="mode-upload" class="flex-1 flex flex-col justify-center">
                                <label id="upload-box-label" class="relative flex-1 flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-white hover:bg-slate-50/80 p-6 text-center transition duration-150 group min-h-[140px] <?php $__errorArgs = ['lampiran_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 mb-2 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700" id="file-label-text">Klik untuk memilih file baru</span>
                                    <span class="text-[11px] text-slate-400 mt-0.5">Format PDF, JPG, atau PNG (Maks 10MB)</span>
                                    <input type="file" name="lampiran_file" id="lampiran_file" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="updateFileName(this)">
                                </label>
                            </div>

                            <!-- Mode 2: Scan Kamera HP -->
                            <div id="mode-camera" class="hidden space-y-3 flex-1 flex flex-col justify-between">
                                <div id="camera-container" class="relative w-full aspect-[4/3] bg-slate-900 rounded-xl overflow-hidden shadow-inner flex items-center justify-center border border-slate-200">
                                    <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <img id="image-preview" class="hidden w-full h-full object-contain bg-slate-950" alt="Preview Scan" />
                                    <div id="camera-placeholder" class="absolute text-white text-xs font-medium">Kamera belum aktif</div>
                                </div>
                                
                                <div class="flex flex-wrap justify-center gap-2 pt-1">
                                    <button type="button" id="start-cam-btn" onclick="startCamera()" class="px-3.5 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-blue-700 transition">Nyalakan Kamera</button>
                                    <button type="button" id="capture-btn" onclick="takeSnapshot()" class="hidden px-3.5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-emerald-700 transition">Ambil Foto / Scan</button>
                                    <button type="button" id="retake-btn" onclick="retakeSnapshot()" class="hidden px-3.5 py-2 bg-amber-500 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-amber-600 transition">Foto Ulang</button>
                                    <button type="button" id="stop-cam-btn" onclick="stopCamera()" class="hidden px-3.5 py-2 bg-rose-600 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-rose-700 transition">Tutup Kamera</button>
                                </div>
                                <input type="hidden" name="captured_image" id="captured_image" value="<?php echo e(old('captured_image')); ?>">
                                <div id="snapshot-preview" class="<?php echo e(old('captured_image') ? '' : 'hidden'); ?> text-center text-xs font-semibold text-emerald-600">✓ Hasil scan berhasil diambil!</div>
                            </div>

                            <div class="mt-2">
                                <?php $__errorArgs = ['lampiran_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['captured_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Lokasi Fisik -->
                        <div class="border border-slate-200/80 bg-slate-50/40 rounded-2xl p-5 flex flex-col justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">
                                    Lokasi Penyimpanan Arsip Fisik
                                </label>
                                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                                    Tentukan lokasi penyimpanan berkas cetak/fisik untuk mempermudah pencarian berkas asli di lemari penyimpanan.
                                </p>
                            </div>
                            
                            <div class="space-y-2 bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs mt-auto">
                                <label class="block text-xs font-semibold text-slate-600 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    Detail Posisi Lemari / Box:
                                </label>
                                <input type="text" name="lokasi_arsip_fisik" value="<?php echo e(old('lokasi_arsip_fisik')); ?>" placeholder="Contoh: Rak A-3 Box 12"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 <?php $__errorArgs = ['lokasi_arsip_fisik'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-rose-500 !bg-rose-50/30 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['lokasi_arsip_fisik'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-xs mt-1.5 font-medium"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Action Buttons Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 px-6 sm:px-8 py-4 bg-slate-50/80 border-t border-slate-100">
                <a href="<?php echo e(route('surat-masuk.index')); ?>" class="w-full sm:w-auto px-5 py-2.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 hover:text-slate-800 transition shadow-xs text-center">
                    Batal
                </a>
                <button type="submit" id="submit-btn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-md shadow-blue-600/30 transition disabled:opacity-50">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Surat Masuk
                </button>
            </div>

        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    let videoStream = null;

    function switchMode(mode) {
        stopCamera();
        
        if (mode === 'upload') {
            document.getElementById('mode-upload').classList.remove('hidden');
            document.getElementById('mode-camera').classList.add('hidden');
            document.getElementById('btn-upload').classList.add('bg-white', 'text-blue-600', 'shadow-xs');
            document.getElementById('btn-camera').classList.remove('bg-white', 'text-blue-600', 'shadow-xs');
        } else {
            document.getElementById('mode-upload').classList.add('hidden');
            document.getElementById('mode-camera').classList.remove('hidden');
            document.getElementById('btn-camera').classList.add('bg-white', 'text-blue-600', 'shadow-xs');
            document.getElementById('btn-upload').classList.remove('bg-white', 'text-blue-600', 'shadow-xs');
        }
    }

    function updateFileName(input) {
        const textElement = document.getElementById('file-label-text');
        if (input.files && input.files[0]) {
            textElement.textContent = "Terpilih: " + input.files[0].name;
            textElement.classList.add('text-blue-600', 'font-bold');
        } else {
            textElement.textContent = "Klik untuk memilih file baru";
            textElement.classList.remove('text-blue-600', 'font-bold');
        }
    }

    async function startCamera() {
        const video = document.getElementById('video');
        document.getElementById('camera-placeholder').style.display = 'none';
        document.getElementById('image-preview').classList.add('hidden');
        video.classList.remove('hidden');

        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }, 
                audio: false 
            });
            video.srcObject = videoStream;
            document.getElementById('start-cam-btn').classList.add('hidden');
            document.getElementById('capture-btn').classList.remove('hidden');
            document.getElementById('stop-cam-btn').classList.remove('hidden');
            document.getElementById('retake-btn').classList.add('hidden');
        } catch (err) {
            alert('Gagal mengakses kamera. Pastikan Anda memberikan izin akses kamera pada browser.');
            document.getElementById('camera-placeholder').style.display = 'block';
        }
    }

    function stopCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        const video = document.getElementById('video');
        if (video) video.srcObject = null;
        
        const placeholder = document.getElementById('camera-placeholder');
        if (placeholder && document.getElementById('captured_image').value === '') {
            placeholder.style.display = 'block';
        }

        const startBtn = document.getElementById('start-cam-btn');
        const capBtn = document.getElementById('capture-btn');
        const stopBtn = document.getElementById('stop-cam-btn');
        if (startBtn) startBtn.classList.remove('hidden');
        if (capBtn) capBtn.classList.add('hidden');
        if (stopBtn) stopBtn.classList.add('hidden');
    }

    function takeSnapshot() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const imgPreview = document.getElementById('image-preview');

        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85); 
        document.getElementById('captured_image').value = dataUrl;

        imgPreview.src = dataUrl;
        imgPreview.classList.remove('hidden');
        video.classList.add('hidden');
        
        stopCamera();

        document.getElementById('start-cam-btn').classList.add('hidden');
        document.getElementById('retake-btn').classList.remove('hidden');
        document.getElementById('snapshot-preview').classList.remove('hidden');
        document.getElementById('camera-container').classList.add('border-2', 'border-emerald-500');
    }

    function retakeSnapshot() {
        document.getElementById('captured_image').value = '';
        document.getElementById('snapshot-preview').classList.add('hidden');
        document.getElementById('camera-container').classList.remove('border-2', 'border-emerald-500');
        startCamera();
    }

    document.getElementById('form-surat').addEventListener('submit', function() {
        stopCamera(); // Pastikan stream kamera dimatikan sebelum form submit
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
        `;
    });

    window.addEventListener('beforeunload', () => {
        stopCamera();
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\user\Downloads\E-Arsip\resources\views/surat_masuk/create.blade.php ENDPATH**/ ?>