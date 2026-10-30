@extends('layouts.auth')

@section('title', 'Log in · '.config('app.name'))

@section('content')
    <h1>Welcome back</h1>
    <p>Log in to read your library, follow invited chapters, and manage your books.</p>

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="form-grid">
            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span>Password</span>
                <input type="password" name="password" required autocomplete="current-password">
                @error('password') <span class="error-text">{{ $message }}</span> @enderror
            </label>

            <label class="checkbox">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Remember me on this device
            </label>
        </div>

        <button class="btn btn-accent btn-block mt-3" type="submit">Log in</button>
    </form>

    <p class="auth-foot">
        New to PenakeTik? <a href="{{ route('register') }}">Create a free account</a>
    </p>
@endsection
