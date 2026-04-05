<?php

return [

    'defaults' => [
        'guard' => 'sanctum',
        'passwords' => 'giangviens',
    ],

    'guards' => [
        'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'giangviens',
        ],
        'giangvien' => [
            'driver' => 'session',
            'provider' => 'giangviens',
        ],
    ],

    'providers' => [
        'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
        'giangviens' => [
            'driver' => 'eloquent',
            'model' => App\Models\GiangVien::class,
        ],
    ],

    'passwords' => [
        'giangviens' => [
            'provider' => 'giangviens',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];
