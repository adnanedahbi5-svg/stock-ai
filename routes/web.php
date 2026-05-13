<?php

use Illuminate\Support\Facades\Route;

/*
| On ne définit plus de logique ici. 
| On dit simplement à Laravel : "Peu importe l'URL (dashboard, login, etc.), 
| renvoie la vue 'app.blade.php'. Le Vue Router s'occupera du reste."
*/

Route::get('/{any}', function () {
    return view('app'); // Assure-toi d'avoir resources/views/app.blade.php
})->where('any', '.*');