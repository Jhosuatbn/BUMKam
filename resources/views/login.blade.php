<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f4f5ef">
    <title>Masuk | BUMKam Tuah Rezeki</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{color-scheme:light;--primary:#234f2d;--primary-dark:#173d22;--ink:#173117;--muted:#6c7868;--line:#d8ddcf;--gold:#f0d66e}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;min-height:100dvh;background:#f4f5ef;color:var(--ink);font-family:Poppins,Arial,sans-serif;-webkit-font-smoothing:antialiased}
        a{color:inherit}
        .login-page{min-height:100vh;min-height:100dvh;display:grid;grid-template-columns:minmax(0,1.16fr) minmax(440px,.84fr);overflow:hidden}
        .visual-panel{position:relative;isolation:isolate;display:flex;min-height:100vh;flex-direction:column;overflow:hidden;background:#173d22;color:#fff}
        .landscape{position:absolute;z-index:-3;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}
        .photo-tint{position:absolute;z-index:-2;inset:0;background:#12351d;opacity:.65;mix-blend-mode:multiply}
        .photo-shade{position:absolute;z-index:-1;inset:0;background:linear-gradient(180deg,rgba(14,45,25,.62),rgba(14,45,25,.08) 42%,rgba(11,37,21,.96))}
        .grid-texture{position:absolute;z-index:-1;inset:0;opacity:.09;background-image:linear-gradient(rgba(255,255,255,.7) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.7) 1px,transparent 1px);background-size:88px 88px;mask-image:linear-gradient(to bottom,#000,transparent 70%)}
        .visual-inner{display:flex;min-height:100vh;flex-direction:column;padding:40px clamp(34px,5vw,76px) 30px}
        .brand{display:flex;width:max-content;align-items:center;gap:12px;color:#fff;text-decoration:none}
        .brand-mark{display:grid;width:46px;height:46px;place-items:center;border:1px solid rgba(255,255,255,.34);border-radius:14px;background:rgba(255,255,255,.15);box-shadow:0 12px 28px rgba(0,0,0,.2);backdrop-filter:blur(9px);font-size:.86rem;font-weight:700}
        .brand-copy{line-height:1.35}.brand-overline{margin:0;color:rgba(255,255,255,.64);font-size:.62rem;font-weight:600;letter-spacing:.2em}.brand-name{margin:2px 0 0;font-size:.88rem;font-weight:600}
        .orbit{position:absolute;top:13%;right:8%;width:clamp(220px,22vw,320px);aspect-ratio:1;border:1px solid rgba(255,255,255,.22);border-radius:50%;animation:appear .8s ease both}
        .orbit:before{position:absolute;inset:28px;border:1px solid rgba(255,255,255,.16);border-radius:50%;content:""}
        .orbit-core{position:absolute;inset:30%;display:grid;place-items:center;border:1px solid rgba(240,214,110,.7);border-radius:50%;background:rgba(23,61,34,.3);backdrop-filter:blur(5px);color:var(--gold)}
        .orbit-core svg{width:31px;height:31px}
        .visual-content{width:min(700px,100%);margin:auto 0 0;padding:70px 0 10px;animation:rise .7s .1s both}
        .eyebrow{display:flex;align-items:center;gap:12px;margin:0 0 23px;color:var(--gold);font-size:.69rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase}
        .eyebrow:before{width:40px;height:1px;background:var(--gold);content:""}
        .visual-content h1{margin:0;font-size:clamp(2.9rem,5vw,5.2rem);font-weight:600;letter-spacing:-.065em;line-height:1.04}
        .visual-content>p{max-width:540px;margin:22px 0 0;color:rgba(255,255,255,.73);font-size:.92rem;line-height:1.9}
        .trust-row{display:flex;max-width:540px;align-items:center;gap:16px;margin-top:34px;padding-top:22px;border-top:1px solid rgba(255,255,255,.21)}
        .initials{display:flex;padding-left:2px}.initials span{display:grid;width:35px;height:35px;place-items:center;margin-left:-3px;border:2px solid #244c2d;border-radius:50%;font-size:.55rem;font-weight:700}.initials span:nth-child(1){background:var(--gold);color:#173117}.initials span:nth-child(2){background:#fff;color:#173117}.initials span:nth-child(3){background:#386843;color:#fff}
        .trust-title{margin:0;font-size:.72rem;font-weight:600}.trust-caption{margin:3px 0 0;color:rgba(255,255,255,.53);font-size:.64rem}
        .form-panel{position:relative;display:flex;min-height:100vh;flex-direction:column;padding:20px clamp(22px,3vw,48px);background:radial-gradient(circle at 100% 0,rgba(35,79,45,.055),transparent 260px),#f4f5ef}
        .form-panel:before{position:absolute;top:0;right:0;width:300px;height:300px;border:36px solid rgba(35,79,45,.04);border-radius:50%;content:"";pointer-events:none}
        .topbar{position:relative;z-index:1;display:flex;align-items:center;justify-content:flex-end;min-height:50px}
        .return-link{display:inline-flex;min-height:40px;align-items:center;gap:8px;padding:0 15px;border:1px solid var(--line);border-radius:999px;background:rgba(255,255,255,.56);color:var(--primary);font-size:.7rem;font-weight:600;text-decoration:none;transition:background .18s,border-color .18s}.return-short{display:none}
        .return-link:hover{border-color:rgba(35,79,45,.35);background:#fff}.return-link:focus-visible{outline:3px solid rgba(35,79,45,.25);outline-offset:2px}.return-link svg{width:15px;height:15px}
        .mobile-brand{display:none}
        .form-wrap{position:relative;z-index:1;display:flex;width:min(420px,100%);flex:1;flex-direction:column;justify-content:center;margin:0 auto;padding:28px 0}
        .portal-label{display:inline-flex;width:max-content;align-items:center;gap:8px;margin:0 0 22px;padding:7px 12px;border:1px solid rgba(35,79,45,.1);border-radius:999px;background:rgba(35,79,45,.06);color:var(--primary);font-size:.58rem;font-weight:600;letter-spacing:.13em;text-transform:uppercase}
        .portal-label svg{width:14px;height:14px}
        h2{margin:0;color:#173117;font-size:clamp(2.2rem,3.2vw,2.75rem);font-weight:600;letter-spacing:-.055em;line-height:1.16}
        .intro{margin:12px 0 30px;color:var(--muted);font-size:.8rem;line-height:1.8}
        .alert{display:flex;gap:10px;margin:0 0 20px;padding:13px 14px;border:1px solid #edc9c4;border-radius:14px;background:#fff5f3;color:#8a3028;font-size:.75rem;line-height:1.6}.alert-icon{width:18px;height:18px;flex:none;margin-top:1px}.alert ul{margin:0;padding-left:17px}
        .field{margin:0 0 19px}.field label{display:block;margin:0 0 8px;color:#334631;font-size:.72rem;font-weight:600}
        .control{position:relative}.control-icon{position:absolute;top:50%;left:15px;width:17px;height:17px;transform:translateY(-50%);color:#849080;pointer-events:none;transition:color .18s}.control:focus-within .control-icon{color:var(--primary)}
        .control input{display:block;width:100%;height:56px;padding:0 46px;border:1px solid var(--line);border-radius:15px;outline:none;background:rgba(255,255,255,.86);color:#173117;font:inherit;font-size:.78rem;box-shadow:0 4px 20px rgba(28,55,25,.035);transition:border-color .18s,box-shadow .18s,background .18s}
        .control input::placeholder{color:#a2aa9f}.control input:hover{border-color:rgba(35,79,45,.25)}.control input:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 4px rgba(35,79,45,.08)}.control input[aria-invalid=true]{border-color:#bc5547}
        .password-toggle{position:absolute;top:50%;right:6px;display:grid;width:40px;height:40px;place-items:center;transform:translateY(-50%);border:0;border-radius:12px;background:transparent;color:#849080;cursor:pointer}.password-toggle:hover{background:rgba(35,79,45,.06);color:var(--primary)}.password-toggle:focus-visible{outline:2px solid var(--primary);outline-offset:1px}.password-toggle svg{width:17px;height:17px}.password-toggle svg.hidden{display:none}
        .remember{display:flex;width:max-content;align-items:center;gap:10px;margin:0 0 21px;color:var(--muted);font-size:.7rem;cursor:pointer}.remember input{position:absolute;width:1px;height:1px;opacity:0}.check{display:grid;width:19px;height:19px;place-items:center;border:1px solid #cbd2c4;border-radius:6px;background:#fff;color:transparent;transition:all .16s}.remember input:checked+.check{border-color:var(--primary);background:var(--primary);color:#fff}.remember input:focus-visible+.check{outline:3px solid rgba(35,79,45,.18);outline-offset:2px}.check svg{width:12px;height:12px}
        .submit{position:relative;display:flex;width:100%;height:58px;align-items:center;justify-content:center;gap:10px;overflow:hidden;border:0;border-radius:15px;background:#234f2d;color:#fff;font:inherit;font-size:.78rem;font-weight:600;box-shadow:0 14px 35px rgba(35,79,45,.2);cursor:pointer;transition:transform .18s,background .18s,box-shadow .18s}.submit:before{position:absolute;top:0;bottom:0;left:0;width:4px;background:#e4c858;content:""}.submit:hover{transform:translateY(-1px);background:#193f23;box-shadow:0 18px 40px rgba(35,79,45,.26)}.submit:focus-visible{outline:3px solid rgba(35,79,45,.24);outline-offset:3px}.submit svg{width:17px;height:17px;transition:transform .18s}.submit:hover svg{transform:translateX(3px)}
        .divider{display:flex;align-items:center;gap:14px;margin:26px 0 0;color:#c3a634}.divider:before,.divider:after{height:1px;flex:1;background:var(--line);content:""}.divider svg{width:14px;height:14px}
        .contact-note{margin:18px 0 0;color:#7a8577;font-size:.65rem;line-height:1.7;text-align:center}.contact-note strong{color:var(--primary);font-weight:600}
        .footer{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 0 3px;border-top:1px solid rgba(216,221,207,.8);color:#8c9589;font-size:.55rem;letter-spacing:.09em;text-transform:uppercase}
        @keyframes rise{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}@keyframes appear{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:scale(1)}}
        @media(max-width:1000px){.login-page{grid-template-columns:minmax(0,1fr) minmax(400px,.88fr)}.visual-inner{padding-right:34px;padding-left:34px}.visual-content h1{font-size:clamp(2.7rem,5.4vw,4.2rem)}.orbit{right:5%;width:clamp(190px,24vw,250px)}}
        @media(max-width:760px){.login-page{display:flex;min-height:100dvh;flex-direction:column}.visual-panel{display:none}.form-panel{min-height:100dvh;padding:16px 22px 17px}.topbar{min-height:48px}.mobile-brand{display:flex;align-items:center;gap:10px;margin-right:auto;text-decoration:none}.mobile-mark{display:grid;width:38px;height:38px;place-items:center;border-radius:12px;background:var(--primary);color:white;font-size:.68rem;font-weight:700}.mobile-brand p{margin:0}.mobile-brand .small{color:#778477;font-size:.53rem;font-weight:600;letter-spacing:.16em}.mobile-brand .name{color:var(--primary);font-size:.7rem;font-weight:600}.return-link{min-height:38px;padding:0 12px;font-size:.62rem}.return-long{display:none}.return-short{display:inline}.form-wrap{width:min(440px,100%);padding:38px 0 28px}.portal-label{margin-bottom:18px}.intro{margin-bottom:28px}.footer{font-size:.5rem}}
        @media(max-width:390px){.form-panel{padding-right:17px;padding-left:17px}.return-link{gap:5px;padding:0 10px}.form-wrap{padding-top:30px}.footer{letter-spacing:.04em}}
        @media(prefers-reduced-motion:reduce){*,*:before,*:after{animation-duration:.01ms!important;animation-iteration-count:1!important;scroll-behavior:auto!important;transition-duration:.01ms!important}}
    </style>
</head>
<body>
    <main class="login-page">
        <section class="visual-panel" aria-label="Identitas BUMKam Tuah Rezeki">
            <img class="landscape" src="https://images.unsplash.com/photo-1594063835708-b3bb19c6e668?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&q=90&w=1800" alt="Lanskap hijau Kampung Rantau Bertuah">
            <div class="photo-tint"></div><div class="photo-shade"></div><div class="grid-texture"></div>
            <div class="orbit" aria-hidden="true"><div class="orbit-core"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/><path d="M12 21s-1-5 2-9 6-5 6-5"/></svg></div></div>
            <div class="visual-inner">
                <a href="{{ route('public.home') }}" class="brand" aria-label="BUMKam Tuah Rezeki, kembali ke beranda"><span class="brand-mark">TR</span><span class="brand-copy"><span class="brand-overline">BUMKAM</span><span class="brand-name">Tuah Rezeki</span></span></a>
                <div class="visual-content">
                    <p class="eyebrow">Tumbuh bersama kampung</p>
                    <h1>Dari kampung,<br>untuk masa depan.</h1>
                    <p>Satu ruang kerja digital untuk mengelola potensi, transparansi, dan pertumbuhan ekonomi Kampung Rantau Bertuah.</p>
                    <div class="trust-row"><div class="initials" aria-hidden="true"><span>TR</span><span>KR</span><span>SK</span></div><div><p class="trust-title">Dikelola secara profesional</p><p class="trust-caption">Mandiri · Amanah · Terpercaya</p></div></div>
                </div>
            </div>
        </section>

        <section class="form-panel" aria-labelledby="login-heading">
            <header class="topbar">
                <a href="{{ route('public.home') }}" class="mobile-brand" aria-label="BUMKam Tuah Rezeki, kembali ke beranda"><span class="mobile-mark">TR</span><span><span class="small">BUMKAM</span><span class="name">Tuah Rezeki</span></span></a>
                <a href="{{ route('public.home') }}" class="return-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/><path d="M9 12h12"/></svg><span class="return-long">Kembali ke beranda</span><span class="return-short">Kembali</span></a>
            </header>
            <div class="form-wrap">
                <p class="portal-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.5 8 10 4.6-1.5 8-5 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg> Portal pengelola</p>
                <h2 id="login-heading">Selamat datang<br>kembali.</h2>
                <p class="intro">Masuk menggunakan akun resmi pengelola.</p>
                @if ($errors->any())
                    <div id="login-errors" class="alert" role="alert" aria-live="assertive"><svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('login.process') }}" method="POST">
                    @csrf
                    <div class="field"><label for="username">Username</label><div class="control"><svg class="control-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg><input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" placeholder="Masukkan username" required autofocus @error('username') aria-invalid="true" aria-describedby="login-errors" @enderror></div></div>
                    <div class="field"><label for="password">Kata sandi</label><div class="control"><svg class="control-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required @error('password') aria-invalid="true" aria-describedby="login-errors" @enderror><button class="password-toggle" type="button" data-password-toggle="password" aria-label="Tampilkan kata sandi" aria-controls="password" aria-pressed="false"><svg data-eye-closed viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg data-eye-open class="hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.9 10.9 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10.8 10.8 0 0 0 4-.8"/></svg></button></div></div>
                    <label class="remember" for="remember"><input id="remember" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}><span class="check"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 8 3 3 7-7"/></svg></span>Ingat saya di perangkat ini</label>
                    <button class="submit" type="submit"><span>Masuk ke Portal</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
                </form>
                <div class="divider" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v2m7.07.93-1.42 1.42M21 12h-2m-.93 7.07-1.42-1.42M12 21v-2m-7.07-.93 1.42-1.42M3 12h2m.93-7.07 1.42 1.42"/><circle cx="12" cy="12" r="4"/></svg></div>
                <p class="contact-note">Gunakan akun yang diberikan administrator untuk mengakses sistem.</p>
            </div>
            <footer class="footer"><span>© {{ now()->year }} Tuah Rezeki</span><span>Rantau Bertuah · Riau</span></footer>
        </section>
    </main>
    <script>
        document.querySelector('[data-password-toggle]')?.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.passwordToggle);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            this.setAttribute('aria-pressed', String(show));
            this.querySelector('[data-eye-open]')?.classList.toggle('hidden', !show);
            this.querySelector('[data-eye-closed]')?.classList.toggle('hidden', show);
        });
    </script>
</body>
</html>
