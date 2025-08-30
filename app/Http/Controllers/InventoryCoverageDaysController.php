<?php

namespace App\Http\Controllers;

use App\SalesItems\DurationYears;
use App\Traits\Redirects;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryCoverageDaysController extends Controller
{
    public function index( $project , DurationYears $durationYears){


        $duration_months_in_years = $durationYears->years($project->end_date, $project->start_date,(($project->duration*12)-1),'years');


        $product_value = [];
        $product_value =  $this->salesProductValue($backlog,$sales,$project);
        return $product_value;
    }


    public function salesProductValue($backlog,$sales,$project)
    {
        $product_value = [];
        $products = (new Redirects)->completedProducts($project);
        foreach($products as $product){
            $product_value_per_product = [];
            array_walk($sales,function($value , $date) use($backlog,&$sales,&$product_value_per_product,$product){
                $rate_name = "backlog_rate_".$product ;

                $result =  $value * (@$backlog->$rate_name/100);

                //First Quarter OF year
                $month= date("m",strtotime($date));
                // if ($month == 1 || $month == 2 || $month == 3) {

                //     $value = ($backlog->first_quarter_product_rate/100)*$result;
                // }
                // //Second Quarter OF year
                // if ($month == 4 || $month == 5 || $month == 6) {
                //     $value = ($backlog->second_quarter_product_rate/100) *$result;
                // }
                // //Third Quarter OF year
                // if ($month == 7 || $month == 8 || $month == 9) {
                //     $value = ($backlog->third_quarter_product_rate/100) *$result;
                // }
                // //Fourth Quarter OF year
                // if ($month == 10 || $month == 11 || $month == 12) {
                //     $value = ($backlog->fourth_quarter_product_rate/100) *$result;
                // }
                    $product_value_per_product[$date] =$result;
            });
            array_multisort(array_map('strtotime',array_keys($product_value_per_product)),SORT_ASC,$product_value_per_product);
            $product_value[$product] = $product_value_per_product;
        }

        return $product_value;
    }

    // Average Of the last year Volumes
    public function average($volumes)
    {
        $last_year_avg_sales = array_sum($volumes)/@count($volumes);
        return $last_year_avg_sales ;
    }

    public function customMonth($date,$number_of_added_months)
    {
        return Carbon::parse($date)->addMonths($number_of_added_months)->format('Y-m-d');
    }





    public function inventoryCoverageDaysSales($products_cost,$project)
    {

        $manufactured_rm_value = [];
        $manufactured_labor_value = [];
        $manufactured_moh_value = [];
        $ending_balances = [];
        $total_ending_balances = [];

        foreach ($products_cost as $product_name => $product_cost) {
            $product = $project->product($product_name);
            $years = (new ProjectController)->years($project,null,$product_name);
            $years = call_user_func_array('array_merge', $years);
            $years = array_combine(array_values($years),array_keys($years));


            $fg_inventory_coverage_days = (new DashboardController)->daysToMonthes($product->fg_inventory_coverage_days);

            $begining_balance = $product->fg_inventory_value;
            $last_12_months =  array_slice($product_cost, -12, 12, true);
            $last_year_avg_sales =   $this->average($last_12_months);


            // last date
            $last_date = array_key_last($product_cost);
            foreach ($years as $year => $count) {
                $rm_cost_rate_field = "rm_cost_".$count."_rate";
                $labor_cost_rate_field = "labor_cost_".$count."_rate";
                $moh_cost_rate_field = "moh_cost_".$count."_rate";

                $rm_cost_rate_per_year[$year] = $product->$rm_cost_rate_field ?? 0;
                $labor_cost_rate_per_year[$year] = $product->$labor_cost_rate_field ?? 0;
                $moh_cost_rate_per_year[$year] = $product->$moh_cost_rate_field ?? 0;
            }


            // first month number
            $counter = 0;
            foreach ($product_cost as $date => $value) {

                $one = isset($product_cost[$this->customMonth($date,1)]) ? $product_cost[$this->customMonth($date,1)] : 0 ;

                // if it is the last month
                if (strtotime($last_date) == strtotime($date)) {
                        $store_final_balance = $last_year_avg_sales * $fg_inventory_coverage_days;
                }elseif ($fg_inventory_coverage_days == 0) {
                    $store_final_balance = 0;
                }elseif ($fg_inventory_coverage_days ==  0.25) {
                    $store_final_balance = $one * 0.25;
                }elseif ($fg_inventory_coverage_days ==  0.5) {
                    $store_final_balance = $one * 0.5;
                }elseif ($fg_inventory_coverage_days == 1) {
                    $store_final_balance = $one;
                }elseif ($fg_inventory_coverage_days == 1.5) {
                    $two = isset($product_cost[$this->customMonth($date,2)]) ? ($product_cost[$this->customMonth($date,2)]) : 0 ;
                    $store_final_balance = ($one + ($two * 0.5));
                }elseif ($fg_inventory_coverage_days == 2) {
                    $two = isset($product_cost[$this->customMonth($date,2)]) ? ($product_cost[$this->customMonth($date,2)]) : 0 ;
                    $store_final_balance = $one+$two;
                }elseif ($fg_inventory_coverage_days == 2.5) {
                    $two = isset($product_cost[$this->customMonth($date,2)]) ? ($product_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_cost[$this->customMonth($date,3)]) ? ($product_cost[$this->customMonth($date,3)]) : 0 ;
                    $store_final_balance = $one+$two+($three*0.5);
                }elseif ($fg_inventory_coverage_days == 3) {
                    $two = isset($product_cost[$this->customMonth($date,2)]) ? ($product_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_cost[$this->customMonth($date,3)]) ? ($product_cost[$this->customMonth($date,3)]) : 0 ;
                    $store_final_balance = $one+$two+$three;
                }elseif ($fg_inventory_coverage_days == 4) {
                    $two = isset($product_cost[$this->customMonth($date,2)]) ? ($product_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_cost[$this->customMonth($date,3)]) ? ($product_cost[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($product_cost[$this->customMonth($date,4)]) ? ($product_cost[$this->customMonth($date,4)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four;
                }elseif ($fg_inventory_coverage_days == 5) {
                    $two = isset($product_cost[$this->customMonth($date,2)]) ? ($product_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_cost[$this->customMonth($date,3)]) ? ($product_cost[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($product_cost[$this->customMonth($date,4)]) ? ($product_cost[$this->customMonth($date,4)]) : 0 ;
                    $five = isset($product_cost[$this->customMonth($date,5)]) ? ($product_cost[$this->customMonth($date,5)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four+$five;
                }elseif ($fg_inventory_coverage_days == 6) {
                    $two = isset($product_cost[$this->customMonth($date,2)]) ? ($product_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_cost[$this->customMonth($date,3)]) ? ($product_cost[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($product_cost[$this->customMonth($date,4)]) ? ($product_cost[$this->customMonth($date,4)]) : 0 ;
                    $five = isset($product_cost[$this->customMonth($date,5)]) ? ($product_cost[$this->customMonth($date,5)]) : 0 ;
                    $six = isset($product_cost[$this->customMonth($date,6)]) ? ($product_cost[$this->customMonth($date,6)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four+$five+$six;
                }
                // manufactured_rm_value

                if ($begining_balance == 0 ) {
                    $manufactured_rm_value[$product_name][$date] = (($value) + $store_final_balance) ;
                }elseif (((($value) + $store_final_balance) - $begining_balance) <= 0 ) {
                    $manufactured_rm_value[$product_name][$date] = 0;
                }else{
                    $manufactured_rm_value[$product_name][$date] = ((($value) + $store_final_balance) - $begining_balance) ;
                }


                // Available For Sales
                $available_for_sales = $begining_balance +  $manufactured_rm_value[$product_name][$date];
                // Ending Balance
                $ending_balance = $available_for_sales - ($value) ;
                // Updating the Beginning Balance


                if (isset($product_cost[$this->customMonth($date,1)])) {
                    $begining_balance = $ending_balance;
                }


                $rm_cost_rate =     $rm_cost_rate_per_year[date('Y',strtotime($date))] ?? 0;
                $labor_cost_rate =  $labor_cost_rate_per_year[date('Y',strtotime($date))] ?? 0;
                $moh_cost_rate =    $moh_cost_rate_per_year[date('Y',strtotime($date))] ?? 0;

                $product_cost_rate = (($rm_cost_rate/100)+($labor_cost_rate/100)+($moh_cost_rate/100));

                $manufactured_labor_value[$date] = $product_cost_rate == 0 ? 0 : (($manufactured_labor_value[$date]??0) + ($manufactured_rm_value[$product_name][$date]*(($labor_cost_rate/100)/$product_cost_rate)));



                $manufactured_moh_value[$date] = $product_cost_rate == 0 ? 0 : (($manufactured_moh_value[$date]??0) + ( $manufactured_rm_value[$product_name][$date]*(($moh_cost_rate/100)/$product_cost_rate)));

                $manufactured_rm_value[$product_name][$date] = $product_cost_rate == 0 ? 0 : $manufactured_rm_value[$product_name][$date]*(($rm_cost_rate/100)/$product_cost_rate);

                $rm_cost[$date] = $value * ($rm_cost_rate/100);
                $labor_cost[$date] = $value * ($labor_cost_rate/100);
                $moh_cost[$date] = $value * ($moh_cost_rate/100);

                $ending_balances[$date] = $ending_balance;
                $total_ending_balances[$date] = isset($total_ending_balances[$date]) ? $total_ending_balances[$date] + $ending_balance : $ending_balance;
                $counter++;
            }
        }


        return [
            'manufactured_labor_value' =>$manufactured_labor_value,
            'manufactured_moh_value' =>$manufactured_moh_value,
            'manufactured_rm_value' =>$manufactured_rm_value,
            'ending_balances'       =>$total_ending_balances
        ];
    }





















    public function inventoryCoverageDaysRm($manufacturing_rm_values,$project)
    {

        $rm_purchases = [];
        $ending_balances = [];

        foreach ($manufacturing_rm_values as $product_name => $manufacturing_rm_value) {
            $product = $project->product($product_name);

            $inventory_coverage_days = (new DashboardController)->daysToMonthes($product->inventory_coverage_days);

            $begining_balance = $product->rm_inventory_value;
            $last_12_months =  array_slice($manufacturing_rm_value, -12, 12, true);
            $last_year_avg_sales =   $this->average($last_12_months);


            // last date
            $last_date = array_key_last($manufacturing_rm_value);

            // first month number
            $counter = 0;

            foreach ($manufacturing_rm_value as $date => $value) {

                // year
                $year = date('Y',strtotime($date));


                $one = isset($manufacturing_rm_value[$this->customMonth($date,1)]) ? $manufacturing_rm_value[$this->customMonth($date,1)] : 0 ;

                // if it is the last month
                if (strtotime($last_date) == strtotime($date)) {
                        $store_final_balance = $last_year_avg_sales * $inventory_coverage_days;
                }elseif ($inventory_coverage_days == 0) {
                    $store_final_balance = 0;
                }elseif ($inventory_coverage_days ==  0.25) {
                    $store_final_balance = $one * 0.25;
                }elseif ($inventory_coverage_days ==  0.5) {
                    $store_final_balance = $one * 0.5;
                }elseif ($inventory_coverage_days == 1) {
                    $store_final_balance = $one;
                }elseif ($inventory_coverage_days == 1.5) {
                    $two = isset($manufacturing_rm_value[$this->customMonth($date,2)]) ? ($manufacturing_rm_value[$this->customMonth($date,2)]) : 0 ;
                    $store_final_balance = ($one + ($two * 0.5));
                }elseif ($inventory_coverage_days == 2) {
                    $two = isset($manufacturing_rm_value[$this->customMonth($date,2)]) ? ($manufacturing_rm_value[$this->customMonth($date,2)]) : 0 ;
                    $store_final_balance = $one+$two;
                }elseif ($inventory_coverage_days == 2.5) {
                    $two = isset($manufacturing_rm_value[$this->customMonth($date,2)]) ? ($manufacturing_rm_value[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($manufacturing_rm_value[$this->customMonth($date,3)]) ? ($manufacturing_rm_value[$this->customMonth($date,3)]) : 0 ;
                    $store_final_balance = $one+$two+($three*0.5);
                }elseif ($inventory_coverage_days == 3) {
                    $two = isset($manufacturing_rm_value[$this->customMonth($date,2)]) ? ($manufacturing_rm_value[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($manufacturing_rm_value[$this->customMonth($date,3)]) ? ($manufacturing_rm_value[$this->customMonth($date,3)]) : 0 ;
                    $store_final_balance = $one+$two+$three;
                }elseif ($inventory_coverage_days == 4) {
                    $two = isset($manufacturing_rm_value[$this->customMonth($date,2)]) ? ($manufacturing_rm_value[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($manufacturing_rm_value[$this->customMonth($date,3)]) ? ($manufacturing_rm_value[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($manufacturing_rm_value[$this->customMonth($date,4)]) ? ($manufacturing_rm_value[$this->customMonth($date,4)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four;
                }elseif ($inventory_coverage_days == 5) {
                    $two = isset($manufacturing_rm_value[$this->customMonth($date,2)]) ? ($manufacturing_rm_value[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($manufacturing_rm_value[$this->customMonth($date,3)]) ? ($manufacturing_rm_value[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($manufacturing_rm_value[$this->customMonth($date,4)]) ? ($manufacturing_rm_value[$this->customMonth($date,4)]) : 0 ;
                    $five = isset($manufacturing_rm_value[$this->customMonth($date,5)]) ? ($manufacturing_rm_value[$this->customMonth($date,5)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four+$five;
                }elseif ($inventory_coverage_days == 6) {
                    $two = isset($manufacturing_rm_value[$this->customMonth($date,2)]) ? ($manufacturing_rm_value[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($manufacturing_rm_value[$this->customMonth($date,3)]) ? ($manufacturing_rm_value[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($manufacturing_rm_value[$this->customMonth($date,4)]) ? ($manufacturing_rm_value[$this->customMonth($date,4)]) : 0 ;
                    $five = isset($manufacturing_rm_value[$this->customMonth($date,5)]) ? ($manufacturing_rm_value[$this->customMonth($date,5)]) : 0 ;
                    $six = isset($manufacturing_rm_value[$this->customMonth($date,6)]) ? ($manufacturing_rm_value[$this->customMonth($date,6)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four+$five+$six;
                }

                // rm_Purchases

                if ($begining_balance == 0 ) {
                    $rm_purchases[$product_name][$date] = (($value) + $store_final_balance) ;
                }elseif (((($value) + $store_final_balance) - $begining_balance) <= 0 ) {
                    $rm_purchases[$product_name][$date] = 0;
                }else{
                    $rm_purchases[$product_name][$date] = ((($value) + $store_final_balance) - $begining_balance) ;
                }


                // Available For Sales
                $available_for_sales = $begining_balance +  $rm_purchases[$product_name][$date];
                // Ending Balance
                $ending_balance = $available_for_sales - ($value) ;
                // Updating the Beginning Balance


                if (isset($manufacturing_rm_value[$this->customMonth($date,1)])) {
                    $begining_balance = $ending_balance;
                }
                $rm_purchases[$product_name][$date] = $rm_purchases[$product_name][$date];
                $ending_balances[$date] = $ending_balance;
                $total_ending_balances[$date] = isset($total_ending_balances[$date]) ? $total_ending_balances[$date] + $ending_balance : $ending_balance;
                $counter++;
            }



        }

    return ['ending_balance'=>($total_ending_balances??[]),'rm_purchases'=>$rm_purchases];

    }
}
