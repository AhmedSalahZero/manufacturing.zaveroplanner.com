@extends('layouts.app')

@section('content')
<div class="clearfix"></div>
<div class="text-center">
    <img src="{{asset('assets/images/logo2.png')}}" class="col-4 ">
</div>

<h1 class="alert alert-danger col-10 offset-1">{{ __('Verify Your Email Address') }}</h1>

<div class="col-10 offset-1">
    <Div class="ProjectList">

        <form  action="{{ route('verification.resend') }}" method="POST">
            @csrf

            <div class="projectItem  btn-danger" >
                {{ __('Verify Your Email Address') }}
            </div>



            <div class="formItem alert-danger">
                <div class="col-12 alert-danger">

                    <div class="row ">
                        <div class="col-10">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert" >
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-10 ">
                            {{ __('Before proceeding, please check your email for a verification link.') }}
                            {{ __('If you did not receive the email') }},
                        </div>

                    </div>

                </div>

            </div>
            <button type="submit" class="btn btn-rev float-right">{{ __('click here to request another') }}</button>
        </form>
    </Div>
</div>
<div class="clearfix"></div>
@endsection

{{-- @extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ __('Before proceeding, please check your email for a verification link.') }}
                    {{ __('If you did not receive the email') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection --}}
