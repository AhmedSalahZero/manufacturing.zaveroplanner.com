<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ValidationsController extends Controller
{
    // Products Validation
    public function productsValidation($request,$project,$type)
    {
         if($request->submit_button == "next" ){

            $validation['seasonality'] = 'required';
            $validation['rm_cost_rate'] = 'nullable|numeric|min:0';
            $validation['labor_cost_rate'] = 'nullable|numeric|min:0';
            $validation['moh_cost_rate'] = 'nullable|numeric|min:0';

            $years = (new ProjectController)->years($project,null,$type);

            foreach ($years as $key => $year) {
                $name_of_year = array_key_first($year);

                $rm_cost_rate_field = 'rm_cost_'.$name_of_year.'_rate' ;
                $labor_cost_rate_field = 'labor_cost_'.$name_of_year.'_rate' ;
                $moh_cost_rate_field = 'moh_cost_'.$name_of_year.'_rate' ;
                $validation[$rm_cost_rate_field] = 'numeric|min:0';
                $validation[$labor_cost_rate_field] = 'numeric|min:0';
                $validation[$moh_cost_rate_field] = 'numeric|min:0';
            }




            $validation['first_contract'] = 'required|numeric|min:0';
            $validation['inventory_coverage_days'] = 'required';
            $validation['fg_inventory_coverage_days'] = 'required';
            $validation['collection_down_payment'] = 'required|numeric|min:0';
            $validation['final_collection_days'] = $request->final_collection_rate > 0 ? 'required' : '';
            $validation['initial_collection_rate'] = 'nullable|numeric|min:0';
            $validation['initial_collection_days'] = $request->initial_collection_rate > 0 ? 'required' : '';
            $validation['outsourcing_down_payment'] = $request->rm_cost_rate > 0 ? 'required|numeric|min:0' : 'nullable|numeric|min:0';
            $validation['balance_rate_one'] = $request->outsource_cost_rate > 0 ? 'required' : '';
            $validation['balance_one_due_in'] = $request->outsource_cost_rate > 0 ? 'required' : '';

            $validation['product_cost_rate'] = $request->rm_cost_rate+$request->labor_cost_rate+$request->moh_cost_rate < 100 ? '':'required';
            $validation['fg_inventory_value'] = 'nullable|numeric|min:0';
            $validation['rm_inventory_value'] = 'nullable|numeric|min:0';
            $validation = $this->totalPercentages($request,$validation);

            $this->validate($request,@$validation,[
                'quarterly_total_percentage.required' => __('Total Percentages Must Equal 100%'),
                'monthly_total_percentage.required' => __('Total Percentages Must Equal 100%'),
                'product_cost_rate.required' => __('Product costs rate percentages must be less than 100%'),
            ]);
        }elseif ($request->submit_button == "save") {
            return ['redirect_route' => redirect()->route('main.project.page',[$project->id])];
        }

    }
    public function backlogValidation($request,$data)
    {
        if($request->submit_button == "next" ){
            if (isset($request->first_contract) && $request->first_contract !=0) {
                $total_backlog_rate = $request->backlog_rate_product_first + $request->backlog_rate_product_second  + $request->backlog_rate_product_third + $request->backlog_rate_product_fourth+ $request->backlog_rate_product_fifth;
                $total_excution_rate = $request->first_quarter_excution_rate +$request->second_quarter_excution_rate +$request->third_quarter_excution_rate +$request->fourth_quarter_excution_rate;
                // $total_product_rate = $request->first_quarter_product_rate + $request->second_quarter_product_rate + $request->third_quarter_product_rate +$request->fourth_quarter_product_rate;

                $validation['backlog_rate'] = $total_backlog_rate != 100 ? 'required' : '';
                // $validation['product_rate'] = $total_excution_rate != 100 ? 'required' : '';
                $validation['excution_rate'] = $total_excution_rate != 100 ? 'required' : '';

                $this->validate($request,@$validation,[
                    'quarterly_total_percentage.required' => __('Total Percentages Must Equal 100%'),
                    'backlog_rate.required' => __('Total Backlog Rate Percentages Must Equal 100%'),
                    'excution_rate.required' => __('Total Execution Rate Percentages Must Equal 100%'),
                ]);
            }
        }elseif ($request->submit_button == "save") {
            return ['redirect_route' => redirect()->route('main.project.page',[$data->id])];
        }
    }
    //Total Percentages
    public function totalPercentages($request,$validation,$result=null)
    {
        $total = 0;
        if ($request->seasonality == "flat") {
       //     $validation['seasonality_constant'] = 'required';
            $total =100;
        }elseif ($request->seasonality == "quarterly") {
            $total = $request->first_quarter + $request->second_quarter + $request->third_quarter + $request->fourth_quarter;
            $validation['quarterly_total_percentage'] = $total != 100 ? 'required' : '' ;
        }elseif ($request->seasonality == "monthly") {
            $total = 0;
            $month_num = date('d-m-y',strtotime('01-01-2020'));
            for ($month = 0; $month <  12; $month++){
                $month_name =  date("F",strtotime(date("d-m-y", strtotime($month_num)) . " +$month month"));
                $name = "monthly_".strtolower($month_name);
                $total += $request->$name;
            }
            $validation['monthly_total_percentage'] = $total != 100 ? 'required' : '' ;
        }
        if ($result != null && $result == "total_percentage") {
            return $total;
        }
        return $validation;
    }

    //Man Power
    public function manPowerValidation($request,$data)
    {
        if($request->submit_button == "next" ){
            $validation = [];
            $this->validate($request,@$validation,[
            ]);
      }elseif ($request->submit_button == "save") {
        return ['redirect_route' => redirect()->route('main.project.page',[$data->id])];
      }
    }
    //Expenses Validation
    public function expensesValidation($request,$data)
    {
        if($request->submit_button == "next" ){
            $validation = [];
            // $validation['marketing_campain'] = "required" ;
            // $validation['monthly_general_expenses'] = "required" ;
            $this->validate($request,@$validation,[
            ]);
      }elseif ($request->submit_button == "save") {
        return ['redirect_route' => redirect()->route('main.project.page',[$data->id])];
      }
    }
    //Assets Validation
    public function assetsValidation($request,$data)
    {
        if($request->submit_button == "next" ){
            if($request->fixed_assets_value > 0 && $request->fixed_assets_value !== null && (( $request->down_payment  + $request->balance_rate) != 100 )){
                $validation['down_payment'] = "required|numeric|min:0" ;
            }
            if($request->fixed_assets_value_two > 0 && $request->fixed_assets_value_two !== null && (( $request->down_payment_two  + $request->balance_rate_two) != 100)  ){
                $validation['down_payment_two'] = "required|numeric|min:0" ;
            }
            $validation['fixed_assets_value'] = "nullable|numeric|min:0" ;
            $validation['fixed_assets_value_two'] = "nullable|numeric|min:0" ;
            $this->validate($request,@$validation,[
            ]);
      }elseif ($request->submit_button == "save") {
        return ['redirect_route' => redirect()->route('main.project.page',[$data->id])];
      }
    }

    //Opening Balances Post
    public function openingBalancesPostValidation($request,$data)
    {
        if($request->submit_button == "next" ){
            $validation = [];
            $validation['total_assets'] = "required|numeric|min:1" ;
            $validation['owners_equity'] = "required|numeric|min:1" ;

            $this->validate($request,@$validation,[
            ]);
      }elseif ($request->submit_button == "save") {
        return ['redirect_route' => redirect()->route('main.project.page',[$data->id])];
      }
    }

}
