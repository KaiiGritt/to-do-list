<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Daymark</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-card">
        <a class="brand login-brand" href="{{ route('login') }}"><span class="brand-mark"><span></span><span></span><span></span></span><span>daymark</span></a>
        <p class="date-label">Welcome back</p>
        <h1>Sign in to your workspace</h1>
        <p class="login-intro">Use your assigned account to continue.</p>

        @if ($errors->any())
            <div class="errors" role="alert">{{ $errors->first() }}</div>
        @endif

        <form class="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>

            <label class="remember-option" for="remember">
                <input id="remember" name="remember" type="checkbox" value="1">
                Remember me
            </label>

            <button class="submit-button" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>
