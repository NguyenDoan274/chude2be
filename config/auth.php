<?php

return [

    'defaults' => [
        'guard' => 'giangvien',
        'passwords' => 'giangviens',
    ],

    'guards' => [
        'web' => [
        'driver' => 'session',
        'provider' => 'users',
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
