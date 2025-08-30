@extends('layouts.app')

@section('content')
<a href="#">
    <h1 class="text-left">{{__('ContactUs Page')}}</h1>
</a>
<div class="col-10 offset-1">
    <h1 class="d-flex justify-content-between steps-span ">
        <span><a href="{{ url()->previous() }}" style="color: white">< {{__("Back")}}</a></span>
        <span>{{ __('ContactUs Page')}}</span>
    </h1>
    <h1  class="bread-crumbs" >
            {{ __("ZAVERO Manufacturing") }}    >    {{ __('ContactUs Page')}}
    </h1>
    <Div class="ProjectList">
        <form action="{{route('Send_ContactUs')}}" method="POST">
        {{ csrf_field() }}
            <div class="form-group">
                <label for="exampleInputsubject1">{{__('Subject')}}</label>
                <input type="text" name="subject" required value="{{old('subject')}}" class="form-control" id="exampleInputsubject1" aria-describedby="subjectHelp">
                <input name="user_id" type="hidden" value="{{auth()->user()->id}}">
            </div>
            <div class="form-group">
                <label for="exampleInputsubject1">{{__('Message')}}</label>
                <textarea name="message"></textarea>
            </div>

            <button type="submit" class="btn btn-rev float-right">{{__('Send')}}</button>
        </form>
        <div class="clearfix"></div>
    </Div>

</div>
@endsection

@section('js')
<style>
.cke_notifications_area{
	display:none !important;
}
</style>

<script>
        CKEDITOR.replace( 'message' );
</script>
@endsection
