@extends('layouts.app')

@section('content')
<a href="#">
    <h1 class="text-left"> {{ __('Privacy Policy') }} </h1>
</a>
<?php $policy_data = File::get(storage_path('app/policy_'.app()->getLocale().'.txt')); //asset("resources/views/admin/policy.txt")) ;    ?>
<div class="col-10 offset-1">
    <a href="{{ url()->previous() }}" class="btn btn-danger">< {{__("Back")}}</a>
    <Div class="ProjectList">

        <form  action="{{route('policy.submit')}}" method="POST">
            {{ csrf_field() }}

            <div class="projectItem" >
               {{ __('Privacy Policy') }}
            </div>

            <div class="formItem">
                <div class="col-12 Privacy" >
                    {!!$policy_data!!}
                </div>

                <div class="col-11 offset-1">
                    <label>
                    <input type="checkbox" validation name="acceptance_of_privacy_policy" value="1" checked> {{__('Accept Terms And Conditions')}}
                    </label>
                    <div class="clearfix"></div>
                    <button  type="submit" id="next" class="btn btn-rev  main-page-button float-right">{{__('Agree')}}</button>
                    {{-- <a href="{{route('home')}}" id="next" class="btn btn-rev  main-page-button float-right">{{__('Submit')}}</a> --}}

                    <div class="clearfix"></div>
                </div>
            </div>



            {{-- <button  type="submit" class="btn btn-rev float-right">{{__('Next')}}</button> --}}
            {{-- {{route('home')}} --}}

        </form>
    </Div>
</div>
<div class="clearfix"></div>
@endsection
@section('script')
<script>
    $('input[type="checkbox"]').click(function(){
        if($(this).prop("checked") == true){
            $('#next').attr('href',"{{route('home')}}");
            $('#next').removeClass('disabled');
        }
        else if($(this).prop("checked") == false){
            $('#next').attr('href',"");
            $('#next').addClass('disabled');
        }
    });
</script>
@endsection
