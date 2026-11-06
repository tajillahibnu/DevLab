<?php

use Illuminate\Support\Facades\Route;

Route::view('/{path?}', 'transcriptions.index')->where('path', '^(?!api|sanctum|build|storage|up).*$')->name('transcriptions.index');
