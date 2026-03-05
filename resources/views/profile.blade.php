@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="mx-auto w-full max-w-3xl">
        <section class="lgu-card p-6 sm:p-8" aria-labelledby="profile-heading">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 id="profile-heading" class="lgu-page-title">Profile</h1>
                    <p class="mt-1 text-sm text-slate-600">Account details for the currently signed-in user.</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}"
                             alt="Profile photo of {{ $user->name }}"
                             class="h-20 w-20 rounded-full border border-slate-200 object-cover">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-full border border-slate-200 bg-blue-50 text-xl font-bold text-blue-700">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>
            </div>

            <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                @csrf
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="photo" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Profile Photo</label>
                        <input id="photo"
                               name="photo"
                               type="file"
                               accept="image/*"
                               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-slate-500">Accepted image files up to 2MB.</p>
                    </div>
                    <button type="submit" class="lgu-btn px-4 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-medium hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Upload Photo
                    </button>
                </div>
                @error('photo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>

            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Name</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-800">{{ $user->name }}</dd>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Email</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-800">{{ $user->email }}</dd>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Role</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-800">{{ $user->role?->name ?? 'Not assigned' }}</dd>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Office</dt>
                    <dd class="mt-1 text-base font-semibold text-slate-800">{{ $user->office?->name ?? 'Not assigned' }}</dd>
                </div>
            </dl>
        </section>
    </div>
@endsection
