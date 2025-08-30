<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Http\Controllers\BalanceSheet;
use App\Product;
use App\Project;
use App\SalesItems\DurationYears;
use App\Traits\ProjectTrait;
use App\Traits\Redirects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ProjectTrait;
    public function index(Request $request, $project, DurationYears $durationYears)
    {
        $slug = null;
        if ($request->is('*/study_results/view')) {
            $slug = $project;
            $project = $this->project($project);
        } else {
            $project = Project::findOrFail($project);
        }
        //Years Contract
        $years = (new ProjectController)->years($project, "Dashboard");

        //Years
        $dates =  $durationYears->years($project->end_date, $project->start_date, (($project->duration * 12) - 1), 'years');
        //All Duration Dates
        $dates = $this->dates($dates, $years);

        //Products Sales
        $products_sales_results = $this->salesCalculations($project, $durationYears, $dates);
        //Product Cost
        $products_rm_cost_total = @$products_sales_results['products_rm_cost_total'];
        $products_labor_cost_total = @$products_sales_results['products_labor_cost_total'];
        $products_moh_cost_total = @$products_sales_results['products_moh_cost_total'];

        // Manufacturing
        $manufacturing_total_values = (new InventoryCoverageDaysController)->inventoryCoverageDaysSales(@$products_sales_results['total_product_cost'], $project);

        $manufacturing_labor_values = $manufacturing_total_values['manufactured_labor_value'] ?? [];
        $manufacturing_moh_values = $manufacturing_total_values['manufactured_moh_value'] ?? [];
        $manufacturing_rm_values = $manufacturing_total_values['manufactured_rm_value'] ?? [];
        $manufacturing_ending_balances = $manufacturing_total_values['ending_balances'] ?? [];


        $products_payment_labor_cost_total = @$manufacturing_labor_values;
        $products_payment_moh_cost_total = @$manufacturing_moh_values;

        $rm_purchases = (new InventoryCoverageDaysController)->inventoryCoverageDaysRm(@$manufacturing_rm_values, $project);
        $rm_ending_balances = $rm_purchases['ending_balance'];
        $rm_purchases = $rm_purchases['rm_purchases'];


        //Purchase Payment
        $rm_purchase_payment = $this->purchasePayment(@$products_sales_results['all_products'], $rm_purchases);

        //Sales Total
        $project_sales = @$products_sales_results['sales'];

        //expenses
        //Opening Balance
        $openingBalanceRow = $project->openingBalance;
        $long_term_loan_amount = @$openingBalanceRow->long_term_loan_amount;
		$gross_value = ($openingBalanceRow->gross_value ?? 0);
        $expenses = (new ExpensesDashboardController)->expensesData($project_sales, $project, $durationYears, $openingBalanceRow, $years,$gross_value);

        //Openning Balance
		
        $openingBalance = (new OpeningBalanceDashboardController)->openingBalanceData($project, $durationYears, $openingBalanceRow, $years);
        //gross profit cost of product
        $gross_profit_cost_of_product = $this->costOfProductAndGross($dates, $project_sales, $expenses['monthly_deprication'], $expenses['opening_monthly_deprication'], $products_rm_cost_total, $products_labor_cost_total, $products_moh_cost_total);
        //total sales markting
        $total_sales_markting = $this->totalCalculationForMultiArrays($dates, @$expenses['monthly_markting_expense'], @$expenses['sales_commission'], @$expenses['marketing_campaign'], @$expenses['salaries']['sales']);

        $total_distribution_expenses = @$expenses['monthly_distribution_expense'];
        //total general expenses
        $total_general_expenses = $this->finalTotal([@$expenses['salaries']['general'], @$expenses['other_expenses'], @$expenses['start_up_feez'], @$expenses['monthly_general_expense'], @$expenses['salaries']['operational_salaries']]);
        //ebitda
        $ebitda  = $this->ebitda($dates, @$gross_profit_cost_of_product['gross_profit'], @$expenses['monthly_deprication'], @$expenses['opening_monthly_deprication'], $total_sales_markting, $total_general_expenses, $total_distribution_expenses);
        //ebit

        $ebit = $this->ebit($dates, @$gross_profit_cost_of_product['gross_profit'], @$expenses['monthly_deprication'], @$expenses['opening_monthly_deprication'], $total_sales_markting, $total_general_expenses, $total_distribution_expenses);

        //ebt
        $ebt = $this->ebtnetprofit($dates, @$ebit, $expenses['installment_with_interest']['interest_amount'], $expenses['opening_interest_cost']);
        //ebt taxes
        $ebt_taxes = $this->taxes($project->tax_rate, $ebt);
        //ebt taxes payment
        $ebt_taxes_payment = $this->taxesPayment($ebt_taxes);
        //ebit taxes
        $ebit_taxes = $this->taxes($project->tax_rate, $ebit);
        //ebt taxes payment
        $ebit_taxes_payment = $this->taxesPayment($ebit_taxes);


        //net profit
        $net_profit = $this->ebtnetprofit($dates, $ebt, $ebt_taxes, []);
        //Sales Collection Total
        $sales_collection = @$products_sales_results['sales_collection'];

        //Salaries Payment
        $salaries_payment = $this->totalCalculationForMultiArrays($dates, @$expenses['salaries']['sales'], @$expenses['salaries']['general'], @$expenses['salaries']['operational_salaries']);

        //Expenses & Interest /Installment Pyment
        $monthly_markting_expense = @$expenses['monthly_markting_expense'];
        $monthly_distribution_expense = @$expenses['monthly_distribution_expense'];
        $other_expenses = @$expenses['other_expenses'];
        $sales_commission = @$expenses['sales_commission'];
        $sales_commission_payment = @$expenses['sales_commission_payment'];
        $marketing_campaign = @$expenses['marketing_campaign'];
        $start_up_feez = @$expenses['start_up_feez'];
        $monthly_general_expense = @$expenses['monthly_general_expense'];
        $monthly_deprication = @$expenses['monthly_deprication'];
        $interest_payment = @$expenses['installment_with_interest']['interest_payment'];
        $variable_installment = @$expenses['installment_with_interest']['variable_installment'];

        $opening_interest_payment = @$expenses['opening_interest_payment'];
        $opening_interest_cost = @$expenses['opening_interest_cost'];

        //Openning Collection and payment
        $opening_customers_collection = @$openingBalance['opening_customers_collection'];
        $opening_debtors_collection = @$openingBalance['opening_debtors_collection'];
        $opening_suppliers_payment = @$openingBalance['opening_suppliers_payment'];
        $opening_creditors_payment = @$openingBalance['opening_creditors_payment'];
        $opeanning_loan_installment_payment = @$openingBalance['opeanning_loan_installment_payment'];
        $other_long_liabilities = @$openingBalance['other_long_liabilities'];
        //Total Investement Cash out
        $arrays_for_investment_cashout_flow = [
            'salaries_payment' => @$salaries_payment,
            'monthly_markting_expense' => @$monthly_markting_expense,
            'monthly_distribution_expense' => @$monthly_distribution_expense,
            'other_expenses' => @$other_expenses,
            'sales_commission_payment' => @$sales_commission_payment,
            'marketing_campaign' => @$marketing_campaign,
            'start_up_feez' => @$start_up_feez,
            'monthly_general_expense' => @$monthly_general_expense,
            'interest_payment' => @$interest_payment,
            'variable_installment' => @$variable_installment,
            'opening_interest_payment' => @$opening_interest_payment,
            'opening_suppliers_payment' => @$opening_suppliers_payment,
            'opening_creditors_payment' => @$opening_creditors_payment,
            'opeanning_loan_installment_payment' => @$opeanning_loan_installment_payment,
            'other_long_liabilities' => @$other_long_liabilities,
            'ebt_taxes_payment' => @$ebt_taxes_payment,
            'rm_purchase_payment' => @$rm_purchase_payment,
            // 'products_labor_cost_total' => $products_labor_cost_total,
            // 'products_moh_cost_total' => $products_moh_cost_total,
            'products_labor_cost_total' => $manufacturing_labor_values,
            'products_moh_cost_total' => $manufacturing_moh_values,
        ];

        $total_investement_cash_out = $this->cashOut($dates, $arrays_for_investment_cashout_flow);

        //sum collections
        $investement_total_collection = $this->totalCalculationForMultiArrays($dates, $sales_collection, @$opening_customers_collection, @$opening_debtors_collection, @$openingBalance['cash_banks_balance']);
        // investement net cashflow
        $investement_net_cashflow = $this->ebtnetprofit($dates, $investement_total_collection, $total_investement_cash_out, []);
        $acumulated_investement_net_cashflow =  (new BalanceSheet)->accumulation($dates, $investement_net_cashflow);
        //Investement Required
        $investement_required = $this->investementRequired($investement_net_cashflow);


        $capital = 0;
        $capital_date = 0;
        if (@count($investement_required) != 0) {
            $capital = min($investement_required) <= 0 ?  (-1 * min($investement_required)) : "NA";
            $capital_date = min($investement_required) <= 0 ? array_search((-1 * $capital), $investement_required) : "NA";
        }
        $installments = $variable_installment;
        if (count($variable_installment) > 0) {
            unset($installments[array_key_first($installments)]);
        }

        $installment_zero_interest = $expenses['assets_interest_rate'] == 0 ? $installments : [];

        //Total NPV Cashout
        $arrays_for_npv_cash_out = [
            'salaries_payment' => @$salaries_payment,
            'monthly_markting_expense' => @$monthly_markting_expense,
            'monthly_distribution_expense' => @$monthly_distribution_expense,
            'other_expenses' => @$other_expenses,
            'sales_commission_payment' => @$sales_commission_payment,
            'marketing_campaign' => @$marketing_campaign,
            'start_up_feez' => @$start_up_feez,
            'monthly_general_expense' => @$monthly_general_expense,
            'opening_suppliers_payment' => @$opening_suppliers_payment,
            'opening_creditors_payment' => @$opening_creditors_payment,
            'other_long_liabilities' => @$other_long_liabilities,
            'assets_down_payment_amount' => @$expenses['assets_down_payment_amount'],
            'ebit_taxes' => @$ebit_taxes_payment,
            'rm_purchase_payment' => @$rm_purchase_payment,
            'products_labor_cost_total' => $manufacturing_labor_values,
            'products_moh_cost_total' => $manufacturing_moh_values,
            'installment_zero_interest' => $installment_zero_interest
        ];

        // New NPV









        $total_npv_cash_out = $this->cashOut($dates, $arrays_for_npv_cash_out);
        // npv net cashflow
        //sum collections
        $npv_total_collection = $this->totalCalculationForMultiArrays($dates, $sales_collection, @$opening_customers_collection, @$opening_debtors_collection);
        $npv_net_cashflow = $this->ebtnetprofit($dates, $npv_total_collection, $total_npv_cash_out, []);

        $cash_banks_balance =  $openingBalance['cash_banks_balance'];
        //WACC
        $capital_val = ($capital == "NA") ? 0 : $capital;

        $total_equity = @$openingBalance['owners_equity'] + $capital_val;
        $total_investement = $total_equity + @$expenses['assets_loan'];

        $total_equity_percentage = $total_investement != 0   ? $total_equity / $total_investement : 0;
        $assets_loan_percentage = $total_investement != 0   ?  @$expenses['assets_loan'] / $total_investement : 0;

        $wacc = ($expenses['assets_interest_rate'] > 0) ?
            ($total_equity_percentage * ($project->return_rate / 100)) + ($assets_loan_percentage * ((@$expenses['assets_interest_rate'] / 100) * (1 - (($project->tax_rate ?? 0) / 100)))) : ($project->return_rate / 100);
        $wacc = ($wacc == 0) ? $project->return_rate / 100 : $wacc;




        // Balance sheet
        $inventory_fg_rm_end_balance = $this->finalTotal([$rm_ending_balances, $manufacturing_ending_balances]);

        $customer_resivable_end_balance =  [];
        $BalanceSheet_object =  new BalanceSheet;
        // receivablesPayableEndBalance
        $receivablesEndBalance = $BalanceSheet_object->endBalance($dates, $project_sales, $sales_collection);
        $total_rm_purchases = $this->finalTotal($rm_purchases);
        // payableEndBalance
        $payableEndBalance = $BalanceSheet_object->endBalance($dates, $total_rm_purchases, $rm_purchase_payment);
        $assets_total_amount = @$expenses['assets_total_amount'];
	
        $accumulated_monthly_deprication = $BalanceSheet_object->accumulation($dates, $monthly_deprication);
        // fixed_asset_end_balance

        $fixed_asset_end_balance = $BalanceSheet_object->fixedAssetsEndBalance($dates, $accumulated_monthly_deprication, $assets_total_amount);
        $installment_with_interest_end_payment = $expenses['installment_with_interest']['end_payment'];
        // $sales_commission_payment @$expenses['sales_commission']
        $other_creditors_balance_expenses = $BalanceSheet_object->endBalance($dates, $expenses['sales_commission'], $sales_commission_payment);
        // $taxes_end_balance
        $taxes_end_balance = $BalanceSheet_object->endBalance($dates, $ebt_taxes, $ebt_taxes_payment);

        // Opening
        $start_date = date('01-m-Y', strtotime($project->start_date));
        $accumulated_deprication = ($openingBalanceRow->accumulated_deprication ?? 0);
        $opening_monthly_deprication_array = $expenses['opening_monthly_deprication'];
        $opening_monthly_deprication_array[array_key_first($opening_monthly_deprication_array)] = ($opening_monthly_deprication_array[array_key_first($opening_monthly_deprication_array)] ?? 0) + $accumulated_deprication;

        $accumulated_opening_deprication = $BalanceSheet_object->accumulation($dates, $opening_monthly_deprication_array);
        
        $end_balance_opening_fixed_Assets = $BalanceSheet_object->fixedAssetsEndBalance($dates, $accumulated_opening_deprication, $gross_value);
        $checks_balance_value = ($openingBalanceRow->checks_balance ?? 0);
        $customers_invoices_checks_balance = [$start_date => $checks_balance_value];
        $end_balance_opening_customers = $BalanceSheet_object->endBalance($dates, $customers_invoices_checks_balance, $opening_customers_collection);

        $other_debtors_balance_value = ($openingBalanceRow->other_bebtors_balance ?? 0);
        $other_debtors_balance = [$start_date => $other_debtors_balance_value];
        $end_balance_other_debtors = $BalanceSheet_object->endBalance($dates, $other_debtors_balance, $opening_debtors_collection);

        $suppliers_checks_balance_value = ($openingBalanceRow->suppliers_checks_balance ?? 0);
        $suppliers_checks_balance = [$start_date => $suppliers_checks_balance_value];
        $end_balance_suppliers_checks_balance = $BalanceSheet_object->endBalance($dates, $suppliers_checks_balance, $opening_suppliers_payment);

        $other_creditors_balance_value = ($openingBalanceRow->other_creditors_balance ?? 0);
        $other_creditors_balance = [$start_date => $other_creditors_balance_value];
        $end_balance_other_creditors = $BalanceSheet_object->endBalance($dates, $other_creditors_balance, $opening_creditors_payment);

        $opening_interest_end_balance =  $BalanceSheet_object->endOpeningInterestBalance($dates, $opening_interest_cost, $opening_interest_payment);

        $long_term_loan_amount_value = ($openingBalanceRow->long_term_loan_amount ?? 0);
        $long_term_loan_amount = [$start_date => $long_term_loan_amount_value];
        $end_balance_long_term_loan = $BalanceSheet_object->endBalance($dates, $long_term_loan_amount, $opeanning_loan_installment_payment);

        $other_long_term_liabilites_value = ($openingBalanceRow->other_long_term_liabilites ?? 0);
        $other_long_term_liabilites = [$start_date => $other_long_term_liabilites_value];
        $end_balance_other_long_term_liabilites = $BalanceSheet_object->endBalance($dates, $other_long_term_liabilites, $other_long_liabilities);


        //////////////////////////////////////////////////////////////////////////////
        // Fixed Assets Gross Value

        // $assets_total_amount[date('Y-m-d',strtotime($project->start_date))] = ($assets_total_amount[date('Y-m-d',strtotime($project->start_date))]??0)+$gross_value;
        //    Change
        $balance_sheet_fixed_assets = $BalanceSheet_object->sumNumberThroughInterval($assets_total_amount,$gross_value);

        // Accumulated Deprication
        $balance_sheet_accumulated_deprication =  $this->finalTotal([$accumulated_opening_deprication, $accumulated_monthly_deprication]);
        // Net Fixed Assets
	
		;
        $balance_sheet_net_fixed_assets = $this->subtractAtDates([$balance_sheet_fixed_assets, $balance_sheet_accumulated_deprication],$dates);
        // $balance_sheet_net_fixed_assets = $this->finalTotal([$fixed_asset_end_balance, $end_balance_opening_fixed_Assets]);


        // Customers Receivables
        $balance_sheet_customers_receivables = $this->finalTotal([$receivablesEndBalance, $end_balance_opening_customers]);
        // Inventory
        $balance_sheet_inventory = $inventory_fg_rm_end_balance;
        // Other Debtors
        $balance_sheet_other_debtors = $end_balance_other_debtors;


        // Total_assets Without Cash
        $balance_sheet_total_assets_without_cash = $this->finalTotal([
            $balance_sheet_net_fixed_assets,
            $balance_sheet_customers_receivables,
            $balance_sheet_inventory,
            $balance_sheet_other_debtors
        ]);

        // Suppliers Payables
        $balance_sheet_suppliers_payables = $this->finalTotal([$payableEndBalance, $end_balance_suppliers_checks_balance]);
        // Other Creditors
        $balance_sheet_other_creditors = $this->finalTotal([
            $other_creditors_balance_expenses,
            $taxes_end_balance,
            $end_balance_other_creditors,
            $opening_interest_end_balance
        ]);
        // Total Current Liabilities
        $balance_sheet_total_current_liabilities =   $this->finalTotal([$balance_sheet_suppliers_payables, $balance_sheet_other_creditors]);

        //   Long Term Loans
        $balance_sheet_long_term_loans = $this->finalTotal([$installment_with_interest_end_payment, $end_balance_long_term_loan]);
        // Other Long Term Liabilities
        $balance_sheet_other_long_liabilities = $end_balance_other_long_term_liabilites;
        // Total Long Term Liabilities
        $balance_sheet_total_long_liabilities = $this->finalTotal([$balance_sheet_long_term_loans, $balance_sheet_other_long_liabilities]);
        // Paid up Capital
        $balance_sheet_paid_up_capital = $BalanceSheet_object->sameNumberThroughInterval($dates, ($openingBalanceRow->owners_equity ?? 0));
        // Additional Paid up Capital
        $negative = array_filter($acumulated_investement_net_cashflow, function ($v) {
            return $v < 0;
        });
        $balance_sheet_additional_paid_up_capital = $BalanceSheet_object->additionalCapital($dates, $negative);
        // Profit of the Period
        $net_profit = $net_profit;
        // Retained Earning
        $retained_earning = $BalanceSheet_object->retainedEarning($dates, $net_profit);
        //Total Owners Equity
        $balance_sheet_total_owners_equity = $this->finalTotal([
            $balance_sheet_paid_up_capital,
            $balance_sheet_additional_paid_up_capital,
            $net_profit,
            $retained_earning
        ]);

        // Cash & Banks Balance
        $balance_sheet_cash_and_banks = $BalanceSheet_object->cashBanks($dates, $balance_sheet_total_assets_without_cash, $balance_sheet_total_current_liabilities, $balance_sheet_total_long_liabilities, $balance_sheet_total_owners_equity);


        // Total Current Assets
        $balance_sheet_total_current_assets = $this->finalTotal([
            $balance_sheet_customers_receivables,
            $balance_sheet_inventory,
            $balance_sheet_cash_and_banks,
            $balance_sheet_other_debtors
        ]);
        // Total Assets
        $balance_sheet_total_assets = $this->finalTotal([$balance_sheet_total_current_assets, $balance_sheet_net_fixed_assets]);

        //////////////////////////////////////////////////////////////////////////
        if ((url()->full()) == route('table.index', $project)) {
            $compact = compact(
                'project',
                'salaries_payment',
                'investement_net_cashflow',
                'project_sales',
                'dates',
                'products_sales_results',
                'expenses',
                'gross_profit_cost_of_product',
                'total_sales_markting',
                'total_general_expenses',
                'ebitda',
                'ebit',
                'ebt',
                'ebt_taxes',
                'ebt_taxes_payment',
                'ebit_taxes',
                'net_profit',
                'sales_collection',
                'monthly_markting_expense',
                'monthly_distribution_expense',
                'other_expenses',
                'sales_commission',
                'sales_commission_payment',
                'marketing_campaign',
                'start_up_feez',
                'monthly_general_expense',
                'monthly_deprication',
                'interest_payment',
                'variable_installment',
                'opening_interest_payment',
                'opening_customers_collection',
                'opening_debtors_collection',
                'opening_suppliers_payment',
                'opening_creditors_payment',
                'opeanning_loan_installment_payment',
                'other_long_liabilities',
                'total_investement_cash_out',
                'wacc',
                'capital',
                // 'net_present_value',
                // 'irr',
                'total_distribution_expenses',
                'rm_purchase_payment',
                'products_rm_cost_total',
                'products_labor_cost_total',
                'products_moh_cost_total',
                'products_payment_labor_cost_total',
                'products_payment_moh_cost_total',
                'rm_purchase_payment',
                'cash_banks_balance',
                'acumulated_investement_net_cashflow',
                'balance_sheet_fixed_assets',
                'balance_sheet_accumulated_deprication',
                'balance_sheet_net_fixed_assets',
                'balance_sheet_customers_receivables',
                'balance_sheet_inventory',
                'balance_sheet_other_debtors',
                'balance_sheet_total_assets_without_cash',
                'balance_sheet_suppliers_payables',
                'balance_sheet_other_creditors',
                'balance_sheet_total_current_liabilities',
                'balance_sheet_long_term_loans',
                'balance_sheet_other_long_liabilities',
                'balance_sheet_total_long_liabilities',
                'balance_sheet_paid_up_capital',
                'balance_sheet_additional_paid_up_capital',
                'net_profit',
                'retained_earning',
                'balance_sheet_total_owners_equity',
                'balance_sheet_cash_and_banks',
                'balance_sheet_total_current_assets',
                'balance_sheet_total_assets',
            );
            return view('table', $compact);
        } elseif ((url()->full()) == route('dashboard.index', $project) || $request->is('*/study_results/view')) {
            //Charts
            $accumelated_net_cash_flow_chart = array();
            array_walk($dates, function ($date) use (&$investement_required, &$accumelated_net_cash_flow_chart) {
                $price = isset($investement_required[$date]) ? $investement_required[$date] : 0;
                $accumelated_net_cash_flow_chart[] = [
                    'date' => date("Y-m-d", strtotime($date)),
                    'price' => number_format($price),
                ];
            });

            $accumelated_net_cash_flow_chart = array_slice($accumelated_net_cash_flow_chart, 0, 36);

            //Calculating Years Totals
            $duration_years = array_keys($years);
            $project_sales_in_years =  $this->yearsTotal($duration_years, @$project_sales);
            $salaries_direct_in_years = $this->yearsTotal($duration_years, @$expenses['salaries']['direct']);
            $monthly_deprication_75_in_years = $this->yearsTotal($duration_years, @$expenses['monthly_deprication'], 0.90);
            $opening_monthly_deprication_75_in_years = $this->yearsTotal($duration_years, @$expenses['opening_monthly_deprication'], 0.90);
            $cost_of_products_in_years = $this->yearsTotal($duration_years, @$gross_profit_cost_of_product['cost_of_products']);
            $gross_profit_in_years = $this->yearsTotal($duration_years, @$gross_profit_cost_of_product['gross_profit']);
            $total_sales_markting_in_years = $this->yearsTotal($duration_years, @$total_sales_markting);
            $total_distribution_expenses_in_years = $this->yearsTotal($duration_years, @$total_distribution_expenses);
            $total_general_expenses_in_years  = $this->yearsTotal($duration_years, @$total_general_expenses);
            $monthly_deprication_25_in_years  = $this->yearsTotal($duration_years, @$expenses['monthly_deprication'], 0.10);
            $opening_monthly_deprication_25_in_years  = $this->yearsTotal($duration_years, @$expenses['opening_monthly_deprication'], 0.10);

            $ebitda_in_years  = $this->yearsTotal($duration_years, @$ebitda);
            $ebit_in_years  = $this->yearsTotal($duration_years, @$ebit);
            $ebt_in_years  = $this->yearsTotal($duration_years, @$ebt);
            $ebt_taxes_in_years  = $this->yearsTotal($duration_years, @$ebt_taxes);
            $net_profit_in_years  = $this->yearsTotal($duration_years, @$net_profit);

            $products_rm_cost_total_in_years = $this->yearsTotal($duration_years, @$products_rm_cost_total);
            $products_labor_cost_total_in_years = $this->yearsTotal($duration_years, @$products_labor_cost_total);
            $products_moh_cost_total_in_years = $this->yearsTotal($duration_years, @$products_moh_cost_total);

            $balance_sheet_fixed_assets_per_year = $this->decValues($years, $balance_sheet_fixed_assets);
            $balance_sheet_accumulated_deprication_per_year = $this->decValues($years, $balance_sheet_accumulated_deprication);
			
            $balance_sheet_net_fixed_assets_per_year = $this->decValues($years, $balance_sheet_net_fixed_assets);
            $balance_sheet_cash_and_banks_per_year = $this->decValues($years, $balance_sheet_cash_and_banks);
            $balance_sheet_customers_receivables_per_year = $this->decValues($years, $balance_sheet_customers_receivables);
            $balance_sheet_inventory_per_year = $this->decValues($years, $balance_sheet_inventory);
            $balance_sheet_other_debtors_per_year = $this->decValues($years, $balance_sheet_other_debtors);
            $balance_sheet_total_current_assets_per_year = $this->decValues($years, $balance_sheet_total_current_assets);
            $balance_sheet_total_assets_per_year = $this->decValues($years, $balance_sheet_total_assets);
            $balance_sheet_suppliers_payables_per_year = $this->decValues($years, $balance_sheet_suppliers_payables);
            $balance_sheet_other_creditors_per_year = $this->decValues($years, $balance_sheet_other_creditors);
            $balance_sheet_total_current_liabilities_per_year = $this->decValues($years, $balance_sheet_total_current_liabilities);
            $balance_sheet_long_term_loans_per_year = $this->decValues($years, $balance_sheet_long_term_loans);
            $balance_sheet_other_long_liabilities_per_year = $this->decValues($years, $balance_sheet_other_long_liabilities);
            $balance_sheet_total_long_liabilities_per_year = $this->decValues($years, $balance_sheet_total_long_liabilities);
            $balance_sheet_paid_up_capital_per_year = $this->decValues($years, $balance_sheet_paid_up_capital);
            $balance_sheet_additional_paid_up_capital_per_year = $this->decValues($years, $balance_sheet_additional_paid_up_capital);
            $retained_earning_per_year = $this->decValues($years, $retained_earning);
            $net_profit_per_year = $this->decValues($years, $net_profit);
            $balance_sheet_total_owners_equity_per_year = $this->decValues($years, $balance_sheet_total_owners_equity);

            // $investement_net_cashflow_in_years = $this->yearsTotal($duration_years,@$investement_net_cashflow);
            $year_first = array_key_first($years);
            $full_duration = @count(array_keys($years)) == 1 ? ['31-Mar-' . $year_first => 'Q1', '30-Jun-' . $year_first => 'Q2', '30-Sep-' . $year_first => 'Q3', '31-Dec-' . $year_first => 'Q4', 'Total' => 'Total'] : array_keys($years);
            $full_duration_for_balances = @count(array_keys($years)) == 1 ? ['31-Mar-' . $year_first => 'Q1', '30-Jun-' . $year_first => 'Q2', '30-Sep-' . $year_first => 'Q3', '31-Dec-' . $year_first => 'Q4', 'Total' => 'Total'] : array_keys($balance_sheet_accumulated_deprication_per_year);

            $sensitivity = $project->sensitivity;
            $products = (new Redirects)->forms($project, $backlog = null);

            // $checks_balance_value;
            // $other_debtors_balance_value;
            // $gross_value;
            $cash_banks_balance_value = $openingBalanceRow->cash_banks_balance ?? 0;
            // $suppliers_checks_balance_value
            // $other_creditors_balance_value
            // $long_term_loan_amount_value
            // $other_long_term_liabilites_value
            $products = (new Redirects)->productsforms($project, 'without-backlog');
            $total_beginning_inventory = $project->new_company == 1 ? 0 :  Product::whereIn('type', $products)->where('project_id', $project->id)->sum(DB::raw('rm_inventory_value + fg_inventory_value'));

            $dso = $BalanceSheet_object->average($checks_balance_value, $balance_sheet_customers_receivables_per_year);
            $dio = $BalanceSheet_object->average($total_beginning_inventory, $balance_sheet_inventory_per_year);
            $dpo = $BalanceSheet_object->average($suppliers_checks_balance_value, $balance_sheet_suppliers_payables_per_year);
            // Net Present Value & IRR
            if ($project->duration >= 3) {

                $net_profit_in_years;
                $total_deprication  = $this->finalTotal([@$opening_monthly_deprication_75_in_years, @$monthly_deprication_75_in_years, @$opening_monthly_deprication_25_in_years, @$monthly_deprication_25_in_years], 'years');


                // $balance_sheet_customers_receivables_per_year
                // Change In Balances
                $change_in_customer_receivable = $BalanceSheet_object->changeInWc($checks_balance_value, $balance_sheet_customers_receivables_per_year);
                $change_in_inventory = $BalanceSheet_object->changeInWc($total_beginning_inventory, $balance_sheet_inventory_per_year);
                $change_in_other_debtors = $BalanceSheet_object->changeInWc($other_debtors_balance_value, $balance_sheet_other_debtors_per_year);
                $change_in_suppliers_payables = $BalanceSheet_object->changeInWc($suppliers_checks_balance_value, $balance_sheet_suppliers_payables_per_year, 'current');
                $change_in_other_creditors = $BalanceSheet_object->changeInWc($other_creditors_balance_value, $balance_sheet_other_creditors_per_year, 'current');
                $change_in_long_term_liabilites = $BalanceSheet_object->changeInWc($other_long_term_liabilites_value, $balance_sheet_other_long_liabilities_per_year, 'current');
                $change_in_long_term_loans = $BalanceSheet_object->changeInWc($long_term_loan_amount_value, $balance_sheet_long_term_loans_per_year, 'current');
				#FIXME
                // $change_in_fixed_assets = $BalanceSheet_object->changeInWc($gross_value, $this->sumForAllValues($balance_sheet_fixed_assets_per_year,$gross_value) );
				
				$change_in_fixed_assets = $BalanceSheet_object->changeInWc($gross_value , $balance_sheet_fixed_assets_per_year);

                $cfo = $this->finalTotal([
                    $net_profit_in_years,
                    $total_deprication,
                    $change_in_customer_receivable,
                    $change_in_inventory,
                    $change_in_other_debtors,
                    $change_in_suppliers_payables,
                    $change_in_other_creditors,
                    $change_in_long_term_liabilites
                ], 'years');

                $fcfe = $this->finalTotal([$cfo, $change_in_long_term_loans, $change_in_fixed_assets], 'years');

                $cash_and_loans = ($openingBalanceRow->cash_banks_balance ?? 0) - ($openingBalanceRow->long_term_loan_amount ?? 0);
                $capital_val = ($capital == "NA") ? 0 : $capital;

                $net_present = $this->netPresentValue($fcfe, $dates, $balance_sheet_long_term_loans_per_year, (($project->return_rate ?? 0) / 100), $project->perpetual_growth_rate, @$openingBalanceRow->cash_banks_balance, $project);

                $net_present_value = $net_present['net_present_value'];
                $irr =  $net_present['irr'];
            } else {
                $net_present_value = "NA";
                $irr = "NA";
            }
            $discount_rate_value = $project->return_rate ?? 0;

            $compact = compact(
                'slug',
                'duration_years',
                'full_duration_for_balances',
                'wacc',
                'net_present_value',
                'irr',
                'project',
                'capital',
                'capital_date',
                'checks_balance_value',
                'total_beginning_inventory',
                'other_debtors_balance_value',
                'gross_value',
                'cash_banks_balance_value',
                'suppliers_checks_balance_value',
                'other_creditors_balance_value',
                'long_term_loan_amount_value',
                'other_long_term_liabilites_value',
                'project_sales_in_years',
                'salaries_direct_in_years',
                'monthly_deprication_75_in_years',
                'opening_monthly_deprication_75_in_years',
                'cost_of_products_in_years',
                'gross_profit_in_years',
                'total_sales_markting_in_years',
                'total_distribution_expenses_in_years',
                'total_general_expenses_in_years',
                'monthly_deprication_25_in_years',
                'opening_monthly_deprication_25_in_years',
                'ebitda_in_years',
                'ebit_in_years',
                'ebt_in_years',
                'ebt_taxes_in_years',
                'net_profit_in_years',
                'full_duration',
                'accumelated_net_cash_flow_chart',
                'sensitivity',
                'products',
                'products_rm_cost_total_in_years',
                'products_labor_cost_total_in_years',
                'balance_sheet_fixed_assets_per_year',
                'balance_sheet_accumulated_deprication_per_year',
                'balance_sheet_net_fixed_assets_per_year',
                'balance_sheet_cash_and_banks_per_year',
                'balance_sheet_customers_receivables_per_year',
                'balance_sheet_inventory_per_year',
                'balance_sheet_other_debtors_per_year',
                'balance_sheet_total_current_assets_per_year',
                'balance_sheet_total_assets_per_year',
                'balance_sheet_suppliers_payables_per_year',
                'balance_sheet_other_creditors_per_year',
                'balance_sheet_total_current_liabilities_per_year',
                'balance_sheet_long_term_loans_per_year',
                'balance_sheet_other_long_liabilities_per_year',
                'balance_sheet_total_long_liabilities_per_year',
                'balance_sheet_paid_up_capital_per_year',
                'balance_sheet_additional_paid_up_capital_per_year',
                'retained_earning_per_year',
                'net_profit_per_year',
                'balance_sheet_total_owners_equity_per_year',
                'products_moh_cost_total_in_years',
                'project',
                'dso',
                'dio',
                'dpo',
                'discount_rate_value'
            );

            return view('dashboard.index', $compact);
        }
    }
    //Days
    public function dates($durationYears, $years)
    {

        $dates = [];
        foreach ($durationYears as $year => $months) {

            if (isset($years[$year])) {
                array_walk($months, function (&$value, $date) use (&$dates) {
                    if ($value != 0) {
                        $dates[] = $date;
                    }
                });
            }
        }

        return $dates;
    }
    //Sales Function
    public function salesCalculations($project, $durationYears, $dates)
    {
        // $years = (new ProjectController)->years($project, 'Dashboard_sales');


        $sensitivity = $project->sensitivity;

        $products = (new Redirects)->completedProducts($project);

        $products_sales_total = [];
        $products_sales_collection_total = [];
        $all_products = [];

        $sales_contract = [];
        $product_cost = [];
        $products_rm_cost_total = [];
        $products_labor_cost_total = [];
        $products_moh_cost_total = [];
        $total_product_cost = [];
        foreach ($products as $key => $product_name) {

            $product = $project->product($product_name);
            $years = (new ProjectController)->years($project, null, $product_name);
            $years = call_user_func_array('array_merge', $years);
            $years = array_combine(array_values($years), array_keys($years));
            $field_of_selling_start_date = $product_name . '_selling_date';
            $duration_months_in_years = $durationYears->years($project->end_date, $project->$field_of_selling_start_date, (($project->duration * 12) - 1), 'years');
            $all_products[$key] = $product;
            $raw_material_total_name = "rm_" . $product_name;
            $sensitivity_value = $sensitivity->$raw_material_total_name ?? 0;

            $raw_material_sensitivity[$product_name] = (1 + $sensitivity_value / 100);


            // Seasonality
            $sales_seasonality_rates = $this->salesSeasonality($product, $duration_months_in_years, $years);
   

            //contract
            $sales_contract[$product_name] = $this->salesContract($years, $product, $sales_seasonality_rates, $sensitivity);
            // SALES VALUES & SALES COLLECTION
            $sales_and_collection_data = $this->productSalesAndCollection($product, $sales_contract[$product_name], $sensitivity);

            // Product  Cost
            $product_cost[$product_name] = $this->productsCost(@$sales_and_collection_data['sales'], $product, $raw_material_sensitivity[$product_name], $years);

            $total_product_cost[$product_name] = $this->totalCalculationForMultiArrays($dates, @$product_cost[$product_name]['rm_cost'], @$product_cost[$product_name]['labor_cost'], @$product_cost[$product_name]['moh_cost']);

            //Products totals
            $products_rm_cost_total = $this->totals(@$product_cost[$product_name]['rm_cost'], $products_rm_cost_total);
            $products_labor_cost_total = $this->totals(@$product_cost[$product_name]['labor_cost'], $products_labor_cost_total);
            $products_moh_cost_total = $this->totals(@$product_cost[$product_name]['moh_cost'], $products_moh_cost_total);
            //total Sales
            $products_sales_total = $this->totals($sales_and_collection_data['sales'], $products_sales_total);

            $products_sales_collection_total = $this->totals($sales_and_collection_data['sales_collection'], $products_sales_collection_total);
        }

        $data = [
            'sales' => $products_sales_total, 'sales_collection' => $products_sales_collection_total,
            'all_products' => $all_products, 'products_rm_cost_total' => $products_rm_cost_total,
            'products_labor_cost_total' => $products_labor_cost_total,
            'products_moh_cost_total' => $products_moh_cost_total,
            'total_product_cost' => $total_product_cost
        ];

        return $data;
    }
    public function daysToMonthes($days)
    {
       
        $coverage_months = 0;
        if ($days == 15) {
            $coverage_months = 0.5;
        } elseif ($days == 30) {
            $coverage_months = 1;
        } elseif ($days == 45) {
            $coverage_months = 1.5;
        } elseif ($days == 60) {
            $coverage_months = 2;
        } elseif ($days == 75) {
            $coverage_months = 2.5;
        } elseif ($days == 90) {
            $coverage_months = 3;
        } elseif ($days == 120) {
            $coverage_months = 4;
        } elseif ($days == 150) {
            $coverage_months = 5;
        } elseif ($days == 180) {
            $coverage_months = 6;
        }
        return $coverage_months;
    }
        public function daysToMonthesSalesAndCollectionAndPayments($days)
    {
        $coverage_months = 0;
        if ($days == 15) {
            $coverage_months = 0;
        } elseif ($days == 30) {
            $coverage_months = 1;
        } elseif ($days == 45) {
            $coverage_months = 2;
        } elseif ($days == 60) {
            $coverage_months = 2;
        } elseif ($days == 75) {
            $coverage_months = 3;
        } elseif ($days == 90) {
            $coverage_months = 3;
        } elseif ($days == 120) {
            $coverage_months = 4;
        } elseif ($days == 150) {
            $coverage_months = 5;
        } elseif ($days == 180) {
            $coverage_months = 6;
        }
        return $coverage_months;
    }
    // Sales Seasonality
    public function salesSeasonality($product, $duration_months_in_years, $years)
    {
        $seasonality_type = $product->seasonality;

        //Final Array
        $sales_seasonality_rates = [];
        foreach ($duration_months_in_years as $year => $months) {
            if (isset($years[$year])) {    // In case of Flate Seasonality
                if ($seasonality_type == "flat") {
                    $seasonality_constant = 1/12*100;
                    array_walk($months, function (&$value, $date) use ($seasonality_constant) {
                        $value = $seasonality_constant * $value;
                    });
                    $total_year_percentages = array_sum($months);
                    array_walk($months, function (&$value, $date) use ($total_year_percentages, &$sales_seasonality_rates) {
                        $sales_seasonality_rates[$date] = $total_year_percentages == 0 ? 0 : $value / $total_year_percentages;
                    });
                }
                // In case of Flate Distribute Quarterly
                elseif ($seasonality_type == "quarterly") {
                    $first_quarter = ($product->first_quarter / 100) / 3;
                    $second_quarter = ($product->second_quarter / 100) / 3;
                    $third_quarter = ($product->third_quarter / 100) / 3;
                    $fourth_quarter = ($product->fourth_quarter / 100) / 3;
                    array_walk($months, function (&$value, $date) use ($first_quarter, $second_quarter, $third_quarter, $fourth_quarter) {
                        //First Quarter OF year
                        $month = date("m", strtotime($date));
                        if ($month == 1 || $month == 2 || $month == 3) {
                            $value = $first_quarter * $value;
                        }
                        //Second Quarter OF year
                        if ($month == 4 || $month == 5 || $month == 6) {
                            $value = $second_quarter * $value;
                        }
                        //Third Quarter OF year
                        if ($month == 7 || $month == 8 || $month == 9) {
                            $value = $third_quarter * $value;
                        }
                        //Fourth Quarter OF year
                        if ($month == 10 || $month == 11 || $month == 12) {
                            $value = $fourth_quarter * $value;
                        }
                    });
                    $total_year_percentages = array_sum($months);

                    array_walk($months, function (&$value, $date) use ($total_year_percentages, &$sales_seasonality_rates) {
                        $sales_seasonality_rates[$date] = $total_year_percentages == 0 ? 0 : $value / $total_year_percentages;
                    });
                }
                // In case of Flate Distribute Monthly
                if ($seasonality_type == "monthly") {

                    array_walk($months, function (&$value, $date) use ($product) {
                        $month = date('F', strtotime($date));
                        $month_name = 'monthly_' . strtolower($month);
                        $month_rate = $product->$month_name / 100;
                        $value = $value * $month_rate;
                    });
                    $total_year_percentages = array_sum($months);
                    array_walk($months, function (&$value, $date) use ($total_year_percentages, &$sales_seasonality_rates) {
                        $sales_seasonality_rates[$date] = $total_year_percentages == 0 ? 0 : $value / $total_year_percentages;
                    });
                }
            }
        }
        return $sales_seasonality_rates;
    }
    // Sales Contract
    public function salesContract($years, $product, $sales_seasonality_rates, $sensitivity)
    {
        $sales_contract = [];

        array_walk($sales_seasonality_rates, function ($value, $date) use ($years, $product, $sensitivity, &$sales_contract) {
            $year = date('Y', strtotime($date));
            $contract_field_name = $years[$year] . "_contract";
            $sensitivity_field_name = 'target_' . $product->type;
            $sensitivity_value = $sensitivity->$sensitivity_field_name ?? 0;
            $sales_contract[$date] = $value * $product->$contract_field_name * (1 + ($sensitivity_value / 100));
        });


        return $sales_contract;
    }
    // Product Sales And Collections
    public function productSalesAndCollection($product, $sales_contract, $sensitivity)
    {
        $sales = [];
        $sales_collection = [];

        array_walk($sales_contract, function ($value, $date) use ($product, &$sales, &$sales_collection, $sensitivity) {
            //Sales

            isset($sales[$date]) ? $sales[$date] += $value : $sales[$date] =  $value;
            //Collection
            //Down Payment
            $collection_down_payment_value = $value * ($product->collection_down_payment / 100);
            $sales_collection[$date] = isset($sales_collection[$date]) ?  $sales_collection[$date] += $collection_down_payment_value :  $sales_collection[$date] = $collection_down_payment_value;
            //Initial & Final Collection
            $sensitivity_field_name = "collections_" . $product->type;
            $sensitivity_value = $sensitivity->$sensitivity_field_name ?? 0;

            $collection_initial_months = ($this-> daysToMonthesSalesAndCollectionAndPayments($product->initial_collection_days)  + $this-> daysToMonthesSalesAndCollectionAndPayments($sensitivity_value));
            $collection_final_months = ($this-> daysToMonthesSalesAndCollectionAndPayments($product->final_collection_days) + $this-> daysToMonthesSalesAndCollectionAndPayments($sensitivity_value));
            $collection_initial_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . "+$collection_initial_months  month"));
            $collection_final_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . "+$collection_final_months  month"));


            $collection_initial_value = $value * ($product->initial_collection_rate / 100);
            $collection_final_value = $value * ($product->final_collection_rate / 100);

            isset($sales_collection[$collection_initial_date]) ? $sales_collection[$collection_initial_date] += $collection_initial_value : $sales_collection[$collection_initial_date] =  $collection_initial_value;
            isset($sales_collection[$collection_final_date]) ? $sales_collection[$collection_final_date] += $collection_final_value : $sales_collection[$collection_final_date] =  $collection_final_value;
        });


        array_multisort(array_map('strtotime', array_keys($sales_collection)), SORT_ASC, $sales_collection);
        array_multisort(array_map('strtotime', array_keys($sales)), SORT_ASC, $sales);
        return ['sales' => $sales, 'sales_collection' => $sales_collection];
    }

    //Out Sourcing Capacity Cost
    public function productOperationCost($sales_array, $product_operation_cost_rates)
    {

        $product_operation_cost_per_product = [];
        $product_operation_cost_total = [];

        foreach ($sales_array as $product_name => $sales) {
            $product_operation_cost_rate = $product_operation_cost_rates[$product_name];

            array_walk($sales, function (&$value, $date) use ($product_operation_cost_rate, $product_name, &$product_operation_cost_per_product, &$product_operation_cost_total) {
                $result =  $value * $product_operation_cost_rate;
                $product_operation_cost_per_product[$product_name][$date] = $result;
                isset($product_operation_cost_total[$date]) ? $product_operation_cost_total[$date]  += $result : $product_operation_cost_total[$date] = $result;
            });
        }

        return ['product_operation_cost_per_product' => $product_operation_cost_per_product, 'product_operation_cost_total' => $product_operation_cost_total];
    }
    //Out Sourcing Capacity Payment
    public function purchasePayment($all_products, $rm_purchases)
    {

        $product_rm_purchases_payment = [];

        foreach ($all_products as $key => $product) {
            $product_name = $product->type;
            //Per Product
            $rm_purchases_per_product =  $rm_purchases[$product_name];

            array_walk($rm_purchases_per_product, function ($value, $date) use ($product, &$product_rm_purchases_payment) {

                //Payment Calculation
                $down_payment_rate = $product->outsourcing_down_payment / 100;
                $remaining_balance_rate = $product->balance_rate_one / 100;
                $remaining_balance_days = $this-> daysToMonthesSalesAndCollectionAndPayments($product->balance_one_due_in);
                $down_payment_days = 0;
                $down_payment_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . "+$down_payment_days  month"));
                $remaining_balance_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . "+$remaining_balance_days  month"));
                //Payment
                isset($product_rm_purchases_payment[$down_payment_date]) ? $product_rm_purchases_payment[$down_payment_date] += $value * $down_payment_rate : $product_rm_purchases_payment[$down_payment_date] = $value * $down_payment_rate;
                isset($product_rm_purchases_payment[$remaining_balance_date]) ? $product_rm_purchases_payment[$remaining_balance_date] += $value * $remaining_balance_rate : $product_rm_purchases_payment[$remaining_balance_date] = $value * $remaining_balance_rate;
            });
            array_multisort(array_map('strtotime', array_keys($product_rm_purchases_payment)), SORT_ASC, $product_rm_purchases_payment);
        }

        return  $product_rm_purchases_payment;
    }
    //Calculate Total Products
    public function totals($array, $total)
    {

        $dates = array_keys(array_merge($array, $total));
        array_walk($dates, function ($date) use (&$total, &$array) {
            $value =  $array[$date] ?? 0;

            isset($total[$date]) ? $total[$date] += $value : $total[$date] = $value;
        });
        array_multisort(array_map('strtotime', array_keys($total)), SORT_ASC, $total);
        return $total;
    }
    //Maximum Capacity
    public function maxCapacity($duration_months_in_years, $years, $project)
    {
        $max_capacity = [];

        foreach ($years as $year => $product) {
            if (isset($duration_months_in_years[$year])) {
                array_walk($duration_months_in_years[$year], function ($value, $date) use ($product, $project, &$max_capacity) {
                    $year = date('Y', strtotime($date));
                    $capacity_name = $product . "_capacity";
                    $max_capacity[$date] = $project->$capacity_name;
                });
            }
        }
        return $max_capacity;
    }
    //Cost Of Product AndGross
    public function costOfProductAndGross($dates, $sales, $monthly_deprication, $opening_monthly_deprication, $products_rm_cost_total, $products_labor_cost_total, $products_moh_cost_total)
    {
        $cost_of_products = [];
        $gross_profit = [];
        array_walk($dates, function ($date) use (&$cost_of_products, &$gross_profit, $sales, $monthly_deprication, $opening_monthly_deprication, $products_rm_cost_total, $products_labor_cost_total, $products_moh_cost_total) {
            $result =   (@$monthly_deprication[$date] * 0.90)
                + (@$opening_monthly_deprication[$date] * 0.90)
                + (@$products_rm_cost_total[$date])
                + (@$products_labor_cost_total[$date])
                + (@$products_moh_cost_total[$date]);
            $cost_of_products[$date] = $result;
            $gross_profit[$date] = @$sales[$date] - $result;
        });
        return  [
            'cost_of_products' => $cost_of_products,
            'gross_profit' => $gross_profit,
        ];
    }
    //Total Calculation For Multi Arrays
    public function totalCalculationForMultiArrays($dates, $array_one, $array_two, $array_three = null, $array_four = null)
    {
        $total = [];

        array_walk($dates, function ($date) use (&$total, $array_one, $array_two, $array_three, $array_four) {
            $result =   @$array_one[$date]
                + @$array_two[$date]
                + @$array_three[$date]
                + @$array_four[$date];

            $total[$date] = $result;
        });
        return  $total;
    }
    //ebitda
    public function ebitda($dates, $gross_profit, $monthly_deprication, $opening_monthly_deprication, $total_sales_markting, $total_general_expenses, $total_distribution_expenses)
    {
        $ebitda = [];

        array_walk($dates, function ($date) use (&$ebitda, $gross_profit, $monthly_deprication, $opening_monthly_deprication, $total_sales_markting, $total_general_expenses, $total_distribution_expenses) {
            $result =   @$gross_profit[$date]
                + (@$monthly_deprication[$date] * 0.90)
                + (@$opening_monthly_deprication[$date] * 0.90)
                - (@$total_sales_markting[$date] + @$total_general_expenses[$date] + @$total_distribution_expenses[$date]);

            $ebitda[$date] = $result;
        });
        return  $ebitda;
    }
    //ebit
    public function ebit($dates, $gross_profit, $monthly_deprication, $opening_monthly_deprication, $total_sales_markting, $total_general_expenses, $total_distribution_expenses)
    {
        $ebit = [];

        array_walk($dates, function ($date) use (&$ebit, $gross_profit, $monthly_deprication, $opening_monthly_deprication, $total_sales_markting, $total_general_expenses, $total_distribution_expenses) {
            $result =   @$gross_profit[$date]
                - (@$monthly_deprication[$date] * 0.10)
                - (@$opening_monthly_deprication[$date] * 0.10)
                - (@$total_sales_markting[$date] + @$total_general_expenses[$date] + @$total_distribution_expenses[$date]);

            $ebit[$date] = $result;
        });
        return  $ebit;
    }

    //ebtnetprofit
    public function ebtnetprofit($dates, $main_array, $first_array, $second_array)
    {
        $result_array = [];
        array_walk($dates, function ($date) use (&$result_array, $main_array, $first_array, $second_array) {
            $result =   @$main_array[$date]
                - (@$first_array[$date] + @$second_array[$date]);

            $result_array[$date] = $result;
        });
        return  $result_array;
    }
    //Taxes
    public function taxes($tax_rate, $ebt)
    {
        $tax_array = [];
        $first_month = array_key_first($ebt) != null ? date("m", strtotime(array_key_first($ebt))) : null;

        if (isset($first_month) && $first_month == "01") {
            $total_per_year = 0;
            array_walk($ebt, function ($value, $date) use (&$tax_array, &$total_per_year, $tax_rate) {
                $month = date("m", strtotime($date));
                if ($month != 12) {
                    $total_per_year += $value;
                } else {
                    $total_per_year += $value;
                    $tax_array[$date] = $total_per_year < 0 ? 0 : $total_per_year * ($tax_rate / 100);
                    $total_per_year = 0;
                }
            });
        } elseif ($first_month != "01") {
            $total_per_year = 0;
            $first_year = array_key_first($ebt) != null ? date("Y", strtotime(array_key_first($ebt))) : null;

            array_walk($ebt, function ($value, $date) use (&$tax_array, &$total_per_year, $tax_rate, $first_year) {
                $current_year =  date("Y", strtotime($date));
                $month = date("m", strtotime($date));
                if ($month != 12 || $current_year == $first_year) {
                    $total_per_year += $value;
                } elseif ($month == 12  && $current_year != $first_year) {
                    $total_per_year += $value;
                    $tax_array[$date] = $total_per_year < 0 ? 0 : $total_per_year * ($tax_rate / 100);
                    $total_per_year = 0;
                }
            });
        }
        return $tax_array;
    }
    //taxes Payment
    public function taxesPayment($tax_rate)
    {
        $tax_payment = [];
        array_walk($tax_rate, function (&$value, $date) use (&$tax_payment) {
            $new_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . "+4  month"));
            $tax_payment[$new_date] = $value;
        });
        return $tax_payment;
    }
    //cashOut
    public function cashOut($dates, $arrays)
    {
        $cashout = [];


        foreach ($arrays as $key => $array) {

            array_walk($dates, function ($date) use ($array, &$cashout) {

                $current_value = isset($array[$date]) ? $array[$date] : 0;
                isset($cashout[$date]) ?  $cashout[$date] += $current_value : $cashout[$date] = $current_value;
            });
        }

        return $cashout;
    }
    //InvestementRequired
    public function investementRequired($investement_required)
    {
        $accumulated = [];
        $total = 0;
        array_walk($investement_required, function ($value, $date) use (&$accumulated, &$total) {
            $total += $value;
            $accumulated[$date] = $total;
        });


        return  $accumulated;
    }
    //IRR
    public function netPresentValue($fcfe, $dates, $balance_sheet_long_term_loans_per_year, $discount_rate, $perpetual_growth_rate, $cash_banks_balance, $project)
    {
        $discounted_factor = [];
        $counter = 1;

        $perpetual_growth_rate = $perpetual_growth_rate / 100;
        $power = 0;
        // $first_year = reset($dates) != null ? date("Y", strtotime(reset($dates))) : null;

        foreach ($dates as $date) {
            $month = date("m", strtotime($date));
            $current_year = date("Y", strtotime($date));

            if ($month == '12') {

                $power = $counter / 12;
            } else {
                $power = 0;
            }

            $discounted_factor[$current_year] =  (pow((1 + $discount_rate), $power));

            $counter++;
        }


        $last_annual_free_cash_value = ((last($fcfe)) ?? 0);
        $last_key_fcfe = array_key_last($fcfe);
        $terminal_npv_rate = ($discount_rate - $perpetual_growth_rate) <= 0 ? 1 : $discount_rate - $perpetual_growth_rate;
        $terminal_value =  (($last_annual_free_cash_value * (1 + $perpetual_growth_rate)) / ($terminal_npv_rate));
        $loan_last_balance = last($balance_sheet_long_term_loans_per_year);
        $terminal_value_minus_debt = $terminal_value - ($loan_last_balance ?? 0);

        $fcfe_plus_terminal_value = $fcfe;
        $fcfe_plus_terminal_value[$last_key_fcfe] = ($fcfe[$last_key_fcfe] ?? 0) + $terminal_value_minus_debt;

        $discounted_fcfe_plus_terminal_value = $this->operationAmongTwoArrays(($fcfe_plus_terminal_value ?? []), ($discounted_factor ?? []));
        $net_present_value = array_sum($discounted_fcfe_plus_terminal_value) + ($cash_banks_balance ?? 0);
        function  checkIfArrayAllIsAllPositive(array $array)
        {
            $positiveNumbers = array_filter($array, function ($val) {
                return $val >= 0;
            });
            return count($positiveNumbers) == count($array);
        }

        function  checkIfArrayAllIsAllNegative(array $array)
        {
            $negativeNumbers = array_filter($array, function ($val) {
                return $val <= 0;
            });
            return count($negativeNumbers) == count($array);
        }

        function calculateIrr($fcfe_plus_terminal_value, $discount_rate,  $calculatedPercentage = null, $numberOfIteration = 1)
        {
            $yearsAndFreeCash = $fcfe_plus_terminal_value;

            if ($numberOfIteration == 1 && ((checkIfArrayAllIsAllNegative($yearsAndFreeCash)==true) || (checkIfArrayAllIsAllPositive($yearsAndFreeCash)==true) )) {
                return $irr = "No IRR";
            }

            $percentage = $calculatedPercentage ?: $discount_rate;
            $discountFactor = [];
            $npv = [];
            $counter = 1;

            foreach ($yearsAndFreeCash as $year => $freshCash) {
                $discountFactor[$counter] = pow(1  +  $percentage, $counter);
                $npv[$counter] = $freshCash / $discountFactor[$counter];
                $counter++;
            }



            $npv_sum = array_sum($npv);
            if($npv_sum == 0){
                return $irr = $discount_rate;
            }

            if ($numberOfIteration == 750000) {

                return $calculatedPercentage;
            }

            // need to make $npv_sum = 0 by changing  $percentage  to get irr
            // logger( $npv_sum > $npv_sum * 0.00001 || $npv_sum < $npv_sum * -0.00001);

            while( $npv_sum >= $npv_sum * 0.000001 || $npv_sum <= $npv_sum * -0.000001 )
            {
                if($npv_sum > 0)
                {
                    $irr = $percentage  + 0.00001 ;
                    return calculateIrr($fcfe_plus_terminal_value, $discount_rate, $irr, ++$numberOfIteration);

                } else{
                    $irr = $percentage - 0.00001 ;
                    return calculateIrr($fcfe_plus_terminal_value, $discount_rate, $irr, ++$numberOfIteration);

                }
            }

            return $calculatedPercentage;
        }



        if ($project->new_company == 0 || $project->duration <= 2) {
            $irr = "No IRR";
        } else {

            $irr = calculateIrr($fcfe_plus_terminal_value, $discount_rate, $calculatedPercentage = null, $numberOfIteration = 1);
        }


        if (is_numeric($irr)) {
            $irr = $irr * 100;
        }
        return ['irr' => $irr, 'net_present_value' => $net_present_value];
    }
    //YEARS TOTAL
    public function yearsTotal($years, $data, $multiply_values = 1)
    {
        $new_array = [];
        $data = $data ?? [];
        $duration = @count($years);
        if ($duration != 1) {
            foreach ($years as $key => $year) {


                array_walk($data, function ($value, $date) use ($year, &$new_array, $multiply_values) {
                    $current_year = date('Y', strtotime($date));
                    if ($year == $current_year) {
                        isset($new_array[$current_year]) ?  $new_array[$current_year] += ($value * $multiply_values) : $new_array[$current_year] = ($value * $multiply_values);
                    }
                });
            }
        } elseif ($duration == 1) {
            $year = array_shift($years);
            array_walk($data, function ($value, $date) use ($year, &$new_array, $multiply_values) {
                $current_month = date('m', strtotime($date));
                $current_year = date('Y', strtotime($date));
                if ($year == $current_year) {
                    if ($current_month == "01" || $current_month == "02" || $current_month == "03") {
                        isset($new_array['Q1']) ?  $new_array['Q1'] += ($value * $multiply_values) : $new_array['Q1'] = ($value * $multiply_values);
                    } elseif ($current_month == "04" || $current_month == "05" || $current_month == "06") {
                        isset($new_array['Q2']) ?  $new_array['Q2'] += ($value * $multiply_values) : $new_array['Q2'] = ($value * $multiply_values);
                    } elseif ($current_month == "07" || $current_month == "08" || $current_month == "09") {
                        isset($new_array['Q3']) ?  $new_array['Q3'] += ($value * $multiply_values) : $new_array['Q3'] = ($value * $multiply_values);
                    } elseif ($current_month == "10" || $current_month == "11" || $current_month == "12") {
                        isset($new_array['Q4']) ?  $new_array['Q4'] += ($value * $multiply_values) : $new_array['Q4'] = ($value * $multiply_values);
                    }
                    isset($new_array['Total']) ? $new_array['Total'] += ($value * $multiply_values) : $new_array['Total'] = ($value * $multiply_values);
                }
            });
        }

        return $new_array;
    }
    public function productsCost($sales_per_service, $product, $raw_material_sensitivity, $years)
    {
        $rm_cost = [];
        $labor_cost = [];
        $moh_cost = [];


        foreach ($years as $year => $count) {
            $rm_cost_rate_field = "rm_cost_" . $count . "_rate";
            $labor_cost_rate_field = "labor_cost_" . $count . "_rate";
            $moh_cost_rate_field = "moh_cost_" . $count . "_rate";

            $rm_cost_rate_per_year[$year] = $product->$rm_cost_rate_field ?? 0;
            $labor_cost_rate_per_year[$year] = $product->$labor_cost_rate_field ?? 0;
            $moh_cost_rate_per_year[$year] = $product->$moh_cost_rate_field ?? 0;
        }




        array_walk($sales_per_service, function ($value, $date) use ($product, &$rm_cost, &$labor_cost, &$moh_cost, $raw_material_sensitivity, $rm_cost_rate_per_year, $labor_cost_rate_per_year, $moh_cost_rate_per_year) {
            //Sales
            $rm_cost_rate =     $rm_cost_rate_per_year[date('Y', strtotime($date))] ?? 0;
            $labor_cost_rate =  $labor_cost_rate_per_year[date('Y', strtotime($date))] ?? 0;
            $moh_cost_rate =    $moh_cost_rate_per_year[date('Y', strtotime($date))] ?? 0;


            $rm_cost[$date] = $value * ($rm_cost_rate / 100) * $raw_material_sensitivity;
            $labor_cost[$date] = $value * ($labor_cost_rate / 100);
            $moh_cost[$date] = $value * ($moh_cost_rate / 100);
        });
        return [
            'rm_cost' => $rm_cost,
            'labor_cost' => $labor_cost,
            'moh_cost' => $moh_cost,
        ];
    }
    public static function finalTotal($array, $type_of_keys = 'dates')
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
	public static function subtractAtDates(array $items, array $dates)
	{
		$itemsCount = count($items);
		if (!$itemsCount) {
			return [];
		}
		if (!isset($items[0])) {
			throw new \Exception('Custom Exception .. First Parameter Must Be Indexes Array That Contains Arrays like [ [] , [] , [] ]');
		}

		$total = [];
		foreach ($dates as $date) {
			$currenTotal = 0;
			for ($i = 0; $i< $itemsCount; $i++) {
				if ($i == 0) {
					$currenTotal += $items[$i][$date]??0;
				} else {
					$currenTotal -= $items[$i][$date]??0;
				}
			}
			$total[$date] = $currenTotal;
		}

		return $total;
	}
    public function decValues($years, $data)
    {
        $result = [];
        $duration = @count($years);
        if ($duration == 1) {
            $year = array_key_first($years);
            array_walk($data, function ($value, $date) use ($year, &$result) {
                $current_month = date('m', strtotime($date));
                $current_year = date('Y', strtotime($date));
                if ($year == $current_year) {
                    if ($current_month == "03") {
                        $result['Q1'] = ($value);
                    } elseif ($current_month == "06") {
                        $result['Q2'] = ($value);
                    } elseif ($current_month == "09") {
                        $result['Q3'] = ($value);
                    } elseif ($current_month == "12") {
                        $result['Q4'] = ($value);
                    }
                    isset($result['Total']) ? $result['Total'] += ($value) : $result['Total'] = ($value);
                }
            });
        } else {

            foreach ($years as $year => $index) {
                $dec_date = "01-12-" . $year;
                $result[date("M-Y", strtotime($dec_date))] = ($data[$dec_date] ?? 0);
            }
        }

        return $result;
    }
    public static function operationAmongTwoArrays($array_one, $array_two, $operation = 'divide')
    {

        $dates = array_keys($array_one);

        $result = [];
        array_walk($dates, function ($date) use (&$result, $array_one, $array_two, $operation) {
            $value1 =  $array_one[$date] ?? 0;
            $value2 =  $array_two[$date] ?? 0;


            if ($operation == 'divide') {
                $result[$date] = $value2 != 0 ?  $value1 / $value2 : 0;
            } elseif ($operation == 'multiply') {
                $result[$date] = $value1 * $value2;
            } elseif ($operation == 'subtraction') {
                $result[$date] = $value1 - $value2;
            }
        });
        // array_multisort(array_map('strtotime', array_keys($result)), SORT_ASC, $result);
        return $result;
    }
	public function sumForAllValues($arr , $val)
	{
		$result = [];
		foreach($arr as $key => $value){
			$result[$key] = $value - $val ;
		}
		return $result ;
	}
}
