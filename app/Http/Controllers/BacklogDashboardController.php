<?php

namespace App\Http\Controllers;

use App\SalesItems\DurationYears;
use App\Traits\Redirects;
use Carbon\Carbon;
use Illuminate\Http\Request;


class BacklogDashboardController extends Controller
{
    public function index( $project , DurationYears $durationYears){
        $backlog = $project->backlog;

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
    public function inventoryCoverageDaysValues($product_purchase_cost,$project)
    {

        $purchases = [];
        $ending_balances = [];

        foreach ($product_purchase_cost as $product_name => $product_purchase_cost) {
            $product = $project->product($product_name);

            $inventory_coverage_days = (new DashboardController)->daysToMonthes($product->inventory_coverage_days);

            $begining_balance = $product->rm_inventory_value;
            $last_12_months =  array_slice($product_purchase_cost, -12, 12, true);
            $last_year_avg_sales =   $this->average($last_12_months);


            // last date
            $last_date = array_key_last($product_purchase_cost);

            // first month number
            $counter = 0;

            foreach ($product_purchase_cost as $date => $value) {

                // year
                $year = date('Y',strtotime($date));


                $one = isset($product_purchase_cost[$this->customMonth($date,1)]) ? $product_purchase_cost[$this->customMonth($date,1)] : 0 ;

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
                    $two = isset($product_purchase_cost[$this->customMonth($date,2)]) ? ($product_purchase_cost[$this->customMonth($date,2)]) : 0 ;
                    $store_final_balance = ($one + ($two * 0.5));
                }elseif ($inventory_coverage_days == 2) {
                    $two = isset($product_purchase_cost[$this->customMonth($date,2)]) ? ($product_purchase_cost[$this->customMonth($date,2)]) : 0 ;
                    $store_final_balance = $one+$two;
                }elseif ($inventory_coverage_days == 2.5) {
                    $two = isset($product_purchase_cost[$this->customMonth($date,2)]) ? ($product_purchase_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_purchase_cost[$this->customMonth($date,3)]) ? ($product_purchase_cost[$this->customMonth($date,3)]) : 0 ;
                    $store_final_balance = $one+$two+($three*0.5);
                }elseif ($inventory_coverage_days == 3) {
                    $two = isset($product_purchase_cost[$this->customMonth($date,2)]) ? ($product_purchase_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_purchase_cost[$this->customMonth($date,3)]) ? ($product_purchase_cost[$this->customMonth($date,3)]) : 0 ;
                    $store_final_balance = $one+$two+$three;
                }elseif ($inventory_coverage_days == 4) {
                    $two = isset($product_purchase_cost[$this->customMonth($date,2)]) ? ($product_purchase_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_purchase_cost[$this->customMonth($date,3)]) ? ($product_purchase_cost[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($product_purchase_cost[$this->customMonth($date,4)]) ? ($product_purchase_cost[$this->customMonth($date,4)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four;
                }elseif ($inventory_coverage_days == 5) {
                    $two = isset($product_purchase_cost[$this->customMonth($date,2)]) ? ($product_purchase_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_purchase_cost[$this->customMonth($date,3)]) ? ($product_purchase_cost[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($product_purchase_cost[$this->customMonth($date,4)]) ? ($product_purchase_cost[$this->customMonth($date,4)]) : 0 ;
                    $five = isset($product_purchase_cost[$this->customMonth($date,5)]) ? ($product_purchase_cost[$this->customMonth($date,5)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four+$five;
                }elseif ($inventory_coverage_days == 6) {
                    $two = isset($product_purchase_cost[$this->customMonth($date,2)]) ? ($product_purchase_cost[$this->customMonth($date,2)]) : 0 ;
                    $three = isset($product_purchase_cost[$this->customMonth($date,3)]) ? ($product_purchase_cost[$this->customMonth($date,3)]) : 0 ;
                    $four = isset($product_purchase_cost[$this->customMonth($date,4)]) ? ($product_purchase_cost[$this->customMonth($date,4)]) : 0 ;
                    $five = isset($product_purchase_cost[$this->customMonth($date,5)]) ? ($product_purchase_cost[$this->customMonth($date,5)]) : 0 ;
                    $six = isset($product_purchase_cost[$this->customMonth($date,6)]) ? ($product_purchase_cost[$this->customMonth($date,6)]) : 0 ;
                    $store_final_balance = $one+$two+$three+$four+$five+$six;
                }

                // Purchases

                if ($begining_balance == 0 ) {
                    $purchases[$product_name][$date] = (($value) + $store_final_balance) ;
                }elseif (((($value) + $store_final_balance) - $begining_balance) <= 0 ) {
                    $purchases[$product_name][$date] = 0;
                }else{
                    $purchases[$product_name][$date] = ((($value) + $store_final_balance) - $begining_balance) ;
                }


                // Available For Sales
                $available_for_sales = $begining_balance +  $purchases[$product_name][$date];
                // Ending Balance
                $ending_balance = $available_for_sales - ($value) ;
                // Updating the Beginning Balance


                if (isset($product_purchase_cost[$this->customMonth($date,1)])) {
                    $begining_balance = $ending_balance;
                }
                $purchases[$product_name][$date] = $purchases[$product_name][$date];
                $ending_balances[$date] = $ending_balance;
                $counter++;
            }



        }

        return $purchases;

    }

}
