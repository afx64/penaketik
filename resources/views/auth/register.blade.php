@extends('layouts.auth')

@section('title', 'Create account · '.config('app.name'))

@section('content')
    <h1>Start your shelf</h1>
    <p>A reader account keeps your purchases, liked books and comments in one place. The author's studio stays private.</p>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="form-grid">
            <label class="field">
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                @error('name') <span class="error-text">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span>Password <span class="hint">at least 8 characters</span></span>
                <input type="password" name="password" required autocomplete="new-password">
                @error('password') <span class="error-text">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span>Confirm password</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </label>
        </div>

        <button class="btn btn-accent btn-block mt-3" type="submit">Create reader account</button>
    </form>

    <p class="auth-foot">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </p>
@endsection
