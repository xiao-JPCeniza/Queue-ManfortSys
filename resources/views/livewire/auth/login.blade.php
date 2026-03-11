<div class="gov-login-shell w-full max-w-5xl">
    <div class="gov-bg-ornament" aria-hidden="true">
        <span class="gov-orb gov-orb-blue"></span>
        <span class="gov-orb gov-orb-gold"></span>
    </div>

    <section class="gov-login-panel" aria-labelledby="admin-login-title">
        <header class="gov-login-banner">
            <div class="gov-banner-content">
                <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich official seal" class="gov-seal">
                <div class="gov-banner-copy">
                    <h1 id="admin-login-title" class="gov-font-heading gov-municipality-title">Municipality of Manolo Fortich</h1>
                    <p class="gov-system-name">LGU Queue Management System</p>
                </div>
            </div>
            <p class="gov-access-chip">Administrative Access Portal</p>
        </header>

        <div class="gov-login-content">
            <aside class="gov-notice-block" aria-label="Portal information">
                <h2 class="gov-font-heading gov-notice-title">Secure Staff Portal</h2>
                <p class="gov-notice-text">
                    Access is restricted to authorized LGU personnel handling queue operations and office service workflows.
                </p>
                <ul class="gov-notice-list">
                    <li>Monitor real-time citizen queue activity.</li>
                    <li>Manage service transactions per office.</li>
                    <li>Generate official queue and reporting records.</li>
                </ul>
                <p class="gov-notice-help">For account concerns, contact your municipal system administrator.</p>
            </aside>

            <div class="gov-form-card">
                <h2 class="gov-font-heading gov-form-title">Sign In</h2>
                <p class="gov-form-subtitle">Use your official government account credentials.</p>

                <form wire:submit="login" class="gov-form-grid">
                    <div>
                        <label for="email" class="gov-label">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            class="gov-input @error('email') gov-input-error @enderror"
                            placeholder="admin@manolofortich.gov.ph"
                            autofocus
                            autocomplete="email"
                        >
                        @error('email')
                            <p class="gov-error-text" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="gov-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            wire:model="password"
                            class="gov-input @error('password') gov-input-error @enderror"
                            placeholder="Enter password"
                            autocomplete="current-password"
                        >
                        @error('password')
                            <p class="gov-error-text" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <label for="remember" class="gov-remember-row">
                        <input
                            type="checkbox"
                            id="remember"
                            wire:model="remember"
                            class="gov-checkbox"
                        >
                        <span>Keep me signed in on this device</span>
                    </label>

                    <button type="submit" class="gov-submit-btn">
                        Sign In to Admin Dashboard
                    </button>
                </form>
            </div>
        </div>
    </section>

    <p class="gov-login-footer">Queue System for LGU Manolo Fortich &copy; {{ date('Y') }}</p>
</div>

@once
    <style>
        .gov-login-shell {
            --gov-blue-950: #0b2f57;
            --gov-blue-900: #0f3f73;
            --gov-blue-800: #1a4f8e;
            --gov-blue-100: #dce9f6;
            --gov-gold-500: #b78a2e;
            --gov-gold-100: #f7efdd;
            --gov-red-600: #9f1239;
            --gov-ink-900: #142033;
            --gov-ink-700: #334155;
            --gov-ink-500: #8b8664;
            --gov-surface: #ffffff;
            --gov-border: #d8e2ef;
            position: relative;
        }

        .gov-bg-ornament {
            position: absolute;
            inset: -2.5rem;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .gov-orb {
            position: absolute;
            border-radius: 999px;
            opacity: 0.35;
        }

        .gov-orb-blue {
            width: 20rem;
            height: 20rem;
            background: radial-gradient(circle at 35% 35%, #bfdbfe 0%, #93c5fd 38%, transparent 72%);
            top: -6rem;
            right: -6rem;
        }

        .gov-orb-gold {
            width: 18rem;
            height: 18rem;
            background: radial-gradient(circle at 55% 55%, #f8e9b8 0%, #f2d38a 40%, transparent 74%);
            bottom: -6rem;
            left: -6rem;
        }

        .gov-login-panel {
            position: relative;
            z-index: 1;
            border: 1px solid var(--gov-border);
            border-radius: 1.5rem;
            overflow: hidden;
            background: var(--gov-surface);
            box-shadow: 0 24px 40px -30px rgb(15 63 115 / 0.45);
            animation: gov-panel-enter 440ms ease-out both;
        }

        .gov-login-banner {
            position: relative;
            padding: 1.5rem 1.5rem 1.1rem;
            background:
                linear-gradient(120deg, #1d3883, #8b9cca),
                linear-gradient(45deg, rgb(255 255 255 / 0.07), transparent 50%);
            color: #fff;
        }

        .gov-login-banner::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.35rem;
            background: linear-gradient(90deg, #ffff08 0%, #133085 66%, #d72c2c 66%, #09811b 84%, #ffff08 84%, #d58904 100%);
        }

        .gov-banner-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .gov-seal {
            width: 3.75rem;
            height: 3.75rem;
            border-radius: 999px;
            border: 2px solid rgb(255 255 255 / 0.6);
            background: rgb(255 255 255 / 0.85);
            object-fit: cover;
            object-position: center;
            flex-shrink: 0;
        }

        .gov-republic-label {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgb(219 234 254 / 0.95);
            font-weight: 600;
        }

        .gov-municipality-title {
            margin: 0.35rem 0 0;
            font-size: clamp(1.4rem, 2vw, 1.95rem);
            line-height: 1.1;
            font-weight: 700;
            color: #fff;
        }

        .gov-system-name {
            margin: 0.3rem 0 0;
            color: rgb(226 232 240 / 0.94);
            font-size: 0.92rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .gov-access-chip {
            margin: 1rem 0 0;
            display: inline-flex;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.74rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 600;
            color: #fff;
            background: rgb(183 138 46 / 0.32);
            border: 1px solid rgb(247 239 221 / 0.4);
        }

        .gov-login-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .gov-notice-block {
            padding: 2rem 1.5rem;
            border-right: 1px solid #e2e8f0;
            background:
                radial-gradient(circle at right top, rgb(220 233 246 / 0.75), transparent 55%),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .gov-notice-title {
            margin: 0;
            color: var(--gov-ink-900);
            font-size: 1.35rem;
            font-weight: 700;
        }

        .gov-notice-text {
            margin: 0.95rem 0 0;
            color: var(--gov-ink-700);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .gov-notice-list {
            margin: 1rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.6rem;
        }

        .gov-notice-list li {
            position: relative;
            padding-left: 1.15rem;
            color: var(--gov-ink-700);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .gov-notice-list li::before {
            content: '';
            position: absolute;
            top: 0.46rem;
            left: 0;
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--gov-blue-800), var(--gov-blue-900));
            box-shadow: 0 0 0 3px var(--gov-blue-100);
        }

        .gov-notice-help {
            margin: 1.25rem 0 0;
            border-radius: 0.8rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 0.75rem 0.9rem;
            color: var(--gov-ink-500);
            font-size: 0.85rem;
            line-height: 1.45;
        }

        .gov-form-card {
            padding: 2rem 1.5rem;
            animation: gov-card-enter 520ms ease-out 120ms both;
        }

        .gov-form-title {
            margin: 0;
            color: var(--gov-ink-900);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .gov-form-subtitle {
            margin: 0.4rem 0 1.35rem;
            color: var(--gov-ink-500);
            font-size: 0.93rem;
            line-height: 1.5;
        }

        .gov-form-grid {
            display: grid;
            gap: 1rem;
        }

        .gov-label {
            display: block;
            margin-bottom: 0.45rem;
            color: var(--gov-ink-900);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .gov-input {
            width: 100%;
            border: 1px solid #bfcee0;
            border-radius: 0.85rem;
            min-height: 3rem;
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
            color: #1e293b;
            background: #fff;
            transition: border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .gov-input::placeholder {
            color: #7b8da5;
        }

        .gov-input:focus {
            outline: none;
            border-color: var(--gov-blue-800);
            box-shadow: 0 0 0 3px rgb(26 79 142 / 0.16);
            background: #fefefe;
        }

        .gov-input-error {
            border-color: #be123c;
        }

        .gov-input-error:focus {
            border-color: #be123c;
            box-shadow: 0 0 0 3px rgb(190 18 60 / 0.16);
        }

        .gov-error-text {
            margin: 0.45rem 0 0;
            color: #be123c;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .gov-remember-row {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: var(--gov-ink-700);
            font-size: 0.88rem;
            user-select: none;
        }

        .gov-checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: 0.22rem;
            border: 1px solid #9fb2c9;
            accent-color: var(--gov-blue-800);
            cursor: pointer;
        }

        .gov-submit-btn {
            width: 100%;
            min-height: 3rem;
            border: 0;
            border-radius: 0.9rem;
            background: linear-gradient(180deg, #d6a050 0%, #cf8701 100%);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: transform 160ms ease, filter 160ms ease, box-shadow 160ms ease;
            box-shadow: 0 10px 20px -14px rgb(4 120 87 / 0.95);
            cursor: pointer;
        }

        .gov-submit-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
        }

        .gov-submit-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgb(16 185 129 / 0.22);
        }

        .gov-submit-btn:active {
            transform: translateY(0);
        }

        .gov-login-footer {
            position: relative;
            z-index: 1;
            margin: 0.95rem 0 0;
            text-align: center;
            font-size: 0.83rem;
            color: #475569;
            letter-spacing: 0.02em;
        }

        @media (max-width: 960px) {
            .gov-login-content {
                grid-template-columns: 1fr;
            }

            .gov-notice-block {
                border-right: 0;
                border-top: 1px solid #e2e8f0;
                order: 2;
                padding-top: 1.6rem;
            }

            .gov-form-card {
                order: 1;
            }
        }

        @media (max-width: 640px) {
            .gov-login-banner,
            .gov-form-card,
            .gov-notice-block {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .gov-banner-content {
                align-items: flex-start;
            }

            .gov-seal {
                width: 3.3rem;
                height: 3.3rem;
            }

            .gov-republic-label {
                font-size: 0.66rem;
            }

            .gov-system-name {
                font-size: 0.79rem;
                line-height: 1.3;
            }
        }

        @keyframes gov-panel-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes gov-card-enter {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .gov-login-panel,
            .gov-form-card {
                animation: none;
            }
        }
    </style>
@endonce
