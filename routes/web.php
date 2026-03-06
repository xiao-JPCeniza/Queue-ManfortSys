<?php

use App\Http\Controllers\BploOfficeController;
use App\Http\Controllers\HrmoOfficeController;
use App\Http\Controllers\OfficeDashboardController;
use App\Http\Controllers\OfficeQueueReportsPdfController;
use App\Http\Controllers\SuperAdminQueueReportsController;
use App\Livewire\Auth\Login;
use App\Livewire\ClientDashboard;
use App\Livewire\OfficeAdmin\Dashboard as OfficeAdminDashboard;
use App\Livewire\QueueJoin;
use App\Livewire\QueueMaster\Dashboard as QueueMasterDashboard;
use App\Livewire\QueueMaster\OfficeManage as QueueMasterOfficeManage;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

Route::view('/welcome', 'welcome')->name('welcome');

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', function () {
            $user = Auth::user();
            $user->load('role');
            if ($user->isSuperAdmin()) {
                return redirect()->route('super-admin.index');
            }
            if ($user->isQueueMaster()) {
                return redirect()->route('queue-master.index');
            }
            if ($user->isOfficeAdmin() && $user->office_id) {
                return redirect()->route('office.dashboard', $user->office->slug);
            }

            return redirect()->route('queue-master.index');
        })->name('dashboard');

    Route::get('/profile', function () {
        $user = Auth::user();
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

    Route::middleware(['role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', QueueMasterDashboard::class)->name('index');
        Route::get('/reports', function () {
            $officeModel = Office::where('slug', 'hrmo')->firstOrFail();

            return view('office.dashboard', ['office' => $officeModel]);
        })->name('reports');
        Route::get('/queue-management', function () {
            $officeModel = Office::where('slug', 'hrmo')->firstOrFail();

            return view('office.dashboard', ['office' => $officeModel]);
        })->name('queue-management');
        Route::get('/queue-reports', SuperAdminQueueReportsController::class)->name('queue-reports');
        Route::get('/queue-reports/pdf', OfficeQueueReportsPdfController::class)->name('queue-reports.pdf');
    });

    Route::middleware(['office.access'])->group(function () {
        Route::get('/office/{office}', OfficeDashboardController::class)->name('office.dashboard');
        Route::get('/office/{office}/monitor', HrmoOfficeController::class)->name('office.hrmo.monitor');
        Route::get('/office/{office}/bplo-monitor', BploOfficeController::class)->name('office.bplo.monitor');
        Route::get('/office/{office}/queue-reports/pdf', OfficeQueueReportsPdfController::class)->name('office.queue-reports.pdf');
    });
});

Route::get('/queue', ClientDashboard::class)->name('queue.client');
Route::get('/queue/join/{office}', QueueJoin::class)->name('queue.join');
