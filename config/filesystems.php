<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */

    'default' => env(
        'FILESYSTEM_DISK',
        'supabase'
    ),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */

    'disks' => [

        /*
        |--------------------------------------------------------------------------
        | LOCAL
        |--------------------------------------------------------------------------
        |
        | Digunakan untuk file internal Laravel.
        | BUKAN untuk menyimpan lampiran surat permanen.
        |
        */

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => true,
            'report' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | PUBLIC
        |--------------------------------------------------------------------------
        |
        | Tetap tersedia untuk kebutuhan asset/file lokal lain.
        | Lampiran surat tidak menggunakan disk ini.
        |
        */

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(
                env(
                    'APP_URL',
                    'http://localhost'
                ),
                '/'
            ) . '/storage',
            'visibility' => 'public',
            'throw' => true,
            'report' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | SUPABASE STORAGE
        |--------------------------------------------------------------------------
        |
        | Storage permanen E-Arsip.
        |
        | Alur:
        |
        | Browser
        |   ↓
        | PHP temporary upload
        |   ↓
        | JPG/PNG → compression GD
        |   ↓
        | Supabase Storage
        |
        | File asli tidak disimpan permanen di container/server.
        |
        */

        'supabase' => [
            'driver' => 's3',

            'key' => env(
                'SUPABASE_S3_ACCESS_KEY_ID'
            ),

            'secret' => env(
                'SUPABASE_S3_SECRET_ACCESS_KEY'
            ),

            'region' => env(
                'SUPABASE_S3_REGION',
                'ap-northeast-2'
            ),

            'bucket' => env(
                'SUPABASE_STORAGE_BUCKET'
            ),

            'endpoint' => env(
                'SUPABASE_S3_ENDPOINT'
            ),

            'use_path_style_endpoint' => filter_var(
                env(
                    'AWS_USE_PATH_STYLE_ENDPOINT',
                    true
                ),
                FILTER_VALIDATE_BOOL
            ),

            /*
            | Penting:
            | true membuat kegagalan upload langsung
            | melempar exception sehingga bisa dicatat di log.
            */

            'throw' => true,

            'report' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | S3 COMPATIBILITY ALIAS
        |--------------------------------------------------------------------------
        |
        | Dipertahankan untuk kompatibilitas apabila ada kode lama
        | yang masih menggunakan Storage::disk('s3').
        |
        */

        's3' => [
            'driver' => 's3',

            'key' => env(
                'SUPABASE_S3_ACCESS_KEY_ID'
            ),

            'secret' => env(
                'SUPABASE_S3_SECRET_ACCESS_KEY'
            ),

            'region' => env(
                'SUPABASE_S3_REGION',
                'ap-northeast-2'
            ),

            'bucket' => env(
                'SUPABASE_STORAGE_BUCKET'
            ),

            'endpoint' => env(
                'SUPABASE_S3_ENDPOINT'
            ),

            'use_path_style_endpoint' => filter_var(
                env(
                    'AWS_USE_PATH_STYLE_ENDPOINT',
                    true
                ),
                FILTER_VALIDATE_BOOL
            ),

            'throw' => true,

            'report' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];