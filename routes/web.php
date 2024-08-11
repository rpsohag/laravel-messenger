<?php

use App\Http\Controllers\MessengerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::group(["middleware" => "auth"], function(){
    Route::get('/messenger', [MessengerController::class, 'index'])->name('messenger.index');
    Route::post('/profile', [UserProfileController::class,'update'])->name('profile.update');
    Route::get('/messenger/search', [MessengerController::class, 'userSearch'])->name('user.search');
    Route::get('/messenger/id-info', [MessengerController::class, 'fetchIdInfo'])->name("messenger.idInfo");
    Route::post('/messenger/send-message', [MessengerController::class, 'sendMessage'])->name("messenger.sendMessage");
    Route::get('/messenger/fetch-message', [MessengerController::class, 'fetchMessages'])->name("messenger.fetchMessages");
    Route::get('/messenger/fetch-contacts', [MessengerController::class, 'fetchContacts'])->name("messenger.fetchContacts");
    Route::get('/messenger/update-contacts', [MessengerController::class, 'updateContacts'])->name("messenger.updateContacts");
    Route::post('/messenger/make-seen', [MessengerController::class, 'makeSeen'])->name("messenger.makeSeen");
});
