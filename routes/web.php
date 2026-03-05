<?php

use App\Http\Controllers\HrmoOfficeController;
use App\Http\Controllers\OfficeDashboardController;
use App\Livewire\Auth\Login;
use App\Livewire\ClientDashboard;
use App\Livewire\OfficeAdmin\Dashboard as OfficeAdminDashboard;
use App\Livewire\QueueJoin;
use App\Livewire\QueueMaster\Dashboard as QueueMasterDashboard;
use App\Livewire\QueueMaster\OfficeManage as QueueMasterOfficeManage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

Route::view('/welcome', 'welcome')->name('welcome');

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $user->load('role');
        if ($user->isSuperAdmin() || $user->isQueueMaster()) {
            return redirect()->route('queue-master.index');
        }
        if ($user->isOfficeAdmin() && $user->office_id) {
            return redirect()->route('office.dashboard', $user->office->slug);
        }
        return redirect()->route('queue-master.index');
    })->name('dashboard');

    Route::get('/profile', function () {
        $user = auth()->user();
        $user->loadMissing(['role', 'office']);

        return view('profile', ['user' => $user]);
    })->name('profile');

    Route::post('/profile/photo', function (Request $request) {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $validated['photo']->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return back()->with('success', 'Profile photo updated.');
    })->name('profile.photo.update');

    Route::middleware(['role:super_admin,queue_master'])->prefix('queue-master')->name('queue-master.')->group(function () {
        Route::get('/', QueueMasterDashboard::class)->name('index');
        Route::get('/office/{office}', QueueMasterOfficeManage::class)->name('office');
    });

    Route::middleware(['office.access'])->group(function () {
        Route::get('/office/{office}', OfficeDashboardController::class)->name('office.dashboard');
        Route::get('/office/{office}/monitor', HrmoOfficeController::class)->name('office.hrmo.monitor');
    });
});

Route::get('/queue', ClientDashboard::class)->name('queue.client');
Route::get('/queue/join/{office}', QueueJoin::class)->name('queue.join');
