<?php

namespace App\Http\Controllers;

use App\Project;
use App\SalesItems\DurationYears;
use Illuminate\Http\Request;

class OpeningBalanceDashboardController extends Controller
{
    public function openingBalanceData($project , DurationYears $durationYears,$openingBalance,$years)
    {

        // Opening customers Collection
        $opening_customers_collection = $this->openingCollectionAndPayment(@$openingBalance->checks_balance,@$openingBalance->collection_rate_a,@$openingBalance->Collected_days_a,@$openingBalance->collection_rate_b,@$openingBalance->Collected_days_b,@$openingBalance->collection_rate_c,@$openingBalance->Collected_days_c,$project->start_date);
        // Opening debtors Collection
        $opening_debtors_collection = $this->openingCollectionAndPayment(@$openingBalance->other_bebtors_balance,@$openingBalance->assets_settlment_rate_a,@$openingBalance->assets_settled_within_days_a,@$openingBalance->assets_settlment_rate_b,@$openingBalance->assets_settled_within_days_b,@$openingBalance->assets_settlment_rate_c,@$openingBalance->assets_settled_within_days_c,$project->start_date);
        // Opening suppliers Payment
        $opening_suppliers_payment = $this->openingCollectionAndPayment(@$openingBalance->suppliers_checks_balance,@$openingBalance->payment_rate_a,@$openingBalance->paid_within_a,@$openingBalance->payment_rate_b,@$openingBalance->paid_within_b,@$openingBalance->payment_rate_c,@$openingBalance->paid_within_c,$project->start_date);
        // Opening creditors Payment
        $opening_creditors_payment = $this->openingCollectionAndPayment(@$openingBalance->other_creditors_balance,@$openingBalance->liabilities_settlment_rate_a,@$openingBalance->liabilities_settled_within_days_a,@$openingBalance->liabilities_settlment_rate_b,@$openingBalance->liabilities_settled_within_days_b,@$openingBalance->liabilities_settlment_rate_c,@$openingBalance->liabilities_settled_within_days_c,$project->start_date);

        $duration_months_in_years = $durationYears->years($project->end_date,$project->start_date,(($project->duration*12)-1),'years');
        //Opeanning Loan Installment
        $opeanning_loan_installment = $this->opeanningLoanInstallment($duration_months_in_years,$openingBalance,$years);
        //Opeanning Loan Installment Payment
        $opeanning_loan_installment_payment = @$openingBalance->installment_interval == 'monthly' ? $opeanning_loan_installment : (new ExpensesDashboardController)->expensePayment($opeanning_loan_installment,$project->start_date);
        //Other Long Liabilities
        $other_long_liabilities = $this->otherLongLiabilities($duration_months_in_years,$openingBalance,$years);
        //cash banks balance
        $cash_banks_balance = [date('01-m-Y',strtotime($project->start_date))=>@$openingBalance->cash_banks_balance];
        $owners_equity = @$openingBalance->owners_equity;
        return [
            'opening_customers_collection' => $opening_customers_collection,
            'opening_debtors_collection' => $opening_debtors_collection,
            'opening_suppliers_payment' => $opening_suppliers_payment,
            'opening_creditors_payment' => $opening_creditors_payment,
            'opeanning_loan_installment' => $opeanning_loan_installment,
            'opeanning_loan_installment_payment' => $opeanning_loan_installment_payment,
            'other_long_liabilities' => $other_long_liabilities,
            'cash_banks_balance' =>$cash_banks_balance,
            'owners_equity'=>$owners_equity,
        ];
    }
    public function daysToMonthes($days)
    {
        $coverage_months = 0;
       if ($days == 30) {
            $coverage_months = 0;
        }elseif ($days == 60 || $days == 45) {
            $coverage_months = 1;
        }elseif ($days == 90 || $days == 75) {
            $coverage_months = 2;
        }elseif ($days == 120) {
            $coverage_months = 3;
        }elseif ($days == 150) {
            $coverage_months = 4;
        }elseif ($days == 180) {
            $coverage_months = 5;
        }
        return $coverage_months;
    }
    //Opening Customers Collection
    public function openingCollectionAndPayment($openingBalance,$rate_a,$days_a,$rate_b,$days_b,$rate_c,$days_c,$start_date)
    {
        $final_array = [];
        $values = ["a","b","c"];

        foreach ($values as  $value) {
            $days = "days_".$value;
            $days = $this->daysToMonthes($$days) ;

            $months = $days < 1 ? 0 : $days;
            $rate = "rate_".$value;
            $date = date("Y-m-d",strtotime(date("Y-m-d", strtotime($start_date)) . "+$months  month"));
            $amount = $openingBalance*($$rate/100);
            isset($final_array[$date])  ? $final_array[$date] += $amount :  $final_array[$date] = $amount;
        }
        array_multisort(array_map('strtotime',array_keys($final_array)),SORT_ASC,$final_array);
        return $final_array ;
    }
    //Opeanning Loan Installment
    public function opeanningLoanInstallment($duration_months_in_years,$openingBalance,$years)
    {
        $openingbalanceCost =[];


        foreach ($duration_months_in_years as $year => $months) {
            if(isset($years[$year]))
            {
                $balance_field_name = $years[$year]."_end_balance";
                if($years[$year] == "first"){
                    $installment_amount = $openingBalance->$balance_field_name ?? 0;
                    $loan_amount = $openingBalance->long_term_loan_amount?? 0;
                }else{
                    $installment_amount = $openingBalance->$balance_field_name ?? 0;
                    $loan_name = $years[$year-1]."_end_balance";
                    $loan_amount = $openingBalance->$loan_name?? 0;
                }
                $openning_installment = $loan_amount-$installment_amount;
                $months_num_per_year = array_sum($months);
                array_walk($months,function(&$value , $date) use($openingBalance,$months_num_per_year,$years,&$openingbalanceCost,$openning_installment){
                    $year = date('Y',strtotime($date));
                    $balance_field_name = $years[$year]."_end_balance";
                    $balance_value = $openingBalance->$balance_field_name ?? 0;
                    $openingbalanceCost[$date] = $value * ($openning_installment / $months_num_per_year) ;
                });
            }

        }
        return $openingbalanceCost;
    }
    //Other Long Liabilities
    public function otherLongLiabilities($duration_months_in_years,$openingBalance,$years)
    {
        $otherLongLiabilities =[];
        foreach ($duration_months_in_years as $year => $months) {
            if(isset($years[$year]))
            {
                $months_num_per_year = array_sum($months);
                array_walk($months,function(&$value , $date) use($openingBalance,$months_num_per_year,$years,&$otherLongLiabilities){
                    $year = date('Y',strtotime($date));
                    $Interest_field_name = $years[$year]."_settlment_amount";
                    $Interest_value = $openingBalance->$Interest_field_name ?? 0;
                    $otherLongLiabilities[$date] = $value * ($Interest_value / $months_num_per_year) ;
                });
            }
        }
        return $otherLongLiabilities;
    }
}
