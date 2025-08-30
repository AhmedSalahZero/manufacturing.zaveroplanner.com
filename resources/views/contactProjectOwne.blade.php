@extends('layouts.app')

@section('content')
{{-- <a href="#">
    <h1 class="text-left">{{__('ContactUs Page')}}</h1>
</a> --}}
<div class="col-10 offset-1">
    <?php $project_slug = $project->slug .'-' .$project->id; ?>


    <h1 class="d-flex justify-content-between steps-span">
        <a href="{{route('study_info.view',[$project_slug])}}" style="color: white">{{__("View Data")}}</a>
        <span>{{__('Contact With Study Owner Or ZAVERO Team') }}</span>
    </h1>
    <h1  class="bread-crumbs" >
        {{ __("ZAVERO Manufacturing") }}    > {{$project->name}}  >  {{__('Contact With Study Owner Or ZAVERO Team') }}
    </h1>

    <Div class="ProjectList">
        <form action="{{route('Send_ContactProjectOwner',[$project_slug])}}" method="POST">
        {{ csrf_field() }}
            <div class="form-group">
                <label for="exampleInputsender_name1">{{__('Enter Your Name')}}</label>
                <input type="text" name="sender_name" required value="{{old('sender_name')}}" class="form-control" id="exampleInputsender_name1" aria-describedby="sender_nameHelp">

            </div>
            <div class="form-group">
                <label for="exampleInputsendermail">{{__('Enter Your Mail')}}</label>
                <input type="email" name="sender_mail" required value="{{old('sender_mail')}}" class="form-control" id="exampleInputsendermail" aria-describedby="subjectHelp">
            </div>
            <div class="form-group">
                <label for="exampleInputsender_name1">{{__('Send This Mail To')}}</label>
                <select name="to" id="to" class="form-control">
                    <option value="owner">Study Owner</option>
                    <option value="zavero_team">ZAVERO Team</option>
                </select>

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


<script>
        CKEDITOR.replace( 'message' );
</script>
@endsection
