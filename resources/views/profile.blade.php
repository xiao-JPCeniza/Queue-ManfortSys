@extends('layouts.app')

@section('title', 'Personnel Profile')

@section('content')
    @php($officeName = $user->office?->name ?? 'Municipal Administration')
    @php($roleName = $user->role?->name ?? 'Authorized User')

    <div class="gov-profile-shell mx-auto w-full max-w-6xl">
        <section class="gov-profile-masthead" aria-labelledby="profile-heading">
            <div class="gov-profile-banner-copy">
                <div class="gov-profile-seal-wrap">
                    <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich official seal" class="gov-profile-seal">
                </div>

                <div>
                    <p class="gov-profile-kicker">LGU of Manolo Fortich Bukidnon</p>
                    <h1 id="profile-heading" class="gov-font-heading gov-profile-title">Personnel Profile Record</h1>
                    <p class="gov-profile-subtitle">
                        Official identity and access record for authorized LGU queue system personnel.
                    </p>
                </div>
            </div>

            <div class="gov-profile-meta" aria-label="Record summary">
                <span class="gov-profile-chip gov-profile-chip-strong">{{ $roleName }}</span>
            </div>
        </section>

        <div class="gov-profile-grid">
            <section class="gov-profile-card gov-profile-identity-card" aria-labelledby="identity-summary-heading">
                <div class="gov-profile-photo-frame">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}"
                             alt="Profile photo of {{ $user->name }}"
                             class="gov-profile-photo">
                    @else
                        <div class="gov-profile-photo-fallback">{{ $user->initials }}</div>
                    @endif
                </div>

                <p class="gov-profile-section-kicker">Personnel Identity</p>
                <h2 id="identity-summary-heading" class="gov-font-heading gov-profile-name">{{ $user->name }}</h2>
                <p class="gov-profile-office">{{ $officeName }}</p>

                <div class="gov-profile-badge-row">
                    <span class="gov-profile-badge gov-profile-badge-active">Active Account</span>
                    <span class="gov-profile-badge gov-profile-badge-muted">{{ $roleName }}</span>
                </div>
            </section>

            <section class="gov-profile-card gov-profile-utility-card" aria-labelledby="photo-registry-heading">
                <div class="gov-profile-card-head">
                    <div>
                        <p class="gov-profile-section-kicker">Photo Registry</p>
                        <h2 id="photo-registry-heading" class="gov-font-heading gov-profile-section-title">Update Identification Photo</h2>
                    </div>
                    <p class="gov-profile-section-copy">Use a recent, clear headshot for internal staff identification.</p>
                </div>

                <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="gov-profile-form">
                    @csrf

                    <div>
                        <label for="photo" class="gov-profile-label">Select Image File</label>
                        <input id="photo"
                               name="photo"
                               type="file"
                               accept="image/*"
                               class="gov-profile-input @error('photo') gov-profile-input-error @enderror">
                        <p class="gov-profile-help">Accepted JPG, PNG, GIF, WEBP, or BMP files up to 2MB.</p>
                    </div>

                    @error('photo')
                        <p class="gov-profile-error" role="alert">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="gov-profile-submit">
                        Upload Updated Photo
                    </button>
                </form>
            </section>

            <section class="gov-profile-card gov-profile-note-card" aria-labelledby="profile-guidance-heading">
                <p class="gov-profile-section-kicker">Personnel Notice</p>
                <h2 id="profile-guidance-heading" class="gov-font-heading gov-profile-section-title">Record Handling Guidance</h2>
                <ul class="gov-profile-note-list">
                    <li>Ensure profile details reflect your current office assignment.</li>
                    <li>Use only official LGU-issued email credentials for account access.</li>
                    <li>Coordinate account corrections with the municipal system administrator.</li>
                </ul>
            </section>

            <section class="gov-profile-card gov-profile-records-card" aria-labelledby="account-sheet-heading">
                <div class="gov-profile-card-head gov-profile-card-head-wide">
                    <div>
                        <p class="gov-profile-section-kicker">Official Record</p>
                        <h2 id="account-sheet-heading" class="gov-font-heading gov-profile-section-title">Account Information Sheet</h2>
                    </div>
                    <p class="gov-profile-section-copy">Validated profile details used across municipal queue operations.</p>
                </div>

                <dl class="gov-profile-fields">
                    <div class="gov-profile-field gov-profile-field-emphasis">
                        <dt>Full Name</dt>
                        <dd>
                            <form action="{{ route('profile.name.update') }}" method="POST" class="gov-profile-inline-form">
                                @csrf
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       maxlength="255"
                                       class="gov-profile-inline-input @error('name') gov-profile-inline-input-error @enderror"
                                       aria-label="Full name">
                                @error('name')
                                    <p class="gov-profile-inline-error" role="alert">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="gov-profile-inline-submit">Save Full Name</button>
                            </form>
                        </dd>
                    </div>

                    <div class="gov-profile-field">
                        <dt>Government Email</dt>
                        <dd>{{ $user->email }}</dd>
                    </div>

                    <div class="gov-profile-field">
                        <dt>Assigned Role</dt>
                        <dd>{{ $roleName }}</dd>
                    </div>

                    <div class="gov-profile-field">
                        <dt>Assigned Office</dt>
                        <dd>{{ $officeName }}</dd>
                    </div>

                    <div class="gov-profile-field gov-profile-field-wide gov-profile-field-emphasis">
                        <dt>Account Password</dt>
                        <dd>
                            <form action="{{ route('profile.password.update') }}" method="POST" class="gov-profile-inline-form gov-profile-password-form">
                                @csrf

                                <p class="gov-profile-inline-help">Update your account password here. Use at least 8 characters and keep it private.</p>

                                <div class="gov-profile-password-grid">
                                    <div>
                                        <label for="current_password" class="gov-profile-label">Current Password</label>
                                        <div class="gov-profile-password-input-wrap">
                                            <input type="password"
                                                   id="current_password"
                                                   name="current_password"
                                                   autocomplete="current-password"
                                                   class="gov-profile-inline-input gov-profile-inline-input-password @error('current_password') gov-profile-inline-input-error @enderror"
                                                   aria-label="Current password">
                                            <button type="button"
                                                    class="gov-profile-password-toggle"
                                                    data-password-target="current_password"
                                                    aria-label="Show current password"
                                                    aria-pressed="false">
                                                Show
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <p class="gov-profile-inline-error" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="password" class="gov-profile-label">New Password</label>
                                        <div class="gov-profile-password-input-wrap">
                                            <input type="password"
                                                   id="password"
                                                   name="password"
                                                   autocomplete="new-password"
                                                   class="gov-profile-inline-input gov-profile-inline-input-password @error('password') gov-profile-inline-input-error @enderror"
                                                   aria-label="New password">
                                            <button type="button"
                                                    class="gov-profile-password-toggle"
                                                    data-password-target="password"
                                                    aria-label="Show new password"
                                                    aria-pressed="false">
                                                Show
                                            </button>
                                        </div>
                                        @error('password')
                                            <p class="gov-profile-inline-error" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="password_confirmation" class="gov-profile-label">Confirm Password</label>
                                        <div class="gov-profile-password-input-wrap">
                                            <input type="password"
                                                   id="password_confirmation"
                                                   name="password_confirmation"
                                                   autocomplete="new-password"
                                                   class="gov-profile-inline-input gov-profile-inline-input-password"
                                                   aria-label="Confirm new password">
                                            <button type="button"
                                                    class="gov-profile-password-toggle"
                                                    data-password-target="password_confirmation"
                                                    aria-label="Show password confirmation"
                                                    aria-pressed="false">
                                                Show
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="gov-profile-inline-submit">Update Password</button>
                            </form>
                        </dd>
                    </div>

                    <div class="gov-profile-field gov-profile-field-wide">
                        <dt>Record Status</dt>
                        <dd>Authorized for queue system access</dd>
                    </div>
                </dl>
            </section>

        </div>
@endsection

@once
    <style>
        .gov-profile-shell {
            --gov-blue-950: #0a2d55;
            --gov-blue-900: #154777;
            --gov-blue-800: #2a5f97;
            --gov-blue-100: #dce8f6;
            --gov-gold-500: #b98a2b;
            --gov-gold-100: #f7efdc;
            --gov-emerald-600: #0f8a62;
            --gov-emerald-100: #d9f4ea;
            --gov-ink-900: #17283f;
            --gov-ink-700: #435978;
            --gov-ink-500: #6a7f9a;
            --gov-border: #d6e1ee;
            --gov-surface: #ffffff;
            position: relative;
            display: grid;
            gap: 1.5rem;
        }

        .gov-profile-shell::before,
        .gov-profile-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.45;
        }

        .gov-profile-shell::before {
            width: 17rem;
            height: 17rem;
            top: -3rem;
            right: -2rem;
            background: radial-gradient(circle at 30% 30%, rgb(219 234 254 / 0.9), transparent 68%);
        }

        .gov-profile-shell::after {
            width: 14rem;
            height: 14rem;
            left: -2rem;
            bottom: 3rem;
            background: radial-gradient(circle at 50% 50%, rgb(247 239 220 / 0.9), transparent 68%);
        }

        .gov-profile-masthead,
        .gov-profile-card {
            position: relative;
            z-index: 1;
        }

        .gov-profile-masthead {
            overflow: hidden;
            border: 1px solid #c8d7e9;
            border-radius: 1.4rem;
            padding: 1.35rem 1.45rem;
            background:
                radial-gradient(circle at top right, rgb(255 255 255 / 0.16), transparent 42%),
                linear-gradient(125deg, var(--gov-blue-950), var(--gov-blue-900));
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.2rem;
            box-shadow: 0 26px 42px -34px rgb(10 45 85 / 0.52);
            animation: gov-profile-rise 420ms ease-out both;
        }

        .gov-profile-masthead::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.32rem;
            background: linear-gradient(90deg, #1d4ed8 0%, #1d4ed8 62%, #b98a2b 62%, #b98a2b 82%, #be123c 82%, #be123c 100%);
        }

        .gov-profile-banner-copy {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .gov-profile-seal-wrap {
            width: 5.8rem;
            height: 5.8rem;
            padding: 0.42rem;
            border-radius: 999px;
            background: rgb(255 255 255 / 0.14);
            border: 1px solid rgb(255 255 255 / 0.28);
            box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.08);
            flex-shrink: 0;
        }

        .gov-profile-seal {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
            background: rgb(255 255 255 / 0.88);
        }

        .gov-profile-kicker {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 700;
            color: rgb(219 234 254 / 0.98);
        }

        .gov-profile-title {
            margin: 0.35rem 0 0;
            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
            line-height: 1.08;
            color: #fff;
        }

        .gov-profile-subtitle {
            margin: 0.45rem 0 0;
            max-width: 42rem;
            color: rgb(226 232 240 / 0.94);
            font-size: 0.96rem;
            line-height: 1.6;
        }

        .gov-profile-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.55rem;
        }

        .gov-profile-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgb(255 255 255 / 0.12);
            border: 1px solid rgb(255 255 255 / 0.18);
            color: #fff;
            font-size: 0.76rem;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .gov-profile-chip-strong {
            background: rgb(185 138 43 / 0.26);
            border-color: rgb(247 239 220 / 0.36);
        }

        .gov-profile-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.5rem;
            align-items: stretch;
        }

        .gov-profile-card {
            border: 1px solid var(--gov-border);
            border-radius: 1.3rem;
            background:
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 22px 36px -34px rgb(15 63 115 / 0.38);
            animation: gov-profile-rise 480ms ease-out both;
        }

        .gov-profile-records-card {
            grid-column: 1 / -1;
        }

        .gov-profile-identity-card {
            overflow: hidden;
            padding: 1.5rem 1.4rem;
            text-align: center;
            background:
                radial-gradient(circle at top center, rgb(220 232 246 / 0.85), transparent 48%),
                linear-gradient(180deg, #fdfefe 0%, #f6faff 100%);
        }

        .gov-profile-utility-card {
            display: flex;
            flex-direction: column;
        }

        .gov-profile-photo-frame {
            width: 10rem;
            height: 10rem;
            margin: 0 auto 1.15rem;
            padding: 0.42rem;
            border-radius: 999px;
            background:
                linear-gradient(145deg, rgb(185 138 43 / 0.88), rgb(21 71 119 / 0.88));
            box-shadow:
                0 0 0 0.45rem rgb(255 255 255 / 0.92),
                0 20px 32px -28px rgb(10 45 85 / 0.62);
        }

        .gov-profile-photo,
        .gov-profile-photo-fallback {
            width: 100%;
            height: 100%;
            border-radius: 999px;
        }

        .gov-profile-photo {
            object-fit: cover;
            background: #fff;
        }

        .gov-profile-photo-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top, #f3f7fb 0%, #dce8f6 100%);
            color: var(--gov-blue-900);
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .gov-profile-section-kicker {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--gov-blue-800);
        }

        .gov-profile-name {
            margin: 0.5rem 0 0;
            color: var(--gov-ink-900);
            font-size: 1.7rem;
            line-height: 1.15;
        }

        .gov-profile-office {
            margin: 0.35rem 0 0;
            color: var(--gov-ink-700);
            font-size: 0.98rem;
            line-height: 1.5;
        }

        .gov-profile-badge-row {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.55rem;
        }

        .gov-profile-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.78rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .gov-profile-badge-active {
            color: #0f684e;
            background: var(--gov-emerald-100);
            border: 1px solid rgb(15 138 98 / 0.18);
        }

        .gov-profile-badge-muted {
            color: var(--gov-blue-900);
            background: var(--gov-blue-100);
            border: 1px solid rgb(42 95 151 / 0.12);
        }

        .gov-profile-card-head {
            display: grid;
            gap: 0.45rem;
            padding: 1.3rem 1.35rem 0;
        }

        .gov-profile-card-head-wide {
            grid-template-columns: minmax(0, 1fr) minmax(14rem, 18rem);
            align-items: end;
            gap: 1rem;
        }

        .gov-profile-section-title {
            margin: 0.28rem 0 0;
            color: var(--gov-ink-900);
            font-size: 1.3rem;
            line-height: 1.2;
        }

        .gov-profile-section-copy {
            margin: 0;
            color: var(--gov-ink-500);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .gov-profile-form {
            padding: 1.25rem 1.35rem 1.35rem;
            display: grid;
            gap: 1rem;
        }

        .gov-profile-label {
            display: inline-block;
            margin-bottom: 0.45rem;
            color: var(--gov-ink-900);
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .gov-profile-input {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid #c8d6e7;
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            color: var(--gov-ink-700);
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
        }

        .gov-profile-input::file-selector-button {
            margin-right: 0.9rem;
            border: 0;
            border-radius: 0.8rem;
            padding: 0.7rem 0.95rem;
            background: linear-gradient(180deg, #eaf2fb, #d8e8f7);
            color: var(--gov-blue-900);
            font-weight: 700;
            cursor: pointer;
        }

        .gov-profile-input:hover,
        .gov-profile-input:focus {
            border-color: var(--gov-blue-800);
            box-shadow: 0 0 0 4px rgb(42 95 151 / 0.12);
            outline: none;
        }

        .gov-profile-input-error {
            border-color: #be123c;
            box-shadow: 0 0 0 4px rgb(190 18 60 / 0.08);
        }

        .gov-profile-help {
            margin: 0.55rem 0 0;
            color: var(--gov-ink-500);
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .gov-profile-error {
            margin: 0;
            color: #b42339;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .gov-profile-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.1rem;
            border: 0;
            border-radius: 0.95rem;
            padding: 0.9rem 1.25rem;
            background: linear-gradient(180deg, var(--gov-blue-800), var(--gov-blue-900));
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
            box-shadow: 0 16px 24px -20px rgb(21 71 119 / 0.8);
        }

        .gov-profile-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .gov-profile-submit:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgb(42 95 151 / 0.18);
        }

        .gov-profile-note-card {
            padding: 1.3rem 1.35rem;
            background:
                linear-gradient(180deg, #fffdf8 0%, #fff9ed 100%);
        }

        .gov-profile-note-list {
            margin: 0.9rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.8rem;
        }

        .gov-profile-note-list li {
            position: relative;
            padding-left: 1.05rem;
            color: var(--gov-ink-700);
            line-height: 1.6;
            font-size: 0.93rem;
        }

        .gov-profile-note-list li::before {
            content: '';
            position: absolute;
            top: 0.55rem;
            left: 0;
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--gov-gold-500), #8b691d);
            box-shadow: 0 0 0 3px rgb(185 138 43 / 0.12);
        }

        .gov-profile-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            padding: 1.25rem 1.35rem 1.35rem;
        }

        .gov-profile-field {
            border: 1px solid #dbe5f0;
            border-radius: 1rem;
            padding: 1rem 1.05rem;
            background:
                linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
        }

        .gov-profile-field-wide {
            grid-column: 1 / -1;
        }

        .gov-profile-field-emphasis {
            background:
                radial-gradient(circle at top right, rgb(220 232 246 / 0.65), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        }

        .gov-profile-field dt {
            margin: 0;
            color: var(--gov-ink-500);
            font-size: 0.76rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .gov-profile-field dd {
            margin: 0.52rem 0 0;
            color: var(--gov-ink-900);
            font-size: 1.04rem;
            line-height: 1.5;
            font-weight: 700;
            word-break: break-word;
        }

        .gov-profile-inline-form {
            display: grid;
            gap: 0.75rem;
        }

        .gov-profile-password-form {
            gap: 0.9rem;
        }

        .gov-profile-password-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
            gap: 0.85rem;
        }

        .gov-profile-password-input-wrap {
            position: relative;
        }

        .gov-profile-inline-input {
            width: 100%;
            border: 1px solid #c8d6e7;
            border-radius: 0.95rem;
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            color: var(--gov-ink-900);
            padding: 0.82rem 0.95rem;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.4;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }

        .gov-profile-inline-input-password {
            padding-right: 5.4rem;
        }

        .gov-profile-inline-input:focus {
            outline: none;
            border-color: var(--gov-blue-800);
            box-shadow: 0 0 0 4px rgb(42 95 151 / 0.12);
        }

        .gov-profile-inline-input-error {
            border-color: #be123c;
            box-shadow: 0 0 0 4px rgb(190 18 60 / 0.08);
        }

        .gov-profile-inline-submit {
            justify-self: start;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.7rem;
            border: 0;
            border-radius: 0.85rem;
            padding: 0.7rem 1rem;
            background: linear-gradient(180deg, var(--gov-blue-800), var(--gov-blue-900));
            color: #fff;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            cursor: pointer;
            transition: transform 180ms ease, filter 180ms ease, box-shadow 180ms ease;
            box-shadow: 0 14px 20px -18px rgb(21 71 119 / 0.78);
        }

        .gov-profile-inline-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .gov-profile-inline-submit:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgb(42 95 151 / 0.18);
        }

        .gov-profile-inline-error {
            margin: 0;
            color: #b42339;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .gov-profile-inline-help {
            margin: 0;
            color: var(--gov-ink-500);
            font-size: 0.84rem;
            line-height: 1.5;
            font-weight: 500;
        }

        .gov-profile-password-toggle {
            position: absolute;
            top: 50%;
            right: 0.7rem;
            transform: translateY(-50%);
            border: 0;
            border-radius: 999px;
            padding: 0.32rem 0.72rem;
            background: rgb(21 71 119 / 0.1);
            color: var(--gov-blue-900);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: background-color 180ms ease, color 180ms ease;
        }

        .gov-profile-password-toggle:hover {
            background: rgb(21 71 119 / 0.16);
        }

        .gov-profile-password-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgb(42 95 151 / 0.18);
        }

        @media (max-width: 1100px) {
            .gov-profile-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gov-profile-note-card {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 780px) {
            .gov-profile-masthead,
            .gov-profile-card-head,
            .gov-profile-form,
            .gov-profile-fields,
            .gov-profile-note-card {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .gov-profile-banner-copy {
                align-items: flex-start;
            }

            .gov-profile-grid,
            .gov-profile-card-head-wide,
            .gov-profile-fields {
                grid-template-columns: 1fr;
            }

            .gov-profile-note-card,
            .gov-profile-records-card {
                grid-column: auto;
            }
        }

        @media (max-width: 640px) {
            .gov-profile-shell {
                gap: 1rem;
            }

            .gov-profile-masthead {
                padding: 1rem;
                border-radius: 1.1rem;
            }

            .gov-profile-banner-copy {
                flex-direction: column;
            }

            .gov-profile-seal-wrap {
                width: 4.8rem;
                height: 4.8rem;
            }

            .gov-profile-title {
                font-size: 1.45rem;
            }

            .gov-profile-photo-frame {
                width: 8.5rem;
                height: 8.5rem;
            }

            .gov-profile-name {
                font-size: 1.45rem;
            }

            .gov-profile-card,
            .gov-profile-identity-card {
                border-radius: 1.1rem;
            }
        }

        @keyframes gov-profile-rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .gov-profile-masthead,
            .gov-profile-card {
                animation: none;
            }
        }
    </style>
@endonce

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.gov-profile-password-toggle').forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const target = document.getElementById(toggle.dataset.passwordTarget);

                    if (! target) {
                        return;
                    }

                    const isVisible = target.type === 'text';

                    target.type = isVisible ? 'password' : 'text';
                    toggle.textContent = isVisible ? 'Show' : 'Hide';
                    toggle.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
                    toggle.setAttribute(
                        'aria-label',
                        `${isVisible ? 'Show' : 'Hide'} ${target.getAttribute('aria-label')?.toLowerCase() ?? 'password'}`
                    );
                });
            });
        });
    </script>
@endonce
