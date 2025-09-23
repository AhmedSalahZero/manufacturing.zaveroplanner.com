@extends('layouts.app')
@if (Auth::user()->email == 'mahmoud.youssef@squadbcc.com' || Auth::user()->email == 'samer.tawfik@squadbcc.com' )
{{-- All Users Projects --}}
<a title="{{ __('All Users Projects') }}" class="logout" href="{{ route('all.users.projects') }}"><i class="fas fa-align-justify"></i></a>
@endif
@section('content')


<div class="col-12">
    <h1 class="d-flex justify-content-between steps-span">
        <span> {{ __('Home')}}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __("ZAVERO Manufacturing") .' > '. __('Home') }}
    </h1>
    <form action="{{ route('projects.store') }}" method="POST">
        {{ csrf_field() }}
		<div class="row">
		<div class="col-12">
		            <label class="new-study-label" for="exampleInputEmail1">{{ __('Add New Study') }}</label>
		</div>
		</div>
		<div class="row">
			<div class="col-md-8">
      	      <input placeholder="{{ __('Insert New Study Name') }}" type="text" name="name" required value="{{ old('name') }}" class="form-control  p-10 d-inline-block w-60" id="exampleInputname1" aria-describedby="nameHelp">
			</div>
			<div class="col-md-4 ml-n-1">
			<button type="submit"  class="btn d-inline-flex btn-secondary btn-outline-hover-brand btn-icon  add-study-btn " title="{{ __('Add') }}" href="#">
			{{ __('Add') }}
			</button>
            <input name="user_id" type="hidden" value="{{ auth()->user()->id }}">
			
			</div>
		</div>
        {{-- <div class="form-group">
			
        </div> --}}
		
        {{-- <button type="submit" class="btn btn-rev float-right">{{ __('Add') }}</button> --}}
    </form>
    <div class="clearfix"></div>
    <Div class="ProjectList">
        <!-- Modal -->
        {{-- <div class="modal fade " id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header d-none">
                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-text">

                        {{ __('This is the light version of REVERO, a one day financial planning tool for your Real Estate Projects, if you want to get the full version please click here') }}

                        <div class="clearfix"></div>

                        <a href="https://reveroplanner.com/" class="btn btn-danger {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button" data-dismiss="modal" aria-label="Close">{{ __('Close') }}</a>

                        <a href="https://reveroplanner.com/" class="btn btn-rev {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button">{{ __('REVERO') }}</a>
                    </div>
                </div>
            </div>
        </div> --}}
        @foreach ($projects as $project)
        <div class="row" @if (app()->getLocale() == 'ar') style="direction:rtl" @endif>
            <div class="col-8">
                @if ($project->completed == 1)
                <?php $projectLink = route('main.project.page', [$project->id]); ?>
                @else
                <?php $projectLink = route('projects.edit', [$project->id]); ?>
                @endif

                <a href="{{ $projectLink }}">
                    <div class="projectItem study-label ">
                        {{ $project->name }}
                        <Span>- {{ __('Click To Start') }} </Span>
                    </div>
                </a>
            </div>
            <div>


                                    <a data-toggle="modal" data-target="#sharing{{ $project->id }}" type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon share-btn-class" title="{{ __('Share') }}" href="#"><i class="fa fa-share-alt exclude-icon default-icon-color"></i></a>
                                    <a data-toggle="modal" data-target="#copy{{ $project->id }}" type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon copy-btn-class" title="{{ __('Copy') }}" href="#"><i class="fa fa-copy exclude-icon default-icon-color"></i></a>
                                    <a data-toggle="modal" data-target="#deleteModel{{ $project->id }}" type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon delete-btn-class" title="{{ __('Delete') }}" href="#"><i class="fa fa-trash-alt exclude-icon default-icon-color"></i></a>

                {{-- <button type="submit" class="btn btn-rev float-left delete-btn" data-toggle="modal" data-target="#exampleModal{{ $project->id }}"><i class="fas fa-trash-alt"></i></button> --}}
                {{-- <button type="button" class="btn btn-rev float-left  delete-btn share_button" data-toggle="modal" data-target="#sharing{{ $project->id }}"><i class="fas fa-share-alt"></i></button> --}}
                {{-- <button type="button" class="btn btn-rev float-left  delete-btn share_button" data-toggle="modal" data-target="#copy{{ $project->id }}"><i class="fas fa-copy"></i></button> --}}
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade " id="deleteModel{{ $project->id }}" tabindex="-1" aria-labelledby="exampleModalLabel{{ $project->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header d-none">
                        <h5 class="modal-title" id="exampleModalLabel{{ $project->id }}">Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-text">

                        {{ __('Do You Really Want To Delete This Project ?') }}
                        <form action="{{ route('projects.destroy', [$project->id]) }}" method="post">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <a class="btn btn-danger {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button" data-dismiss="modal" aria-label="Close">{{ __('Close') }}</a>
                            <button type="submit" class="btn btn-rev {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button" data-toggle="modal" data-target="#exampleModal{{ $project->id }}">{{ __('DELETE') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="copy{{ $project->id }}" tabindex="-1" role="dialog" aria-labelledby="copyModalLabel{{ $project->id }}" aria-hidden="true" style="color: black">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="copyModalLabel{{ $project->id }}">{{ __('Copy') }}</h5>
                    </div>
					       <form action="{{ route('copy.project', ['project'=>$project->id]) }}" id="form{{ $project->id }}" method="POST">
                    <div class="modal-body">
                 
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="name{{ $project->id }}" class="col-form-label">{{ __('New Project Name') }}</label>
                                {{-- <span class="sharing-span"> {{__('This field will help you to control Show/Hide the project for them in the future')}} </span> --}}
                                <input type="text" name="name" class="form-control" id="name{{ $project->id }}">
                            </div>
                   
                    </div>
                    <div class="modal-footer d-flex">
                        <button type="button" class="btn btn-sm btn-secondary p-2" data-dismiss="modal">{{__('Close')}}</button>
                        <button type="submit" class="btn btn-sm btn-info p-2 " ><span class="tooltiptext submit-copy-btn"><i class="far fa-copy" > {{__('Copy')}}</i> </span>
                        </button>
                    </div>
					     </form>
                </div>
            </div>
        </div>

        <!-- Sharing Modal -->
        <div class="modal fade" id="sharing{{ $project->id }}" tabindex="-1" role="dialog" aria-labelledby="sharingModalLabel{{ $project->id }}" aria-hidden="true" style="color: black">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sharingModalLabel{{ $project->id }}">{{ __('Sharing Link') }}</h5>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('sharingLink.store', $project) }}" id="form{{ $project->id }}" method="POST">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="name{{ $project->id }}" class="col-form-label">{{ __('Add the contact name you want to share the study with') }}</label>
                                {{-- <span class="sharing-span"> {{__('This field will help you to control Show/Hide the project for them in the future')}} </span> --}}
                                <input type="text" name="name" class="form-control" id="name{{ $project->id }}">
                            </div>
                            <?php  $code = uniqid(rand()); ?>
                            <div class="form-group">
                                <label for="myInput{{ $project->id }}" class="col-form-label">{{ __('Sharing Link') }}</label>
                                <input readonly type="text" id="myInput{{ $project->id }}" value="{{ route('study_info.view', [$code]) }}" class="form-control">
                                <input type="hidden" name="link_code" value="{{ $code }}">
                            </div>
                            <input name="user_id" type="hidden" value="{{ auth()->user()->id }}">
                        </form>
                    </div>
                    <div class="modal-footer d-flex">
                        <a href="{{ route('sharing.page', $project) }}" class="btn btn-light mr-auto p-2">{{__('Sharing Links')}}</a>
                        <button type="button" class="btn btn-secondary p-2" data-dismiss="modal">{{__('Close')}}</button>
                        <button type="submit" class="btn btn-info p-2" onclick="myFunction({{ $project->id }})" onmouseout="outFunc({{ $project->id }})"><span class="tooltiptext"><i class="far fa-copy submit-copy-btn " id="myTooltip{{ $project->id }}"> {{__('Copy')}}</i> </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- old Share Modal --}}
        <div class="modal fade" id="shareModal{{ $project->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="col-md-12">
                            <?php $project_slug = $project->slug . '-' . $project->id; ?>
                            <input class="form-control" type="text" value="{{ route('study_info.view', [$project_slug]) }}" id="myInput{{ $project->id }}">
                        </div>
                        <br>
                        <button class="btn btn-rev" onclick="myFunction({{ $project->id }})" onmouseout="outFunc({{ $project->id }})">
                            <span class="tooltiptext"><i id="myTooltip{{ $project->id }}" class="fas fa-copy "> Copy Link</i>
                            </span>

                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        @endforeach
    </Div>
</div>

@endsection
@section('js')
<script>
    function myFunction(project_id) {
        var copyText = document.getElementById("myInput" + project_id);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");

        var tooltip = document.getElementById("myTooltip" + project_id);
        tooltip.innerHTML = "";
        tooltip.innerHTML = " {{__('Copied')}}";
        $("#form" + project_id).submit();
    }

    function outFunc(project_id) {
        var tooltip = document.getElementById("myTooltip" + project_id);
        //   tooltip.innerHTML = " Copy Link";
    }

</script>
@endsection
