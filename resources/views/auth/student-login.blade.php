<!DOCTYPE html>
<html>

<head>
    <title>Student Login</title>
</head>

<body>
    <h2>Login</h2>
    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <div>
            <label>CPR</label>
            <input type="text" name="cpr" value="{{ old('cpr') }}">
            @error('cpr')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password">
            @error('password')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">Login</button>
    </form>
</body>

</html>
