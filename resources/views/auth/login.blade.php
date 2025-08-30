@extends('layouts.auth_app')

@section('content')
<div class="text-center">
    <a href="{{url('/')}}">
<img src="{{asset('assets/images/logo2.png')}}" class="col-4  logo">
    </a>
</div>
<h1>{{__('Login')}}</h1>
<form method="POST" action="{{ route('login') }}">
    @csrf
    {{--  --}}
    <div class="form-group">
        <label for="exampleInputEmail1">{{__('Email address')}}</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"  autocomplete="email" required autofocus id="exampleInputEmail1" aria-describedby="emailHelp">
        @error('email')
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">{{__('Password')}}</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

        @error('password')
            <span class="invalid-feedback alert alert-danger" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
  <div class="form-group form-check">
    <input type="checkbox" class="form-check-input"  name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
    <label class="form-check-label" for="remember">{{__('Remember Me')}}</label>
  </div>
  <button type="submit" class="btn btn-rev float-right">{{ __('Login') }}</button>
  <?php $current = "/".app()->getLocale(); $next_lang = $current == "/en" ? "/ar": "/en" ;?>

<a class="btn btn-rev float-right lang" title="{{strtoupper(str_replace('/','',$next_lang))}}" href="{{url(str_replace($current,$next_lang,Request::fullUrl())) }}">{{$current == "/en" ? " العربية " : " English "}}<i class="fas fa-globe"> </i></a>
</form>

<div class="clearfix"></div>
<div class="otherLinks">
    <a class="btn btn-rev button-view" href="{{ route('register') }}">{{ __('Create New Account') }} </a>
    <a class="btn btn-rev button-view" href="{{ route('password.request') }}">{{ __('Forgot Password?') }}</a>
</div>
@endsection
