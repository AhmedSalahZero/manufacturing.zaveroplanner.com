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
    <div class="ProjectList">

        <form action="{{ route('expenses.submit', $project) }}" method="POST">
            {{ csrf_field() }}
            {{-- <div class="col-12 alert alert-info text-center">
                <span class="red">{{ __('If you have information please fill or click next') }}</span>
            </div> --}}
            @include('expenses._content',$project->getExpensesViewVars())

            @include('save_buttons')
        </form>
    </div>
</div>
<div class="clearfix"></div>
@endsection

@section('js')


<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
<script>
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };

</script>
<script>
 function initMultiselect(container) {
        const $container = $(container);
        const $trigger = $container.find('.multiselect-trigger');
        const $dropdown = $container.find('.multiselect-dropdown');
        const $searchInput = $container.find('.search-input');
        const $addOptionInput = $container.find('.add-option-input');
        const $addOptionBtn = $container.find('.btn-add-option');
        const $selectAllBtn = $container.find('.btn-select-all');
        const $deselectAllBtn = $container.find('.btn-deselect-all');
        const $optionsContainer = $container.find('.multiselect-options');
        const $selectedText = $container.find('.selected-text');
        const $selectedOptionsContainer = $container.find('.selected-options-container');
        let selectedValues = [];

        // Toggle dropdown
        $trigger.on('click', function(e) {
          e.stopPropagation();
          $dropdown.toggle();
        });

        // Close on outside click
        $(document).on('click', function(e) {
          if (!$container.has(e.target).length) {
            $dropdown.hide();
          }
        });

        // Bind checkbox events
        function bindCheckboxEvents($checkbox) {
          $checkbox.on('change', updateSelected);
        }

        // Update selected values and display
        function updateSelected() {
          const $options = $optionsContainer.find('.option-item input[type="checkbox"]');
          selectedValues = $options.filter(':checked').map(function() { return $(this).val(); }).get();
          $selectedText.text(selectedValues.length ? `${selectedValues.length} selected` : 'Select options...');
          
          // Clear existing hidden inputs
          $selectedOptionsContainer.empty();
          // Add a hidden input for each selected value
          selectedValues.forEach(function(value) {
            $selectedOptionsContainer.append(
              `<input type="hidden" name="selectedOptions[]" value="${value}">`
            );
          });
        }

        // Bind initial checkboxes
        $optionsContainer.find('.option-item input[type="checkbox"]').each(function() {
          bindCheckboxEvents($(this));
        });

        // Select All
        $selectAllBtn.on('click', function(e) {
          e.preventDefault();
          $optionsContainer.find('.option-item input[type="checkbox"]').prop('checked', true);
          updateSelected();
        });

        // Deselect All
        $deselectAllBtn.on('click', function(e) {
          e.preventDefault();
          $optionsContainer.find('.option-item input[type="checkbox"]').prop('checked', false);
          updateSelected();
        });

        // Search filter
        $searchInput.on('input', function() {
          const query = $(this).val().toLowerCase();
          $optionsContainer.find('.option-item').each(function() {
            const label = $(this).text().toLowerCase();
            $(this).toggle(label.includes(query));
          });
        });

        // Add new option
        $addOptionBtn.on('click', function(e) {
          e.preventDefault();
          const newOptionText = $addOptionInput.val().trim();
          if (newOptionText) {
            const newValue = `option${Date.now()}`;
            const $newOption = $(`<label class="option-item"><input type="checkbox" value="${newValue}"> ${newOptionText}</label>`);
            $optionsContainer.append($newOption);
            bindCheckboxEvents($newOption.find('input[type="checkbox"]'));
            $addOptionInput.val('');
            updateSelected();
          }
        });

        // Add option with Enter key
        $addOptionInput.on('keypress', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            $addOptionBtn.click();
          }
        });

        updateSelected(); // Initial call
      }
	  
</script>
@foreach(getExpensesTypes() as $expenseType)
<script>
   

    $(document).ready(function() {


        var selector = "#{{ $expenseType.'_repeater' }}";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash',
				'is_as_revenue_percentages':1,
            }
            , show: function() {
                $(this).slideDown();
                $('.js-select2-with-one-selection').select2({});
                recalculateAllocations(this);
                initMultiselect($(this));
				
				
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
@endforeach
<script>
    $('.repeater_item').each(function() {
	      initMultiselect($(this));
    });
</script>
@endsection
