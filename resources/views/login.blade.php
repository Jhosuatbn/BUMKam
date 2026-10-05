<!DOCTYPE html>
<html>
<head>
    <title>Login BUMKam</title>
</head>
<body>

    <h1>Login BUMKam</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('login.process') }}" method="POST">
        @csrf

        <div>
            <label>Username</label><br>
            <input
                type="text"
                name="username"
                value="{{ old('username') }}"
                placeholder="Masukkan username"
            >
        </div>

        <br>

        <div>
            <label>Password</label><br>
            <input
                type="password"
                name="password"
                placeholder="Masukkan password"
            >
        </div>

        <br>

        <button type="submit">
            Login
        </button>
    </form>

</body>
</html>