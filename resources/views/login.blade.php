<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Login BUMKam</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f8e9 0%, #e4f3df 45%, #f4f8e9 100%);
            font-family: 'Public Sans', Arial, sans-serif;
            color: #163a2c;
        }

        .login-frame {
            width: min(1100px, 95vw);
            min-height: 700px;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            background: rgba(255,255,255,0.4);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(17, 58, 74, 0.14);
        }

        .left-panel {
            position: relative;
            background: radial-gradient(circle at 78% 22%, rgba(163, 230, 53, .28), transparent 22%),
                        radial-gradient(circle at 18% 82%, rgba(5, 150, 105, .3), transparent 30%),
                        linear-gradient(145deg, #123b2b 0%, #087443 55%, #079447 100%);
            min-height: 100%;
            padding: 26px 28px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            text-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .brand-mark .logo {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            background: #fff;
            border: 1px solid rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 5px 16px rgba(0,0,0,.16);
        }

        .brand-mark .logo img { width: 100%; height: 100%; object-fit: contain; }

        .property-photo {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            min-height: 300px;
        }

        .property-card {
            width: 80%;
            background: rgba(4, 54, 32, 0.3);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 18px;
            backdrop-filter: blur(3px);
            padding: 16px 18px;
            color: white;
            box-shadow: 0 14px 28px rgba(0,0,0,0.1);
        }

        .property-card h2 {
            margin: 0 0 8px;
            font-size: 2.2rem;
            line-height: 1.1;
        }

        .property-card p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.95;
        }

        .right-panel {
            background: rgba(255,255,255,0.62);
            padding: 42px 40px 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: min(360px, 100%);
        }

        .login-box h1 {
            margin: 0 0 8px;
            font-size: 2.3rem;
            color: #14532d;
        }

        .login-box .small-text {
            margin: 0 0 28px;
            font-size: 0.92rem;
            color: #5f6d7a;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: #2d4f57;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .field input {
            width: 100%;
            padding: 14px 14px;
            border: 1px solid rgba(107, 122, 138, 0.3);
            border-radius: 10px;
            background: rgba(255,255,255,0.7);
            font-size: 0.96rem;
            outline: none;
        }

        .field input:focus {
            border-color: #078a46;
            box-shadow: 0 0 0 3px rgba(7, 138, 70, 0.13);
        }

        .password-input { position: relative; }
        .password-input input { padding-right: 48px; }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #526475;
            cursor: pointer;
        }
        .password-toggle:hover { background: rgba(100, 116, 139, .12); }
        .password-toggle:focus-visible { outline: 2px solid #078a46; outline-offset: 2px; }
        .password-toggle svg { width: 20px; height: 20px; }
        .password-toggle svg.hidden { display: none; }

        .btn {
            width: 100%;
            border: none;
            background: linear-gradient(135deg, #078a46, #079447 65%, #65a30d);
            color: white;
            padding: 15px 18px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 18px rgba(7, 138, 70, 0.2);
        }

        .alert {
            margin-bottom: 18px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            padding: 10px 12px;
            color: #991b1b;
        }

        .alert ul {
            margin: 0;
            padding-left: 18px;
        }

        @media (max-width: 900px) {
            .login-frame {
                grid-template-columns: 1fr;
            }

            .left-panel {
                min-height: 260px;
            }

            .right-panel {
                padding-top: 28px;
            }
        }

        @media (prefers-color-scheme: dark) {
            body { background: #0b1220; color: #e2e8f0; }
            .login-frame { background: #111c2e; border-color: #334155; }
            .right-panel { background: #111c2e; }
            .login-box h1 { color: #f1f5f9; }
            .field label { color: #cbd5e1; }
            .field input { background: #0f172a; color: #f1f5f9; border-color: #475569; }
        }
    </style>
</head>
<body>
    <div class="login-frame">
        <div class="left-panel">
            <div class="brand-mark">
                <div class="logo"><img src="{{ asset('assets/img/bumkam-mark.png') }}" alt="Logo BUMKam Tuah Rezeki"></div>
                <span>BUMKam</span>
            </div>

            <div class="property-photo">
                <svg viewBox="0 0 600 320" role="img" aria-label="Ilustrasi desa dan pertumbuhan ekonomi" style="width:100%;max-width:620px;height:auto;filter:drop-shadow(0 20px 30px rgba(0,0,0,.18))">
                    <circle cx="475" cy="68" r="34" fill="#fef3c7" opacity=".95"/>
                    <path d="M0 226 130 150l91 57 116-91 140 92 123-65v177H0Z" fill="#164e2d" opacity=".8"/>
                    <path d="M0 245 125 188l103 57 110-71 132 74 130-51v103H0Z" fill="#166534"/>
                    <path d="M0 270c98-29 181-13 267 4 112 22 210-14 333-30v76H0Z" fill="#15803d"/>
                    <g fill="#f8fafc"><path d="m186 188 50-41 51 41v63h-101Z"/><path d="m174 188 62-51 63 51h-125Z" fill="#d9f99d"/><path d="M228 212h18v39h-18z" fill="#14532d"/><path d="M201 195h15v13h-15zm36 0h15v13h-15z" fill="#a3e635"/><path d="m356 205 42-34 43 34v50h-85Z"/><path d="m345 205 53-44 54 44H345Z" fill="#bef264"/><path d="M390 225h17v30h-17z" fill="#14532d"/></g>
                    <g fill="#bef264"><circle cx="82" cy="226" r="7"/><circle cx="92" cy="216" r="9"/><circle cx="102" cy="229" r="8"/><circle cx="516" cy="223" r="7"/><circle cx="526" cy="213" r="9"/><circle cx="536" cy="226" r="8"/></g>
                    <path d="M0 276c86-14 150-9 217 5m156 8c82-14 145-14 227-1" fill="none" stroke="#bef264" stroke-width="3" stroke-linecap="round" opacity=".75"/>
                </svg>
            </div>

            <div class="property-card">
                <h2>BUMKam</h2>
                <p>Tuah Rezeki<br>Bersama Membangun Desa</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="login-box">
                <h1>Login</h1>
                <p class="small-text">Masuk untuk melanjutkan ke dashboard</p>

                @if ($errors->any())
                    <div class="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.process') }}" method="POST">
                    @csrf

                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="password-input">
                            <input id="password" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password">
                            <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Tampilkan password" aria-controls="password" aria-pressed="false">
                                <svg data-eye-closed viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg data-eye-open class="hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.9 10.9 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10.8 10.8 0 0 0 4-.8"/></svg>
                            </button>
                        </div>
                    </div>

                    <button class="btn" type="submit">Login</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('[data-password-toggle]')?.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.passwordToggle);
            if (!input) return;

            const tampilkan = input.type === 'password';
            input.type = tampilkan ? 'text' : 'password';
            this.setAttribute('aria-label', tampilkan ? 'Sembunyikan password' : 'Tampilkan password');
            this.setAttribute('aria-pressed', String(tampilkan));
            this.querySelector('[data-eye-open]')?.classList.toggle('hidden', !tampilkan);
            this.querySelector('[data-eye-closed]')?.classList.toggle('hidden', tampilkan);
        });
    </script>
</body>
</html>
