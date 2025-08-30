@extends('layouts.auth_app')

@section('content')
<div class="text-center">
<img src="{{asset('assets/images/logo2.png')}}" class="col-4  logo">
</div>

<h1>{{__('Register')}}</h1>
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('First Name') }}<span class="red"> *</span></label>
        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
        @error('name')
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('Last Name') }}<span class="red"> *</span></label>
        <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name" autofocus>
        @error('last_name')
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{__('Email address')}}<span class="red"> *</span></label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

        @error('email')
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('Mobile') }}</label>
        <input type="number" name="mobile" class="form-control" id="exampleInputmobile1"  value="{{ old('mobile') }}" aria-describedby="mobileHelp">
        @error('mobile')
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">{{ __('Company Name') }}</label>
        <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}"  autocomplete="company_name" autofocus>
        @error('name')
            <span class="invalid-feedback alert alert-danger" role="alert">
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
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{$message}}</strong>
            </span>
        @enderror

    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">{{__('Password')}}<span class="red"> *</span></label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

        @error('password')
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">{{__('Confirm Password')}}<span class="red"> *</span></label>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-rev float-right">{{__('Sign Up')}}</button>
</form>
<div class="clearfix"></div>

@endsection
