<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login BUMKam</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dfeefb 0%, #cdeaf8 40%, #d9f1f8 100%);
            font-family: Arial, sans-serif;
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
            background: linear-gradient(180deg, rgba(119, 189, 224, 0.2), rgba(97, 157, 193, 0.15)),
                        url('https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80') center/cover no-repeat;
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
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(255,255,255,0.25);
            border: 1px solid rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .property-photo {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            min-height: 300px;
        }

        .property-card {
            width: 80%;
            background: rgba(4, 33, 52, 0.28);
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
            color: #173c30;
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
            border-color: #1d6a59;
            box-shadow: 0 0 0 3px rgba(29, 106, 89, 0.12);
        }

        .btn {
            width: 100%;
            border: none;
            background: linear-gradient(135deg, #0d6fb5, #1d7db4 65%, #2da2c3);
            color: white;
            padding: 15px 18px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 18px rgba(13, 111, 181, 0.18);
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
    </style>
</head>
<body>
    <div class="login-frame">
        <div class="left-panel">
            <div class="brand-mark">
                <div class="logo">B</div>
                <span>BUMKam</span>
            </div>

            <div class="property-photo">
                <div class="property-card">
                    <h2>BUMKam</h2>
                    <p>Tuah Rezki<br>Bersemi Membangu Desa</p>
                </div>
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
                        <input id="password" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password">
                    </div>

                    <button class="btn" type="submit">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>