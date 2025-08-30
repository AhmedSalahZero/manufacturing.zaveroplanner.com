<?php
namespace App\Http\Controllers;
    class BalanceSheet
    {
        public function endBalance($dates,$data,$collection)
        {
            $end_balance =[];
            $beginning_balance = 0;
            foreach ($dates as $date) {
                $due_amount =($data[$date]??0) + $beginning_balance ;
                $end_balance[$date] = $due_amount - ($collection[$date]??0);

                $beginning_balance = $end_balance[$date];
            }
            return $end_balance;
        }
        public function endOpeningInterestBalance($dates,$opening_interest_cost,$opening_interest_payment)
        {
            $end_balance =[];
            $beginning_balance = 0;
            foreach ($dates as $date) {
                $due_amount =($opening_interest_cost[$date]??0) + $beginning_balance ;
                $end_balance[$date] = (($due_amount) - ($opening_interest_payment[$date]??0));
                $end_balance[$date] = $end_balance[$date]> 0 ? $end_balance[$date] : (-1*$end_balance[$date]);
                $beginning_balance = $end_balance[$date];
            }
            return $end_balance;
        }
        public function fixedAssetsEndBalance($dates,$collection,$beginning_balance)
        {
            $end_balance =[];
            foreach ($dates as $date) {
                // $due_amount = ($beginning_balance[$date]??0) ;
                $end_balance[$date] =($beginning_balance[$date]??0)  - ($collection[$date]??0);
            }
            return $end_balance;
        }
        public function sameNumberThroughInterval($dates,$number)
        {
            $result =[];

            foreach ($dates as $date) {
                $result[$date] =  $number;

                // $beginning_balance = $result[$date];
            }
            return $result;
        }
        public function sumNumberThroughInterval($array,$number)
        {
            $result =[];

            foreach ($array as $date => $amount) {
                $result[$date] = $amount + $number;

                // $beginning_balance = $result[$date];
            }
            return $result;
        }
        public function accumulation($dates,$array)
        {
            $accumulated_value = 0;
            $result = [];
            foreach ($dates as $date) {
                $accumulated_value = $accumulated_value + ($array[$date]??0);
                $result[$date] = $accumulated_value;
            }
            return $result;
        }

        public function additionalCapital($dates,$array)
        {
            $compared_value = 0;
            $result = [];
            foreach ($dates as $date) {
                $value =  -1*($array[$date]??0);
                $compared_value = $value > $compared_value ? $value : $compared_value;

                $result[$date] = $compared_value;
            }
            return $result;
        }
        public function retainedEarning($dates,$net_profit)
        {
            $value = 0;
            $first_date = array_key_first($net_profit);
            $previous_value = $net_profit[$first_date];

            $result = [];
            foreach ($dates as $date) {
                if ($first_date == $date) {
                    $result[$date] = 0 ;
                }elseif ($first_date !== $date) {

                    $result[$date] = $previous_value;
                    $previous_value = $previous_value+ ($net_profit[$date]??0) ;
                }

            }
            return $result;
        }
            //ebitda
        public function cashBanks($dates,$balance_sheet_total_assets_without_cash,$balance_sheet_total_current_liabilities,$balance_sheet_total_long_liabilities,$balance_sheet_total_owners_equity)
        {
            $array = [];

            array_walk($dates, function ($date) use (&$array, $balance_sheet_total_assets_without_cash, $balance_sheet_total_current_liabilities, $balance_sheet_total_long_liabilities, $balance_sheet_total_owners_equity) {
                $result =   @$balance_sheet_total_owners_equity[$date]
                    + (@$balance_sheet_total_current_liabilities[$date])
                    + (@$balance_sheet_total_long_liabilities[$date])
                    - (@$balance_sheet_total_assets_without_cash[$date]);

                $array[$date] = $result;
            });
            return  $array;
        }
        public function average($opening,$array)
        {
            $previous = $opening;
            $result = [];
            unset($array['Total']);

            foreach ($array as $date => $value) {
                $current_avg = ($value+$previous)/2;
                if (str_contains($date,"Q")  ) {
                    if ($date =="Q1") {
                        $result[$date] = $current_avg*90;
                    }elseif ($date =="Q2") {
                        $result[$date] = $current_avg*180;
                    }elseif ($date =="Q3") {
                        $result[$date] = $current_avg*270;
                    }elseif ($date =="Q4") {
                        $result[$date] = $current_avg*365;
                    }
                    $result['Total'] = ($result['Total']??0) + $result[$date];
                } else {
                    $result[$date] = $current_avg*365;

                }
                $previous = $value;

            }
            isset($array['Total']) ? $result['Total'] = array_sum($result) : '';

            return $result;
        }
        function changeInWc($previous_value=0 , $array=[] , $type_of_calculation = 'previous' ){
            $result = [];
            if ($type_of_calculation == 'previous' ) {

                foreach ($array as $date => $value) {

                    $year = date("Y",strtotime($date));
                    $result[$year] = $previous_value - $value ;
                    $previous_value = $value;
                }
            }else{
                foreach ($array as $date => $value) {
                    $year = date("Y",strtotime($date));
                    $result[$year] =   $value - $previous_value ;
                    $previous_value = $value;
                }
            }
            return $result;
        }
    }
