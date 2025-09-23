<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
	
    <!-- Bootstrap CSS -->
        <link rel="stylesheet" type="text/css" href="{{asset('cdn/bootstrap.css')}}" >
    <link rel="stylesheet" href="{{url('assets/main.css')}}">
    @if(app()->getLocale()=="ar")
    <link rel="stylesheet" href="{{url('assets/main_ar.css')}}">
    @endif


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	<link rel="stylesheet" href="/css/global.css">
	<link rel="stylesheet" href="/css/select2.css">
	
    @yield('css')
	@stack('css')
	{{-- <link ref="stylesheet" href="/css/"> --}}
    <title>ZAVERO Magic Sheet Application</title>
	

<style>
</style>
</head>
<body data-lang="{{app()->getLocale()}}" data-base-url="{{\Illuminate\Support\Facades\URL::to('/')}}"  data-token="{{ csrf_token() }}">
    <div class="container2 lang-{{app()->getLocale()}}">
        <div class="login global">
            <div class="loginInner">

                <a><img src="{{asset('assets/images/logo.png')}}" width="106" class="text-left float-left logo-img"></a>

                @auth
                    <a title="{{__('Home')}}" class="logout float-left" href="{{ route('home') }}"><i class="fas fa-home"></i></a>

                    {{-- <a title="{{ __('Systems List') }}" class="logout  float-left" href="{{ env('ZAVERO') . app()->getLocale() }}">
                        <i class="fas fa-list"></i>
                    </a> --}}
                    <a class="logout" href="{{LaravelLocalization::localizeUrl('logout') }}"
                        onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> {{__('Logout')}}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>



                    <a title="{{__('ContactUs')}}" class="logout float-left" href="{{ route('ContactUs') }}">
                        <i class="fas fa-file-signature"></i>
                    </a>

                @endauth
                @guest
                    <a title="{{__('Login')}}" class="logout float-left" href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> {{__('Login')}}</a>
                @endguest
                <?php $current = "/".app()->getLocale(); $next_lang = $current == "/en" ? "/ar": "/en" ;?>
                <a class="logout float-left" title="{{$next_lang == '/en' ?strtoupper(str_replace('/','',$next_lang)) : 'عربي' }}" href="{{url(str_replace($current,$next_lang,Request::fullUrl())) }}"> {{$current == '/en' ?strtoupper(str_replace('/','',$current)) : 'عربي' }} </a>

                @yield('header_components')
                <a href="#">
                    <h1 class="text-left"> &nbsp;</h1>
                </a>

                @yield('content')
            </div>
        </div>
    </div>
    <!-- Optional JavaScript; choose one of the two! -->

    @if (Request::is('*/dashboard') === true)
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
            integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
        </script>
        <script src="{{asset('cdn/bootstrap.js')}}
" ></script>
        </script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="{{asset('cdn/bootstrap.js')}}
" ></script>
        </script>
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>

        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    @else
        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
            integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
        </script>
    <script src="{{asset('cdn/bootstrap.js')}}
" ></script>
    
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
 <script src="{{asset('cdn/bootstrap.js')}}
" ></script>

    @endif
<script src="/js/jquery.repeater/src/lib.js" type="text/javascript"></script>
<script src="/js/jquery.repeater/src/jquery.input.js" type="text/javascript"></script>
<script src="/js/jquery.repeater/src/repeater.js" type="text/javascript"></script>
<script src="/js/jquery.repeater/form-repeater.js" type="text/javascript"></script>

<script src="/js/form-repeater.js"> </script>


@yield('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



		
<script src="/js/ckeditor-5.js"></script>
<script src="/js/validation.js"> </script>
<script src="/js/global.js"> </script>
<script src="/js/select2.js"> </script>

@stack('js_end')


 @if(session()->has('errors'))
 <script>
 Swal.fire({

            icon: "error",
            title: 'Error',
            text: "{!! session()->get('errors')->first()  !!}",
        
 })
 </script>
 
 
 @endif 
 
</body>

</html>
