<?php

use App\Http\Controllers\OfficeDashboardController;
use App\Livewire\Auth\Login;
use App\Livewire\ClientDashboard;
use App\Livewire\OfficeAdmin\Dashboard as OfficeAdminDashboard;
use App\Livewire\QueueJoin;
use App\Livewire\QueueMaster\Dashboard as QueueMasterDashboard;
use App\Livewire\QueueMaster\OfficeManage as QueueMasterOfficeManage;
use App\Livewire\SuperAdmin\OfficesManage as SuperAdminOfficesManage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

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

    Route::middleware(['role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/offices', SuperAdminOfficesManage::class)->name('offices');
    });

    Route::middleware(['role:super_admin,queue_master'])->prefix('queue-master')->name('queue-master.')->group(function () {
        Route::get('/', QueueMasterDashboard::class)->name('index');
        Route::get('/office/{office}', QueueMasterOfficeManage::class)->name('office');
    });

    Route::middleware(['office.access'])->group(function () {
        Route::get('/office/{office}', OfficeDashboardController::class)->name('office.dashboard');
    });
});

Route::get('/queue', ClientDashboard::class)->name('queue.client');
Route::get('/queue/join/{office}', QueueJoin::class)->name('queue.join');
