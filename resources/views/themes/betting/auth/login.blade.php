@extends($theme.'layouts.app')
@section('title','Login')
@section('content')
    <section class="login-section auth-page auth-login-page">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-lg-6 p-0">
                    <div class="text-box h-100">
                        <div class="overlay h-100">
                            <div class="text">
                                <span class="auth-eyebrow">@lang('PROPHECY SPORTS')</span>
                                <h2>@lang('Sign in to your account')</h2>
                                <p>@lang('Pick up where you left off. Your bets and wallet are ready when you are.')</p>
                                <a href="{{route('home')}}">@lang('back to home')</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-wrapper d-flex align-items-center h-100">
                        <form action="{{ route('login') }}" method="post" class="auth-form-card">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12 auth-form-heading">
                                    <span class="auth-eyebrow">@lang('PLAYER ACCOUNT')</span>
                                    <h4>@lang('Welcome back')</h4>
                                    <p>@lang('Sign in to continue to your account.')</p>
                                </div>
                                <div class="input-box col-12">
                                    <label class="auth-field-label" for="login-identity">@lang('Email or username')</label>
                                    <input type="text" class="form-control" name="username"
                                           id="login-identity" autocomplete="username"
                                           placeholder="@lang('Enter your email or username')" required/>
                                    @error('username')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                    @error('email')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-12">
                                    <label class="auth-field-label" for="login-password">@lang('Password')</label>
                                    <input type="password" class="form-control" name="password"
                                           id="login-password" autocomplete="current-password"
                                           placeholder="@lang('Enter your password')" required/>
                                    @error('password')
                                    <span class="text-danger mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                @if(basicControl()->reCaptcha_status_login)
                                    <div class="box mb-4 form-group">
                                        {!! NoCaptcha::renderJs(session()->get('trans')) !!}
                                        {!! NoCaptcha::display(['data-theme' => 'dark']) !!}
                                        @error('g-recaptcha-response')
                                        <span class="text-danger mt-1">@lang($message)</span>
                                        @enderror
                                    </div>
                                @endif
                                <div class="col-12">
                                    <div class="links">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="remember" {{ old('remember') ? 'checked' : '' }}
                                                id="flexCheckDefault"/>
                                            <label
                                                class="form-check-label"
                                                for="flexCheckDefault">
                                                @lang('Remember Me')
                                            </label>
                                        </div>
                                        <a href="{{ route('password.request') }}">@lang('Forgot password?')</a>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-custom auth-submit w-100">@lang('Sign in')</button>
                            <div class="bottom">
                                @lang("Don't have an account?")

                                <a href="{{ route('register') }}">@lang('Create account')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
    <style>
        .modal .text-box,
            .login-section .text-box {
                background-image: url({{getFile(config('location.logo.path').'loginImage.png')}});
                background-size: cover;
                background-position: center;
        }
    </style>
@endpush
