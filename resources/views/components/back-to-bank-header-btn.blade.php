@props([
	'createRoute',
	'createPermissionName'
])
<div class="kt-portlet__head">
        <div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !Request('active') || Request('active') == 'bank-accounts' ?'active':'' }}" href="{{ route('view.financial.institutions',['company'=>$company->id]) }}" role="tab">
                        <i class="fa fa-arrow-left"></i> {{ __('Back To Banks Table') }}
                    </a>
                </li>

            </ul>
			
			@if($createRoute && $createPermissionName)
			@if(hasAuthFor($createPermissionName))
            <div class="flex-tabs">
                <a href="
				{{ $createRoute }}
				" class="btn  active-style btn-icon-sm align-self-center">
                    <i class="fas fa-plus"></i>
                    {{ __('New Record') }}
                </a>
            </div>
			@endif
			@endif 
			

        </div>
    </div>
	