{{-- @extends('layouts.auth_app')

@section('content')

<h1>{{ __('Confirm Password') }}</h1>
{{ __('Please confirm your password before continuing.') }}

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('First Name') }}</label>
        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('Last Name') }}</label>
        <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name" autofocus>
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{__('Email address')}}</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('Mobile') }}</label>
        <input type="number" name="mobile" class="form-control" id="exampleInputmobile1"  value="{{ old('mobile') }}" aria-describedby="mobileHelp">
        @error('mobile')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('Company Name') }}</label>
        <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}" required autocomplete="company_name" autofocus>
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        <label for="department">{{ __('Departement') }}</label>
        <select class="form-control @error('department') is-invalid @enderror" name="department" id="department">
            <option value=""> {{__('Select')}} </option>
            <option value="top-management" {{old('department') == 'top-management'? 'selected':'' }}> {{__('Top Management')}} </option>
            <option value="project-and-planning" {{old('department') == 'project-and-planning'? 'selected':'' }}> {{__('Project And Planning')}} </option>
            <option value="financial-departement" {{old('department') == 'financial-departement'? 'selected':'' }}> {{__('Financial Departement')}} </option>
            <option value="investor" {{old('department') == 'Investor'? 'selected':'' }}> {{__('Investor')}} </option>
            <option value="others" {{old('department') == 'Others'? 'selected':'' }}> {{__('Others')}} </option>
        </select>
        @error('department')
            <span class="invalid-feedback" role="alert">
                <strong>{{$message}}</strong>
            </span>
        @enderror

    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">{{__('Password')}}</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
    @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">{{__('Confirm Password')}}</label>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-rev float-right">{{__('Sign Up')}}</button>
</form>
<div class="clearfix"></div>

@endsection --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Confirm Password') }}</div>

                <div class="card-body">
                    {{ __('Please confirm your password before continuing.') }}

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Confirm Password') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
