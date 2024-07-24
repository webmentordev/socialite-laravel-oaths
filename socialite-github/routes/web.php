<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\ProfileController;

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

// GitHub OAuth
Route::get('/auth/redirect', function () {
    return Socialite::driver('github')->redirect();
})->name('github.auth');

Route::get('/auth/callback', function () {
    $githubUser = Socialite::driver('github')->user();
    $user = User::where('email', $githubUser->email)->first();
    if ($user) {
        $user->update([
            'github_id' => $githubUser->id,
            'name' => $githubUser->name,
            'github_token' => $githubUser->token,
            'github_refresh_token' => $githubUser->refreshToken,
        ]);
    } else {
        $user = User::create([
            'github_id' => $githubUser->id,
            'name' => $githubUser->name,
            'email' => $githubUser->email,
            'github_token' => $githubUser->token,
            'github_refresh_token' => $githubUser->refreshToken,
        ]);
    }
    Auth::login($user);
    return redirect('/dashboard');
});

require __DIR__ . '/auth.php';