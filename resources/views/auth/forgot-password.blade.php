@extends('layouts.guest')
@section('content')
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

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
                                <h6 class="mt-3 mb-1 fw-semibold text-light font-12">
                                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                                </h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label text-light">{{ __('Email') }}</label>
                                    <input id="email" class="form-control" type="email" name="email"
                                        value="{{ old('email') }}" required autofocus>
                                    @if ($errors->has('email'))
                                        <div class="mt-2 text-danger">
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Email Password Reset Link') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
