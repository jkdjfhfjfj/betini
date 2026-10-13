@extends($theme.'layouts.app')
@section('title','Register')
@section('content')
    <section class="login-section auth-page auth-register-page">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-lg-6 p-0">
                    <div class="text-box h-100">
                        <div class="overlay h-100">
                            <div class="text">
                                <h2>@lang('create an account')</h2>
                                <p>@lang('Create your player account to follow matches, place bets and manage your wallet.')</p>
                                <a href="{{route('home')}}">@lang('back to home')</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-wrapper d-flex align-items-center h-100">
                        <form action="{{ route('register') }}" method="post" class="auth-form-card">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12 auth-form-heading">
                                    <span class="auth-eyebrow">@lang('NEW PLAYER')</span>
                                    <h4>@lang('Create your account')</h4>
                                    <p>@lang('A few details are all you need to get started.')</p>
                                </div>
                                @if(session()->get('sponsor') != null)
                                    <div class="input-box col-12">
                                        <label class="auth-field-label" for="register-sponsor">@lang('Sponsor Name')</label>
                                        <input type="text" name="sponsor" class="form-control" id="register-sponsor"
                                               placeholder="{{trans('Sponsor By') }}"
                                               value="{{session()->get('sponsor')}}" readonly>
                                    </div>
                                @endif
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-firstname">@lang('First name')</label>
                                    <input
                                        type="text"
                                        name="firstname"
                                        value="{{old('firstname')}}"
                                        class="form-control"
                                        id="register-firstname" autocomplete="given-name"
                                        placeholder="@lang('First name')" required/>
                                    @error('firstname')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-lastname">@lang('Last name')</label>
                                    <input
                                        type="text"
                                        name="lastname"
                                        value="{{old('lastname')}}"
                                        class="form-control"
                                        id="register-lastname" autocomplete="family-name"
                                        placeholder="@lang('Last name')" required/>
                                    @error('lastname')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-username">@lang('Username')</label>
                                    <input
                                        type="text"
                                        name="username"
                                        value="{{old('username')}}"
                                        class="form-control"
                                        id="register-username" autocomplete="username"
                                        placeholder="@lang('Choose a username')" required/>
                                    @error('username')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-email">@lang('Email address')</label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{old('email')}}"
                                        class="form-control"
                                        id="register-email" autocomplete="email"
                                        placeholder="@lang('you@example.com')" required/>
                                    @error('email')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-phone-code">@lang('Country calling code')</label>
                                    @php
                                        $country_code = (string) @getIpInfo()['code'] ?: null;
                                        $myCollection = collect(config('country'))->map(function($row) {
                                            return collect($row);
                                        });
                                        $countries = $myCollection->sortBy('code');
                                    @endphp
                                    <select
                                        class="form-select country_code dialCode-change" name="phone_code"
                                        aria-label="@lang('Country calling code')" id="register-phone-code" required>
                                        <option selected="" disabled>@lang('Select Code')</option>
                                        @foreach(config('country') as $value)
                                            <option value="{{$value['phone_code']}}"
                                                    data-name="{{$value['name']}}"
                                                    data-code="{{$value['code']}}"
                                                {{$country_code == $value['code'] ? 'selected' : ''}}> {{$value['name']}}
                                                ({{$value['phone_code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-phone">@lang('Phone number')</label>
                                    <input
                                        type="text"
                                        name="phone" value="{{old('phone')}}"
                                        class="form-control dialcode-set"
                                        id="register-phone" autocomplete="tel-national" inputmode="tel"
                                        placeholder="712 345 678" required/>
                                    @error('phone')
                                    <span class="text-danger mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <input type="hidden" name="country_code" value="{{old('country_code', $country_code)}}"
                                       class="text-dark">
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-password">@lang('Password')</label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        id="register-password" autocomplete="new-password"
                                        placeholder="@lang('Create a password')" required/>
                                    @error('password')
                                    <span class="text-danger mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="input-box col-6">
                                    <label class="auth-field-label" for="register-password-confirm">@lang('Confirm password')</label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        class="form-control"
                                        id="register-password-confirm" autocomplete="new-password"
                                        placeholder="@lang('Confirm your password')" required/>
                                </div>
                                @if(basicControl()->reCaptcha_status_registration)
                                    <div class="col-md-6 box mb-4 form-group">
                                        {!! NoCaptcha::renderJs(session()->get('trans')) !!}
                                        {!! NoCaptcha::display(['data-theme' => 'dark']) !!}
                                        @error('g-recaptcha-response')
                                        <span class="text-danger mt-1">@lang($message)</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                            <button type="submit" class="btn-custom w-100 mt-2">@lang('sign up')</button>
                            <div class="bottom">
                                @lang('Already have an account?')

                                <a href="{{route('login')}}">@lang('Login here')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
    <script>
        "use strict";
        $(document).ready(function () {
            setDialCode();
            $(document).on('change', '.dialCode-change', function () {
                setDialCode();
            });

            function setDialCode() {
                let selected = $('.dialCode-change option:selected');
                $('input[name="country_code"]').val(selected.data('code') || '');
            }

        });

    </script>
@endpush

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
