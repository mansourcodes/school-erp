@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-header">
                        Student Login
                    </div>

                    <form method="POST" action="{{ url('/login') }}" class="w-100 mx-auto my-5" style="max-width: 400px;">
                        @csrf

                        <div class="mb-3">
                            <label for="cpr" class="form-label">CPR</label>
                            <input type="text" name="cpr" id="cpr" value="{{ old('cpr') }}"
                                class="form-control @error('cpr') is-invalid @enderror">
                            @error('cpr')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>




                </div>
            </div>
        </div>
    </div>
@endsection
