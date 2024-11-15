@extends('layouts.guest')
@section('content')
    <div class="row vh-100 d-flex justify-content-center">
        <div class="col-12 align-self-center">
            <div class="row">
                <div class="col-lg-4 mx-auto">
                    <div class="card bg-dark">
                        <div class="card-body p-0 auth-header-box">
                            <div class="text-center p-3">
                                <a href="#" class="logo logo-admin">
                                    <img src="{{ asset('images/watchme-logo-white.png') }}" height="50" alt="logo"
                                        class="auth-logo">
                                </a>
                                <h4 class="mt-3 mb-1 fw-semibold text-light font-18">Let's Get Started</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form class="form-horizontal auth-form" method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label text-light" for="email">Email</label>
                                    <div class="input-group">
                                        <input type="email" class="form-control" name="email" id="email"
                                            placeholder="Enter email" required autofocus autocomplete="email">
                                    </div>
                                    @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-light" for="password">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password" id="userpassword"
                                            placeholder="Enter password" required autocomplete="current-password">
                                    </div>
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row my-3">
                                    <div class="col-sm-6">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember"
                                                value="1">
                                            <label class="form-check-label text-muted text-light" for="remember_me">Remember me</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 text-end">
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="text-muted text-light font-13"><i
                                                    class="dripicons-lock"></i> Forgot password?</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Log In
                                            <i class="fas fa-sign-in-alt ms-1"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
