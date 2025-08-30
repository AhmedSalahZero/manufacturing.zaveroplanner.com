@extends('layouts.app')
@section('css')
    <style>
        table {
            /* table-layout: fixed; */
            white-space: nowrap;
            /* background: red !important; */
        }

        table th {
            background-color: hsl(215deg 44% 38%) !important;
            color: white !important;
        }

    </style>
@endsection
@section('content')
    <!-- Button trigger modal -->
    {{-- <h1 class="text-left">{{__('Home')}}</h1> --}}

    <div class="col-12">
        <h1 class="d-flex justify-content-between steps-span">
            <span> {{ __('Home') }}</span>
        </h1>
        <h1 class="bread-crumbs">
            {{ __('ZAVERO Manufacturing') . ' > ' . __('Sharing Page') . ' > ' }} <b> {{ __('Study Name') }} <i
                    class="fa fa-arrow-{{ __('right') }}"></i> </b> {{ $project->name }}
        </h1>
        {{-- <form action="{{ route('sharingLink.store', $project) }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="exampleInputEmail1">{{ __('Add Contact Name') }}</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="form-control"
                    id="exampleInputname1" aria-describedby="nameHelp">
                <input name="user_id" type="hidden" value="{{ auth()->user()->id }}">
            </div>
            <button type="submit" class="btn btn-rev float-right">{{ __('Add') }}</button>
        </form> --}}
        <div class="clearfix"></div>
        <Div class="ProjectList table-responsive">
            <table class="table text-center ">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Date Of Creation') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody style="background: whitesmoke">
                    <?php $counter = 1; ?>
                    @foreach ($project->sharingLinks as $link)
                        <?php
                        $project_link = $link->link_code;
                        ?>
                        <tr class="{{ $link->closed == 0 ? 'table-info' : '' }}">
                        <tr class="{{ $link->closed == 0 ? 'table-info' : 'table-dark' }}">
                            <td scope="row">{{ $counter }}</th>
                            <td>{{ $link->name ?? "-"  }} </td>
                            <td>{{ $link->created_at->diffForHumans() }} </td>

                            <td>
                                <div id="secretInfo{{ $link->id }}" style="display: none;">
                                    {{ route('study_info.view', [$project_link]) }}</div>
                                @if ($link->closed == 0)
                                    <button class="p-2 btn btn-rev " onclick="outFunc({{ $link->id }})" type="button"
                                        id="btnCopy{{ $link->id }}" ><span
                                            class="tooltiptext"><i class="far fa-copy"
                                                id="myTooltip{{ $link->id }}"></i> {{ __('Copy') }}</span>


                                    </button>
                                @endif
                                <button class="p-2 btn btn-rev ml-2" data-toggle="modal"
                                    data-target="#exampleModal{{ $link->id }}"
                                    title="{{ $link->closed == 0 ? __('Close Link') : __('Reactivate Link') }}"><i
                                        class="fas {{ $link->closed == 0 ? 'fa-times-circle' : 'fa-lock-open' }}"></i>
                                    {{ __($link->closed == 0 ? 'Stop Sharing' : 'Activate Sharing') }}
                                </button>
                            </td>
                            <td>
                                @if ($link->closed == 0)
                                    <i class="{{ $link->open == 0 ? 'fas fa-envelope' : 'fas fa-envelope-open-text' }}">
                                    </i>
                                        @if( $link->open == 0 )
                                            {{__('Unread')}}
                                        @else
                                            <?php $number_of_viewers = App\SharingLinkVisitor::where('link_code',$project_link)->count(); ?>
                                            {{$number_of_viewers . ' ' . __('Views')}}
                                        @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <?php $counter++; ?>
                        <!-- ////////////////////////// Modal ////////////////////////////////// -->

                        <div class="modal fade" id="exampleModal{{ $link->id }}" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalLabel{{ $link->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" style="color: black"
                                            id="exampleModalLabel{{ $link->id }}">
                                            {{ __($link->closed == 0 ? 'Stop Sharing' : 'Activate Sharing') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body modal-text">
                                        {{ __('Do You Really Want To ' . ($link->closed == 0 ? 'Close' : 'Reactivate') . 'This Link ?') }}
                                    </div>
                                    <div class="modal-footer d-flex">
                                        <a href="{{ route('sharingLink.status', [$project, $link]) }}"
                                            class="btn btn-rev {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button">
                                            {{ __('Yes') }} </a>
                                        <a class="btn btn-danger {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button"
                                            data-dismiss="modal" aria-label="Close">{{ __('No') }}</a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
            {{-- Linls For current project --}}
            {{-- @foreach ($project->sharingLinks as $link)
                <div class="row" @if (app()->getLocale() == 'ar') style="direction:rtl" @endif>

                    <div class="col-12">
                        <div class="projectItem {{$link->closed == 0 ? "noHover" : "closed-link"}} d-flex justify-content-between">

                            <Span class="p-2" > {{ $link->name }} </Span>
                            <Span class="p-2"> <input readonly class="form-control" type="text"
                                    value="{{ route('study_info.view', [$project_link]) }}"
                                    id="myInput{{ $link->id }}"></Span>

                            @if ($link->closed == 0)
                                <button class="p-2 btn btn-rev " onclick="myFunction({{ $link->id }})"
                                    onmouseout="outFunc({{ $link->id }})"><span class="tooltiptext"><i
                                            class="far fa-copy" id="myTooltip{{ $link->id }}"></i> {{__('Copy')}}</span>
                                </button>
                            @endif
                            <button  class="p-2 btn btn-rev ml-2" data-toggle="modal" title="{{$link->closed == 0 ? __('Close Link') : __('Reactivate Link')}}"
                                data-target="#exampleModal{{ $link->id }}"><i class="fas {{$link->closed == 0 ?'fa-times-circle' : 'fa-lock-open'}}"></i> {{__($link->closed == 0 ?'Stop Sharing':'Activate Sharing')}}</button>

                        </div>
                    </div>
                </div>
                <!-- Modal -->
                <div class="modal fade " id="exampleModal{{ $link->id }}" tabindex="-1"
                    aria-labelledby="exampleModalLabel{{ $link->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header d-none">
                                <h5 class="modal-title" id="exampleModalLabel{{ $link->id }}">Delete</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body modal-text">

                                {{ __('Do You Really Want To ' .($link->closed == 0 ? 'Close' : 'Reactivate' ). 'This Link ?') }}

                                    <a href="{{ route('sharingLink.status', [$project,$link]) }}" class="btn btn-rev {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button" > {{__('Yes')}} </a>
                                    <a class="btn btn-danger {{ app()->getLocale() == 'ar' ? 'float-left' : 'float-right' }} main-page-button"
                                        data-dismiss="modal" aria-label="Close">{{ __('No') }}</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach --}}
        </div>

    </Div>
    </div>

@endsection
@section('js')

    <script type="text/javascript">
        function outFunc($id) {

            var $body = document.getElementsByTagName('body')[0];
            var $btnCopy = document.getElementById('btnCopy' + $id);
            var secretInfo = document.getElementById('secretInfo' + $id).innerHTML;

            var copyToClipboard = function(secretInfo) {
                var $tempInput = document.createElement('INPUT');
                $body.appendChild($tempInput);
                $tempInput.setAttribute('value', secretInfo)
                $tempInput.select();
                document.execCommand('copy');
                $body.removeChild($tempInput);
            }
            copyToClipboard(secretInfo);
        }
    </script>
@endsection
