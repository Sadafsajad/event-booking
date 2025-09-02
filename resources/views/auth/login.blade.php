<x-guest-layout>

    {{-- --- Simple CSS (no Tailwind / no build needed) --- --}}
    <style>
        /* divider + social buttons (as before) */
        .auth-divider {
            position: relative;
            margin: 24px 0;
            text-align: center
        }

        .auth-divider:before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #e5e7eb
        }

        .auth-divider span {
            position: relative;
            background: #fff;
            padding: 0 8px;
            font-size: 12px;
            color: #6b7280;
            letter-spacing: .08em
        }

        .social-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px
        }

        @media(min-width:640px) {
            .social-grid {
                grid-template-columns: 1fr 1fr
            }
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background .15s ease, box-shadow .15s ease, transform .06s ease
        }

        .social-btn:active {
            transform: translateY(1px)
        }

        .social-btn.google {
            border: 1px solid #d1d5db;
            color: #374151;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04)
        }

        .social-btn.google:hover {
            background: #f9fafb
        }

        .social-btn.github {
            background: #24292E;
            color: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08)
        }

        .social-btn.github:hover {
            background: #000
        }

        .social-icon {
            width: 20px;
            height: 20px;
            display: inline-block
        }

        /* NEW: login + forgot styles */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
            gap: 12px
        }

        .forgot-link {
            font-size: 13px;
            color: #6b7280;
            text-decoration: none;
            border-bottom: 1px dashed transparent;
            line-height: 1.2
        }

        .forgot-link:hover {
            color: #111827;
            border-bottom-color: #9ca3af
        }

        .login-btn {
            padding: 10px 16px;
            border-radius: 10px;
            background: #111827;
            color: #fff;
            border: none;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .1)
        }

        .login-btn:hover {
            background: #0b1220
        }

        .login-btn:focus {
            outline: 2px solid #4338ca;
            outline-offset: 2px
        }

        @media(max-width:380px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch
            }

            .forgot-link {
                order: 2;
                text-align: center
            }

            .login-btn {
                order: 1;
                width: 100%
            }
        }
    </style>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Actions: Forgot + Login (custom HTML/CSS) -->
        <div class="form-actions">
            @if (Route::has('password.request'))
                <a class="forgot-link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="login-btn">
                {{ __('LOG IN') }}
            </button>
        </div>
    </form>

    {{-- Social sign-in (pure HTML/CSS) --}}
    <div class="mt-8">
        <div class="auth-divider"><span>OR CONTINUE WITH</span></div>

        <div class="social-grid">
            {{-- Google --}}
            <a href="{{ route('google.redirect') }}" class="social-btn google">
                <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#EA4335"
                        d="M12 10.2v3.9h5.5c-.24 1.4-1.67 4.1-5.5 4.1A6.4 6.4 0 1 1 12 5.6c1.83 0 3.06.78 3.76 1.45l2.56-2.47C16.89 3.3 14.7 2.4 12 2.4 6.98 2.4 2.9 6.47 2.9 11.5S6.98 20.6 12 20.6c6.03 0 8.06-4.22 8.06-6.37 0-.42-.04-.7-.09-.99H12Z" />
                    <path fill="#4285F4" d="M21.95 10.24H12v3.9h5.77a5.2 5.2 0 0 0 4.18-3.9Z" />
                    <path fill="#FBBC05" d="M6.7 13.54a4.9 4.9 0 0 1 0-3.98V6.77H2.9a8.6 8.6 0 0 0 0 9.96l3.8-3.19Z" />
                    <path fill="#34A853"
                        d="M12 20.6c2.7 0 4.97-.89 6.63-2.42l-3.22-3.09c-.89.62-2.06 1.05-3.41 1.05a6.4 6.4 0 0 1-6.3-4.6l-3.8 3.2A8.6 8.6 0 0 0 12 20.6Z" />
                </svg>
                <span>Google</span>
            </a>

            {{-- GitHub --}}
            <a href="{{ route('github.redirect') }}" class="social-btn github">
                <svg class="social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M12 2C6.48 2 2 6.58 2 12.25c0 4.52 2.87 8.35 6.85 9.71.5.09.68-.22.68-.49 0-.24-.01-.87-.01-1.71-2.78.62-3.37-1.37-3.37-1.37-.45-1.18-1.1-1.5-1.1-1.5-.9-.63.07-.62.07-.62 1 .07 1.52 1.06 1.52 1.06.89 1.56 2.34 1.11 2.91.85.09-.66.35-1.11.63-1.37-2.22-.26-4.55-1.15-4.55-5.14 0-1.14.39-2.07 1.03-2.8-.1-.26-.45-1.31.1-2.73 0 0 .84-.27 2.75 1.07a9.2 9.2 0 0 1 5 0c1.9-1.34 2.74-1.07 2.74-1.07.56 1.42.21 2.47.11 2.73.65.73 1.03 1.66 1.03 2.8 0 3.99-2.34 4.88-4.57 5.14.36.32.68.95.68 1.92 0 1.38-.01 2.49-.01 2.83 0 .27.18.59.69.49A10.09 10.09 0 0 0 22 12.25C22 6.58 17.52 2 12 2Z" />
                </svg>
                <span>GitHub</span>
            </a>
        </div>
    </div>
</x-guest-layout>