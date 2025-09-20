<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
</head>

<body>
    <h2>Welcome, {{ Auth::guard('student')->user()->name }}</h2>
    <p>Your CPR: {{ Auth::guard('student')->user()->cpr }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>

</html>
