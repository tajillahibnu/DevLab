<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, false)) {
        return response()->json(['message' => 'Email atau kata sandi tidak sesuai.'], 422);
    }

    $request->session()->regenerate();

    return response()->json(['data' => $request->user()]);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Anda telah keluar.']);
    });

    Route::get('/user', fn (Request $request) => response()->json($request->user()));
});
