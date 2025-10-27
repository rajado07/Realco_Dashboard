<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/rawdata',  // Tambahkan rute API yang Anda gunakan
        // Anda bisa menambahkan lebih banyak rute jika diperlukan
    ];
}
