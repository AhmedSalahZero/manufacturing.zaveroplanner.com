<?php

namespace App\Http\Controllers;

use App\Project;
use App\SalesItems\DurationYears;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpensesDashboardController extends Controller
{

    public function expensesData($sales , $project , DurationYears $durationYears,$openingBalance,$years,$gross_value){

        $salaries=[];
        // direct

        $expenses_array = ['sales','operational_salaries','general'];
        foreach ($expenses_array as $key => $expense_name) {
            $salaries[$expense_name] = $this->salaries($project,$durationYears,$expense_name);
        }
        //Expense Row
        $expense = $project->expense;
        //Monthly Markting Expenses
        $years = (new ProjectController)->years($project,'project');
        $years = call_user_func_array('array_merge', $years);
        $marketing_rate_per_year =[];
        $distribution_rate_per_year =[];
        $selling_start_date = $project->new_company == 0 ? $project->start_date : (new ProjectController)->years($project,  'full_min_selling_date');

        foreach ($years as $count => $year) {
            $marketing_rate_field = "monthly_marketing_expenses_".$count."_rate";
            $distribution_rate_field = "monthly_distribution_expenses_".$count."_rate";
            $marketing_rate_per_year[$year] = $expense->$marketing_rate_field ?? 0;
            $distribution_rate_per_year[$year] = $expense->$distribution_rate_field ?? 0;
            $other_rate_per_year[$year] = $expense->other_rate ?? 0;
            $sales_commission_rate_per_year[$year] = $expense->sales_commission_rate ?? 0;
        }

        $monthly_markting_expense = $this->expenseRate($sales ,$marketing_rate_per_year??[]);
        $monthly_distribution_expense = $this->expenseRate($sales ,$distribution_rate_per_year??[]);

        // Other G&A
        $other_expenses = $this->expenseRate($sales ,($other_rate_per_year));
        // Sales Commission
        $sales_commission = $this->expenseRate($sales ,($sales_commission_rate_per_year));

        //Sales Commission Paymet
        $sales_commission_payment = [];
        if(isset($expense->sales_commission_payment_interval)){
            $sales_commission_payment = $expense->sales_commission_payment_interval == 'monthly' ? $sales_commission : $this->expensePayment($sales_commission,$project->start_date);
        }
        //Marketing Campaign
        $marketing_campaign =[date('01-m-Y',strtotime($selling_start_date))=>$expense->marketing_campain?? 0];

        //Start Up Feez
        $start_up_feez =[date('01-m-Y',strtotime($project->start_date))=>@$expense->start_up_fees];
        //Monthly General Expenses Amount
        $duration_months_in_years = $durationYears->years($project->end_date,$project->start_date,(($project->duration*12)-1),'years');

        $monthly_general_expense = $this->generalExpenses(@$expense->monthly_general_expenses,@$expense->expenses_annual_progression,$duration_months_in_years);
        //Monthly Deprication
        $assets = $project->assets;

        // Date Of Purchase
        $month_of_project_start_date = date('m', strtotime($project->start_date));
        if($project->new_company == 0){

            $fixed_asset_purchase_date_one = isset($assets->date_of_addition_one) && ($assets->date_of_addition_one != '') ?
                                                 $years[$assets->date_of_addition_one].'-'.$month_of_project_start_date.'-01': $project->start_date;

            $fixed_asset_purchase_date_two = isset($assets->date_of_addition_two) && ($assets->date_of_addition_two != '') ?
                                                  $years[$assets->date_of_addition_two].'-'.$month_of_project_start_date.'-01': $project->start_date;
        }else{
            $min_selling_date =  (new ProjectController)->years($project,  'full_min_selling_date');
            $purchase_date_one = isset($assets->date_of_addition_one) ? $years[@$assets->date_of_addition_one].'-'.$month_of_project_start_date.'-01': $project->start_date;
            $purchase_date_two = isset($assets->date_of_addition_two) ? $years[@$assets->date_of_addition_two].'-'.$month_of_project_start_date.'-01' : $project->start_date;
            $fixed_asset_purchase_date_one = (strtotime($purchase_date_one)  >= strtotime($min_selling_date)) ? $purchase_date_one : $min_selling_date;
            $fixed_asset_purchase_date_two = (strtotime($purchase_date_two)  >= strtotime($min_selling_date)) ? $purchase_date_two : $min_selling_date;

        }

        $fixed_asset_purchase_payment_date_one =  isset($assets->date_of_addition_one) && ($assets->date_of_addition_one != '') ? '01-'.$month_of_project_start_date .'-'.$years[$assets->date_of_addition_one] :  date('01-m-Y',strtotime($project->start_date)) ;
        $fixed_asset_purchase_payment_date_two =  isset($assets->date_of_addition_two) && ($assets->date_of_addition_two != '')  ?  '01-'.$month_of_project_start_date .'-'.$years[$assets->date_of_addition_two]: date('01-m-Y',strtotime($project->start_date)) ;



        $monthly_deprication_value =  @$assets->monthly_deprication;
        $life_duration_months =  @$assets->life_duration_months;
        $monthly_deprication = $this->monthlyDeprication($fixed_asset_purchase_date_one,$monthly_deprication_value,$life_duration_months );

        //Assets Down payment amount
        $amount = (@$assets->fixed_assets_value * @$assets->down_payment/100);
        $assets_down_payment_amount =[$fixed_asset_purchase_payment_date_one=>$amount];
        $balance_amount = (@$assets->fixed_assets_value * @$assets->balance_rate/100);

        // Assets Two
        $monthly_deprication_value_two =  @$assets->monthly_deprication_two;
        $life_duration_months_two =  @$assets->life_duration_months_two;
        $monthly_deprication_two = $this->monthlyDeprication($fixed_asset_purchase_date_two,$monthly_deprication_value_two,$life_duration_months_two );

        //Assets Down payment amount
        $amount_two = (@$assets->fixed_assets_value_two * @$assets->down_payment_two/100);
        $assets_down_payment_amount_two =[$fixed_asset_purchase_payment_date_two=>$amount_two];
        $balance_amount_two = (@$assets->fixed_assets_value_two * @$assets->balance_rate_two/100);


        $assets_loan =$balance_amount + $balance_amount_two;
        $monthly_deprication = $this->finalTotal([$monthly_deprication,$monthly_deprication_two]);
        $assets_down_payment_amount = $this->finalTotal([$assets_down_payment_amount,$assets_down_payment_amount_two]);

        $value_one = [$fixed_asset_purchase_payment_date_one=>@$assets->fixed_assets_value];
        $value_two = [$fixed_asset_purchase_payment_date_two=>@$assets->fixed_assets_value_two];

        $assets_total_amount_per_year =$this->finalTotal([$value_one,$value_two]);
		// [Carbon::make($project->start_date)->format('Y-m-d') => $gross_value]
        $assets_total_amount =  $this->assets($duration_months_in_years,$assets_total_amount_per_year,$years);
		// [Carbon::make($project->start_date)->format('Y-m-d') => $gross_value]
        //Installment With Interest
        $installment_with_interest_one = $this->installmentWithInterest($fixed_asset_purchase_payment_date_one,$assets,'');
        $installment_with_interest_two = $this->installmentWithInterest($fixed_asset_purchase_payment_date_two,$assets,'two');

        $installment_with_interest['interest_amount'] = $this->finalTotal([$installment_with_interest_one['interest_amount'],$installment_with_interest_two['interest_amount']]);
        $installment_with_interest['interest_payment'] = $this->finalTotal([$installment_with_interest_one['interest_payment'],$installment_with_interest_two['interest_payment']]);
        $installment_with_interest['variable_installment'] = $this->finalTotal([$installment_with_interest_one['variable_installment'],$installment_with_interest_two['variable_installment']]);
        $installment_with_interest['end_payment'] = $this->finalTotal([$installment_with_interest_one['end_payment'],$installment_with_interest_two['end_payment']]);

        //openingBalance
        $monthly_deprication_value = @$openingBalance->monthly_deprication;
        $duration = @$openingBalance->duration ;
        //Opening Monthly Deprication

        $opening_monthly_deprication = $this->openingMonthlyDeprication($project->start_date,$monthly_deprication_value,$duration);
        //Opening Interest Cost
        $opening_interest_cost = $this->openingInterestCost($duration_months_in_years,@$openingBalance,$years);

        //Opening Interest Paymet
        $opening_interest_payment = @$openingBalance->installment_interval == 'monthly' ? $opening_interest_cost : $this->expensePayment($opening_interest_cost,$project->start_date);

        return [

            'salaries' => $salaries ,
            'monthly_markting_expense' => $monthly_markting_expense ,
            'other_expenses' => $other_expenses ,
            'sales_commission' => $sales_commission ,
            'sales_commission_payment' => $sales_commission_payment ,
            'marketing_campaign' => $marketing_campaign ,
            'start_up_feez' => $start_up_feez ,
            'monthly_general_expense' => $monthly_general_expense ,
            'monthly_deprication' => $monthly_deprication ,
            'installment_with_interest' => $installment_with_interest ,
            'opening_monthly_deprication' => $opening_monthly_deprication ,
            'opening_interest_cost' => $opening_interest_cost ,
            'opening_interest_payment' => $opening_interest_payment,
            'monthly_distribution_expense' =>$monthly_distribution_expense,
            //assets data
            'assets_down_payment_amount'=>$assets_down_payment_amount,
            'assets_loan'=>$assets_loan,
            'assets_interest_rate' =>  @$assets->interest_rate ?? 0,
            'assets_total_amount'=>$assets_total_amount

        ];

    }
    public function salaries($project,$durationYears,$expense_name)
    {
        $years = (new ProjectController)->years($project,($project->new_company == 0 ?'project' : 'min_selling_date'));
        $years = call_user_func_array('array_merge', $years);
        $years = array_combine(array_values($years),array_keys($years));

        $duration_months_in_years = $durationYears->years($project->end_date,$project->start_date,(($project->duration*12)-1),'years');
        $manPower = $project->manPower;

        //Final Array
        $salaries =[];

        if (isset($manPower)) {
            foreach ($duration_months_in_years as $year => $months) {

                array_walk($months,function($value , $date) use($manPower,$years,$year,$expense_name,&$salaries){
                    $field_name  =  isset($years[$year]) ? $expense_name.'_'.$years[$year]."_capacity" : null;
                    if(strpos(($field_name), 'first') === false) {
                        $salary = ($manPower->$field_name  ?? 0);
                    }else{
                        $month  = date('m',strtotime($date));
                        if ($month == '01' ||$month == '02' ||$month == '03'  ) {
                            $salary = (($manPower->$field_name)['one']  ?? 0);
                        } elseif ($month == '04' ||$month == '05' ||$month == '06'  ) {
                            $salary = (($manPower->$field_name)['two']  ?? 0);
                        } elseif ($month == '07' ||$month == '08' ||$month == '09'  ) {
                            $salary = (($manPower->$field_name)['three']  ?? 0);
                        } elseif ($month == '10' ||$month == '11' ||$month == '12'  ) {
                            $salary = (($manPower->$field_name)['four']  ?? 0);
                        }
                    }


                    $salaries[$date] = $value * $salary ;

                });
            }
        }

        return $salaries;

    }
    public function generalExpenses($general_expenses,$expenses_annual_progression,$duration_months_in_years)
    {
        $total_expenses=[];
        $counter = 0;
        $expenses_annual_progression = $expenses_annual_progression/100;
        foreach($duration_months_in_years as $year => $months){

            array_walk($months, function($value,$date) use (&$general_expenses,$expenses_annual_progression,&$total_expenses,&$counter){
                if($value == 1){
                    if($counter%12 == 0 && $counter != 0){
                        $general_expenses = $general_expenses * (1+$expenses_annual_progression);
                    }
                    $total_expenses[$date] =$general_expenses;
                    $counter++;
                }
            });
        }
        return $total_expenses ;
    }
    //Calculation of Monthly Markting Expenses - Operation Cost Expenses - Other G&A
    public function expenseRate($sales,$rate_per_year)
    {
        $expenses=[];
        array_walk($sales,function($value,$date)use(&$expenses,$rate_per_year){
            $rate =$rate_per_year[date('Y',strtotime($date))] ?? 0;

            $result = $value * ($rate/100);
            $expenses[$date]= $result;
        });
        return $expenses;
    }
    //Expense Commission Payment
    public function expensePayment($sales_commission,$start_date)
    {
        $quarterly_result = array();
        $counter = 1;
        $total_interval = 0;
        $array_count = @count($sales_commission);
        array_walk($sales_commission , function($value,$date) use(&$quarterly_result,&$counter,&$total_interval,$array_count,$start_date){
            if (strtotime($date) >= strtotime($start_date)) {
                if($counter%3 == 0 ){
                    $total_interval += $value;
                    $quarterly_result[$date] = $total_interval;
                    $total_interval =0 ;
                }elseif($array_count == $counter){
                    $total_interval += $value;
                    $quarterly_result[$date] = $total_interval;
                    $total_interval =0 ;
                }else{
                    $total_interval += $value;
                }
                $counter++;
            }
        });
        return $quarterly_result;
    }
    //Monthly Deprication
    public function monthlyDeprication($start_date,$monthly_deprication_value,$life_duration_months)
    {
        $monthly_deprication=[];
        for ($month=0; $month < $life_duration_months ; $month++) {
            $date = date("Y-m-d",strtotime(date("Y-m-d", strtotime($start_date)) . "+$month  month"));
            $monthly_deprication[$date] = $monthly_deprication_value;
        }

        return $monthly_deprication;
    }
    //Installment With Interest
    public function installmentWithInterest($starting_date,$assets,$asset_number='')
    {
        $fixed_assets_value_field = 'fixed_assets_value'.($asset_number == 'two'?'_two':'');
        $cost = @$assets->$fixed_assets_value_field;
        $installment_distributions =[];
        $interest_amount_distributions =[];
        $interest_payment_distributions =[];
        $variable_installment_distributions =[];

        $down_payment_two_month = 0;
        $down_payment_two = 0;
        $grace_period = 0;

        $down_paymen_field = 'down_payment'.($asset_number == 'two'?'_two':'');
        $balance_rat_field = 'balance_rate'.($asset_number == 'two'?'_two':'');
        $interest_rat_field = 'interest_rate'.($asset_number == 'two'?'_two':'');
        $installment_coun_field = 'installment_count'.($asset_number == 'two'?'_two':'');
        $balance_rate_perio_field = 'balance_rate_period'.($asset_number == 'two'?'_two':'');

        $down_payment = @$assets->$down_paymen_field;
        $balance_rate = @$assets->$balance_rat_field;
        $interest_rate = @$assets->$interest_rat_field;
        $installment_count = @$assets->$installment_coun_field;
        $balance_rate_interval = @$assets->$balance_rate_perio_field;

        // $down_payment = @$assets->down_payment;
        // $balance_rate = @$assets->balance_rate;
        // $interest_rate = @$assets->interest_rate;
        // $installment_count = @$assets->installment_count;
        // $balance_rate_interval = @$assets->balance_rate_period;

        $interval_num = $this->installmentCountPerYear($balance_rate_interval);
        //the tottal duration for the entire cycle
        $total_duration = ($interval_num * $installment_count);
        //the start date of the loan according for the selected project
        $installment_with_interest_start_date = date('Y-m-d',strtotime($starting_date));
        //loan amount
        $installment_with_interest_amount = $cost;


            //for both the installment and interest intervals if its monthly the num wil be 1 ,quarterly the number will be 3 semiAnnually will be 6

            $InstallmentIntervalNum = $this->intervalNumber($balance_rate_interval);
            $InterestIntervalNum =$InstallmentIntervalNum;
            //Equation of Calculating the installment_amount
            if ($balance_rate_interval == 'monthly') {
                $installment_amount = $total_duration == 0 ? 0 : ($cost/($total_duration))*($balance_rate /100);
            }elseif ($balance_rate_interval == 'quarterly') {
                $installment_amount = $total_duration == 0 ? 0 : ($cost/(($total_duration/12)*4))*($balance_rate /100);
            }elseif ($balance_rate_interval == 'semi-annually') {
                $installment_amount = $total_duration == 0 ? 0 : ($cost/(($total_duration/12)*2))*($balance_rate /100);
            }elseif($balance_rate_interval == "annually"){
                $installment_amount = $total_duration == 0 ? 0 : ($cost/(($total_duration/12)*1))*($balance_rate /100);
            }else{
                $installment_amount =0;
            }

            $margin_borrowing_rate = $interest_rate;
            $interest_percent = $margin_borrowing_rate;


            $interest_per_interval = 0;
            $interest_payment = 0;
            $total_interest_payment = 0;
            $total_installment = 0;
            $conter = 1;
            $end_payment=0;
            $interest_amount=0;
            $current_interest_percent =0;
            // the loan distributions rows
            for ($month=0; $month <= $total_duration ; $month++) {
            // foreach ($installment_with_interest->longDistributions as $key=>$row) {
                //date
                $installment_with_interest_date = date("Y-m-d",strtotime(date("Y-m-d", strtotime($installment_with_interest_start_date)) . " +$month  month")) ;

                //if there id an updated borrowing rate update the interest_percent
                $total_due = $installment_with_interest_amount;
                if ($month == 0) {
                    $installment = $cost * ($down_payment/100);

                }elseif ($month == $down_payment_two_month) {
                    $installment = $cost * ($down_payment_two/100);
                }
                elseif ($month > $down_payment_two_month){
                    // month #num of days
                    $month_num_days = cal_days_in_month(CAL_GREGORIAN,date('m',strtotime($installment_with_interest_date)),date('Y',strtotime($installment_with_interest_date)));

                    // $interest_percent = $interest_percent ;

                    $current_interest_percent = (($interest_percent/365)*$month_num_days)/100;

                    $interest_amount = ($current_interest_percent) * $installment_with_interest_amount ;
                    $interest_per_interval += $interest_amount;
                    //in the garace period the instalmment will be equal to 0
                    // also the installment will equal 0 if the month is not in the interval
                    $total_due = $installment_with_interest_amount+$interest_amount;
                    if ($grace_period > $month || !is_int($conter / $InstallmentIntervalNum) ) {
                        $installment = 0;
                    }else{
                        $installment = $installment_amount;
                    }



                }
                    if ($InterestIntervalNum != 0 && is_int($conter / $InterestIntervalNum) ) {
                        $interest_payment = $interest_per_interval;
                        $end_payment = $total_due - $interest_payment - $installment;

                        $interest_per_interval = 0;
                    }else{
                        $interest_payment = 0;
                        $end_payment = $total_due - $interest_payment - $installment;

                    }
                // var_dump($installment_with_interest_date .'  '.number_format($installment).'  '.number_format($interest_amount,0) );
                //ARRAY
                if ($month == 0 || $month == $down_payment_two_month || $month > $down_payment_two_month) {
                    $installment_distributions [$month]['month'] = $month;
                    $installment_distributions [$month]['date'] =  $installment_with_interest_date;
                    $installment_distributions [$month]['loan_amount'] = $installment_with_interest_amount;
                    $installment_distributions [$month]['interest_percent'] = $current_interest_percent*100;
                    $installment_distributions [$month]['interest_amount'] = $interest_amount ;
                    $installment_distributions [$month]['total_due'] = $total_due ;
                    $installment_distributions [$month]['interest_payment'] = $interest_payment ;
                    $installment_distributions [$month]['variable_installment'] = $installment ;
                    $installment_distributions [$month]['fixed_installment'] = 0;
                    $installment_distributions [$month]['end_payment'] = $end_payment ;
                    $installment_distributions [$month]['down_payment_cost'] =  $installment+$interest_amount;

                    //full
                    $interest_amount_distributions[$installment_with_interest_date] = $interest_amount ;
                    $interest_payment_distributions[$installment_with_interest_date] =$interest_payment;
                    $variable_installment_distributions[$installment_with_interest_date] =$installment;
                    $end_payment_distributions[$installment_with_interest_date] = $end_payment ;

                    $total_interest_payment += $interest_payment;
                    $total_installment += $installment;

                    $installment_with_interest_amount = $end_payment ;

                }
                if ($month > $down_payment_two_month){
                    $conter++;
                }

            }
            return [
                'interest_amount' => $interest_amount_distributions,
                'interest_payment' => $interest_payment_distributions,
                'variable_installment' => $variable_installment_distributions,
                'end_payment' => $end_payment_distributions,
            ];
    }
    public function installmentCountPerYear($interval)
    {
        if($interval == 'monthly'){
            $count = 1;
        }elseif ($interval == 'quarterly'){
            $count = 3;
        }elseif ($interval == 'semi-annually'){
            $count = 6;
        }elseif ($interval == 'annually'){
            $count = 12;
        }else{
            $count = 0;
        }

        return $count;
    }
    public function intervalNumber($interval)
    {
        if($interval == 'monthly'){
            $count = 1;
        }elseif ($interval == 'quarterly'){
            $count = 3;
        }elseif ($interval == 'semi-annually'){
            $count = 6;
        }elseif ($interval == 'annually'){
            $count = 12;
        }else{
            $count = 0;
        }

        return $count;
    }
    //Opening Monthly Deprication
    public function openingMonthlyDeprication($start_date,$monthly_deprication_value,$duration)
    {

        $monthly_deprication=[];
        for ($month=0; $month < $duration ; $month++) {
            $date = date("Y-m-d",strtotime(date("Y-m-d", strtotime($start_date)) . "+$month  month"));
            $monthly_deprication[$date] = $monthly_deprication_value;
        }

        return $monthly_deprication;
    }
    //Opening Interest
    public function openingInterestCost($duration_months_in_years,$openingBalance,$years)
    {
        $years = array_combine(array_values($years),array_keys($years));

        $openingInterestCost =[];
        foreach ($duration_months_in_years as $year => $months) {
            if(isset($years[$year]))
            {
                $months_num_per_year = array_sum($months);
                array_walk($months,function(&$value , $date) use($openingBalance,$months_num_per_year,$years,&$openingInterestCost){
                    $year = date('Y',strtotime($date));
                    $Interest_field_name = $years[$year]."_interest_amount";
                    $Interest_value = $openingBalance->$Interest_field_name ?? 0;
                    $openingInterestCost[$date] = $value * ($Interest_value / $months_num_per_year) ;
                });
            }
        }
        return $openingInterestCost;
    }
    //assets
    public function assets($duration_months_in_years,$assets_total_amount,$years)
    {
        $years = array_combine(array_values($years),array_keys($years));

        $result =[];
        $asset_value = 0;
        foreach ($duration_months_in_years as $year => $months) {
            if(isset($years[$year]))
            {
                array_walk($months,function(&$value , $date) use($assets_total_amount,&$asset_value,$years,&$result){
                    $year = date('Y',strtotime($date));
                    $asset_value = ($assets_total_amount[$date]??0) + $asset_value;

                    $result[$date] = $asset_value;
                });
            }
        }
        return $result;
    }
    public static function finalTotal($array,$type_of_keys ='dates')
    {
        $final = [];
        if ($array !== null) {

            array_walk_recursive($array, function ($item, $key) use (&$final) {
                if (is_numeric($item)) {

                    $final[$key] = isset($final[$key]) ?  $item + $final[$key] : $item;
                }
            });
        }

        $type_of_keys == 'dates' ? array_multisort(array_map('strtotime', array_keys($final)), SORT_ASC, $final) : '';
        return $final;
    }
}
