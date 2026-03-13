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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

Route::view('/welcome', 'welcome')->name('welcome');
Route::view('/live-monitor', 'office.all-offices-monitor')->name('live-monitor.public');
Route::get('/session/pulse', function (Request $request) {
    $request->session()->put('_session_pulse_at', now()->timestamp);

    return response()->json([
        'token' => csrf_token(),
        'authenticated' => Auth::check(),
    ]);
})->name('session.pulse');

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

    Route::post('/profile/name', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => 'The full name field is required.',
            ]);
        }

        $request->user()->update(['name' => $name]);

        return back()->with('success', 'Full name updated.');
    })->name('profile.name.update');

    Route::post('/profile/password', function (Request $request) {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.different' => 'The new password must be different from the current password.',
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password updated.');
    })->name('profile.password.update');

    Route::get('/profile/photo/{user}', function (User $user) {
        abort_unless($user->profile_photo_path, 404);
        $relativePath = str_replace(['../', '..\\'], '', $user->profile_photo_path);
        $absolutePath = storage_path('app/public/'.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));

        abort_unless(is_file($absolutePath), 404);

        $extension = strtolower(pathinfo($user->profile_photo_path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
        ];

        return response(file_get_contents($absolutePath), 200, [
            'Content-Type' => $mimeTypes[$extension] ?? 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    })->name('profile.photo.show');

    Route::post('/profile/photo', function (Request $request) {
        $validated = $request->validate([
            'photo' => ['required', 'file', 'max:2048', 'extensions:jpg,jpeg,png,gif,webp,bmp'],
        ]);

        if (@getimagesize($validated['photo']->getRealPath()) === false) {
            throw ValidationException::withMessages([
                'photo' => 'The photo must be a valid JPG, PNG, GIF, WEBP, or BMP image.',
            ]);
        }

        $user = $request->user();
        $previousPath = $user->profile_photo_path;
        $storageRoot = storage_path('app/public');
        $destinationDirectory = $storageRoot.DIRECTORY_SEPARATOR.'profile-photos';

        if (! is_dir($destinationDirectory) && ! mkdir($destinationDirectory, 0755, true) && ! is_dir($destinationDirectory)) {
            throw ValidationException::withMessages([
                'photo' => 'Unable to prepare storage for the uploaded photo.',
            ]);
        }

        $extension = strtolower($validated['photo']->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::uuid().'.'.$extension;
        $path = 'profile-photos/'.$filename;

        try {
            $validated['photo']->move($destinationDirectory, $filename);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'photo' => 'Unable to save the uploaded photo.',
            ]);
        }

        $user->update(['profile_photo_path' => $path]);

        if ($previousPath) {
            $previousRelativePath = str_replace(['../', '..\\'], '', $previousPath);
            $previousAbsolutePath = $storageRoot.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $previousRelativePath);

            if (is_file($previousAbsolutePath)) {
                @unlink($previousAbsolutePath);
            }
        }

        return back()->with('success', 'Profile photo updated.');
    })->name('profile.photo.update');

    Route::middleware(['role:super_admin,queue_master'])->prefix('queue-master')->name('queue-master.')->group(function () {
        Route::get('/', QueueMasterDashboard::class)->name('index');
        Route::redirect('/live-monitor', '/live-monitor')->name('live-monitor');
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
        Route::get('/offices', function () {
            return view('super-admin.offices');
        })->name('offices');
        Route::get('/user-management', function () {
            $officeModel = Office::where('slug', 'hrmo')->firstOrFail();

            return view('office.dashboard', ['office' => $officeModel]);
        })->name('user-management');
        Route::get('/queue-reports', SuperAdminQueueReportsController::class)->name('queue-reports');
        Route::get('/queue-reports/pdf', OfficeQueueReportsPdfController::class)->name('queue-reports.pdf');
    });

    Route::middleware(['office.access'])->group(function () {
        Route::get('/office/{office}', OfficeDashboardController::class)->name('office.dashboard');
        Route::get('/office/{office}/monitor', HrmoOfficeController::class)->name('office.hrmo.monitor');
        Route::get('/office/{office}/bplo-monitor', BploOfficeController::class)->name('office.bplo.monitor');
    });
});

Route::get('/queue', ClientDashboard::class)->name('queue.client');
Route::get('/queue/join/{office}', QueueJoin::class)->name('queue.join');
