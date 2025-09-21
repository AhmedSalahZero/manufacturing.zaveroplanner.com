@extends('layouts.app')
@section('content')
<style>

</style>
<div class="col-12">
<div id="number-of-products" data-value="{{ count($products) }}"></div>
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{ __('Back') }}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] . '/' . $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ __($step_data['route_name']) }}
    </h1>
    <Div class="ProjectList">
	{{-- <div>
	
	<div id="myCard" class="card">
    <button id="toggleBtn" class="fullscreen-btn">⛶</button>
    <h2>My Card</h2>
    <p>This card can go fullscreen when you click the button.</p>
  </div>
  
	</div> --}}
        <form action="#" method="POST">
        {{-- <form action="{{ route('expenses.submit', $project) }}" method="POST"> --}}
            {{ csrf_field() }}
            <div class="col-12 alert alert-info text-center">
                <span class="red">{{ __('If you have information please fill or click next') }}</span>
            </div>
            


         @include('fixed-assets._content',$project->getFixedAssetsViewVars())
			
			




         



@include('save_buttons')
</form>
</Div>
</div>
<div class="clearfix"></div>
@endsection

@section('js')


<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
<script>
    // Define translated strings for JavaScript
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };
    
	 $(document).ready(function() {
        var selector = "#fixedAssets_repeater";
		
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash',
                'counts': 1,
				'grace_period':0,
				'manufacturing_depreciation_percentage':0,
				'manufacturing_depreciation_percentage':0,
				'admin_depreciation_percentage':0,
				'reservation_rate':0,
				'contractual_rate':0,
				'after_months':0,
				'installment_grace_period':0,
				'installment_count':1,
				'payment_installment_interval':'monthly',
			
				'is_as_revenue_percentages':1,
				'equity_funding_rate':100,
				'replacement_cost_rate':0,
				'depreciation_duration':5,
				'amount':0,
				'tenor':60,
				'interest_rate':0,
				'installment_interval':'monthly',
				'replacement_cost_interval':1
             }
            , show: function() {
                $(this).slideDown();
				$('.js-select2-with-one-selection').select2({});
				$('.hundred-minus-number').trigger('change')
				recalculateAllocations(this);
				$('.hundred-minus-number-one').trigger('change')
				$('.allocate-checkbox').trigger('change')
            }
            , ready: function(setIndexes) {
					
            }
            , hide: function(deleteElement) {
                if (confirm(translations.deleteConfirm)) {
                    $(this).slideUp(deleteElement);


                }

            }
            , isFirstItemUndeletable: true
        });
    });
	
	

</script>


@endsection
