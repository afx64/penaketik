@if (session('status'))
    <div class="flash flash-ok" role="status">
        <span aria-hidden="true">✓</span>
        <div>{{ session('status') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="flash flash-error" role="alert">
        <span aria-hidden="true">!</span>
        <div>
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
