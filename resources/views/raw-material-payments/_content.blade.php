<div class="div-title">
                {{ __('Raw Material Payments') }}
            </div>

            <div class="formItem">
                <div class="row ml-1 mr-1 closest-parent ">

                    <div class="col-12">


                        @php
                        $isYearsStudy = false;
                        $tableClass = $isYearsStudy ? 'col-md-6 closest-parent' : 'col-md-12 closest-parent';
                        @endphp


                        <x-tables.repeater-table :scrollable="false" :table-class="$tableClass" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="''" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="aboveThsTrs">
                                <tr>
                                    <x-tables.repeater-table-th rowspan="2" class="  header-border-down max-w-15" :title="__('Name')"></x-tables.repeater-table-th>
                                    <x-tables.repeater-table-th rowspan="2" class="  header-border-down max-w-5" :title="__('Beginning <br> Inventory Value')"></x-tables.repeater-table-th>
                                    @foreach($project->getFirstYearUntilLast(0) as $index=>$title)
                                    <x-tables.repeater-table-th colspan="5" class="  header-border-down max-w-15" :title="$title"></x-tables.repeater-table-th>
                                    @endforeach

                                </tr>
                            </x-slot>
                            <x-slot name="ths">

                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Cash %')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Payment <br> Rate-1 %')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Due <br> Days-1')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Payment <br> Rate-2 %')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Due <br> Days-2')"></x-tables.repeater-table-th>

                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Cash %')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Payment <br> Rate-1 %')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Due <br> Days-1')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Payment <br> Rate-2 %')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down max-w-5 font-weight-normal" :title="__('Due <br> Days-2')"></x-tables.repeater-table-th>


                            </x-slot>
                            <x-slot name="trs">
                                @foreach($uniqueDistributedRawMaterialNames as $rawMaterial)
                                @php
                                $rawMaterialId = $rawMaterial->id;
                                $rawMaterialName = $rawMaterial->name;
                                @endphp
                                <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                    <td>
                                        <input name="rawMaterials[{{ $rawMaterialId }}][id]" value="{{  $rawMaterialName }}" disabled class="form-control text-left " type="text">
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input 
											@if($project->isNewCompany())
											disabled
											@endif 
											class="form-control" type="text" name="rawMaterials[{{ $rawMaterialId }}][beginning_inventory_value]" value="{{ $rawMaterial->beginning_inventory_value }}">
                                        </div>
                                    </td>

                                    @foreach($project->getFirstYearUntilLast(0) as $index=>$title)
                                    @php


                                    $currentFieldName = "rawMaterials[$rawMaterialId][collection_policy_value][$index][cash_payment]";
                                    $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                                    $currentVal = $rawMaterial->getCollectionDownPaymentAtIndex($index,null);

                                    @endphp

                                    <td>

                                        <input type="number" step="any" class="form-control @error($nameToOld) is-invalid @enderror" value="{{$currentVal}}" name="{{ $currentFieldName }}" id="{{ $currentFieldName }}">





                                    </td>

                                    @for($numberOfCollections = 0 ; $numberOfCollections<= 1 ; $numberOfCollections++) <td>

                                        @php
                                        $currentFieldName = "rawMaterials[$rawMaterialId][collection_policy_value][$index][rate][$numberOfCollections]";
                                        $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                                        $currentVal = $rawMaterial->getCollectionRateAtIndex($index,$numberOfCollections,null);
                                        @endphp
                                        <input type="number" step="any" class="form-control @error($nameToOld) is-invalid @enderror" value="{{$currentVal}}" name="{{ $currentFieldName }}" id="{{ $nameToOld }}">


                                        </td>
                                        @php
                                        $currentFieldName = "rawMaterials[$rawMaterialId][collection_policy_value][$index][due_in_days][$numberOfCollections]";
                                        $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                                        $currentVal =$rawMaterial->getCollectionDueDaysAtIndex($index,$numberOfCollections,null);
                                        @endphp

                                        <td>
                                            <select class="form-control  @error($nameToOld) is-invalid @enderror" name="{{ $currentFieldName }}" id="{{ $nameToOld }}">
                                                @foreach(collectionDueDays() as $dueDay => $formattedDueDay )
                                                <option value="{{ $dueDay }}" {{$currentVal == $dueDay  ?'selected' : '' }}>{{ $formattedDueDay }}</option>
                                                @endforeach
                                            </select>

                                        </td>

                                        @endfor


                                        @endforeach

                                </tr>

                                @endforeach


                            </x-slot>



                        </x-tables.repeater-table>


                    </div>



                </div>
                {{-- Selling Start Date --}}

                {{-- Contract Target --}}

            </div>

            <div class="div-title toggle-show-hide" data-query=".comment-for-raw-materials">
                {{__('Insert Comment For Raw Materials')}}
            </div>
            <div class="formItem comment-for-raw-materials">
                <div class="row justify-content-center">

                    <div class="col-md-11">
                        <div class="form-group ">
                            <textarea data-is-ck-editor name="comment_for_raw_materials">{!! $project->comment_for_raw_materials !!}</textarea>
                        </div>
                    </div>

                </div>

            </div>
