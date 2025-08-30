 <!doctype html>
 <html lang="en">

 <head>
     <!-- Required meta tags -->
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
     <meta name="csrf-token" content="{{ csrf_token() }}">
     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
         integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
     <link rel="stylesheet" href="{{ url('assets/main.css') }}">
     <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css"
         crossorigin="anonymous">
     <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css"
         crossorigin="anonymous">
     <style>
         table {
             overflow-y: scroll;
             white-space: nowrap;
         }

         table td {
             border: white 1px solid !important;
         }

         .tr-color {
             color: white;
             background-color: #3B368C;
         }

         .td-style {
             width: 60%;
             background-color: lightgrey
         }

         h1 {
             position: relative;
             padding: 0;
             margin: 0;
             font-family: "Raleway", sans-serif;
             font-weight: 300;
             font-size: 40px;
             color: #080808;
             -webkit-transition: all 0.4s ease 0s;
             -o-transition: all 0.4s ease 0s;
             transition: all 0.4s ease 0s;
         }

         .seven h1 {
            background-color: transparent;
             text-align: center;
             font-size: 30px;
             font-weight: 300;
             color: #222;
             letter-spacing: 1px;
             text-transform: uppercase;

             display: grid;
             grid-template-columns: 1fr max-content 1fr;
             grid-template-rows: 27px 0;
             grid-gap: 20px;
             align-items: center;
         }

         .seven h1:after,
         .seven h1:before {
             content: " ";
             display: block;
             border-bottom: 1px solid #c50000;
             border-top: 1px solid #c50000;
             height: 5px;
             background-color: #f8f8f8;
         }
     </style>

     @if (app()->getLocale() == 'ar')
         <link rel="stylesheet" href="{{ url('assets/main_ar.css') }}">
     @endif

     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
         integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA=="
         crossorigin="anonymous" />
     <title>ZAVERO Magic Sheet Application</title>
 </head>

 <body>
     <div class="container2 lang-{{ app()->getLocale() }}">
         <div class=" global">
             <div class="loginInner">
                 <a class="logout" href="{{ LaravelLocalization::localizeUrl('logout') }}"
                     onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                     <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                 </a>
                 <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                     {{ csrf_field() }}
                 </form>
                 <a title="{{ __('Home') }}" class="logout" href="{{ route('home') }}">
                     <i class="fas fa-home"></i>
                 </a>
                 <a title="{{ __('ContactUs') }}" class="logout" href="{{ route('ContactUs') }}">
                     <i class="fas fa-file-signature"></i>
                 </a>
                 <?php
                 $current = '/' . app()->getLocale();
                 $next_lang = $current == '/en' ? '/ar' : '/en';
                 ?>
                 <a class="logout" title="{{ strtoupper(str_replace('/', '', $next_lang)) }}"
                     href="{{ url(str_replace($current, $next_lang, Request::fullUrl())) }}"><i
                         class="fas fa-globe"></i></a>
                 <a title="{{ __('Systems List') }}" class="logout" href="{{ env('ZAVERO') . app()->getLocale() }}">
                     <i class="fas fa-list"></i>
                 </a>

                 <a href="#">
                     <h2 class="text-left">{{ __('Table Results') }}</h2>
                 </a>
                 <br>
                 <div class="table-responsive">
                     <div class="seven">
                         <h1>Monthly Income Statement</h1>
                     </div>
                     {{-- <h3  class="text-black-50">Monthly Income Statement</h3> --}}
                     <table class="table table-hover text-center datatablejs" class="display" style="width: 100%;">
                         <thead>
                             <tr class="tr-color">
                                 <th class="tr-color text-center">Date</th>

                                 @foreach ($dates as $date)
                                     <th style="width: 60%;">{{ date('M/Y', strtotime($date)) }}</th>
                                 @endforeach
                             </tr>
                         </thead>
                         <tbody>
                             <tr class="tr-color">
                                 <th class="tr-color">(+) Sales</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$project_sales[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Raw Material Cost</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$products_rm_cost_total[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Labor Cost</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$products_labor_cost_total[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color"> (-) Manufacuring Overheads </th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$products_moh_cost_total[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Operation Deprication </th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$expenses['monthly_deprication'][$date] * 0.9) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Opening Deprication </th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$expenses['opening_monthly_deprication'][$date] * 0.9) }}
                                     </td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Cost Of Goods </th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$gross_profit_cost_of_product['cost_of_products'][$date]) }}
                                     </td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Gross Profit</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$gross_profit_cost_of_product['gross_profit'][$date]) }}
                                     </td>
                                 @endforeach
                             </tr>

                             <tr class="tr-color">
                                 <th class="tr-color">(-) Total Sales & Marketing</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$total_sales_markting[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Total Distribution Expense</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$total_distribution_expenses[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Total General Expenses</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$total_general_expenses[$date]) }}</td>
                                 @endforeach
                             </tr>

                             <tr class="tr-color">
                                 <th class="tr-color">(-) G&A Deprication </th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$expenses['monthly_deprication'][$date] * 0.1) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Opening Deprication </th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$expenses['opening_monthly_deprication'][$date] * 0.1) }}
                                     </td>
                                 @endforeach
                             </tr>



                             <tr class="tr-color">
                                 <th class="tr-color">(+/-) EBITDA</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$ebitda[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(+/-) EBIT</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$ebit[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(+/-) EBT</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$ebt[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) EBT Taxes</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$ebt_taxes[$date]) }}</td>
                                 @endforeach
                             </tr>

                             <tr class="tr-color">
                                 <th class="tr-color">(+/-) Net Profit</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$net_profit[$date]) }}</td>
                                 @endforeach
                             </tr>
                         </tbody>
                     </table>
                 </div>

                 <br>
                 <div class="table-responsive">
                    <div class="seven">
                         <h1>Monthly Cashflow</h1>
                     </div>
                     <table class="table table-hover text-center datatablejs" class="display" style="width: 100%;">
                         <thead>
                             <tr class="tr-color">
                                 <th class="tr-color  text-center">Date</th>

                                 @foreach ($dates as $date)
                                     <th style="width: 60%;">{{ date('M/Y', strtotime($date)) }}</th>
                                 @endforeach
                             </tr>
                         </thead>
                         <tbody>

                             <tr class="tr-color">
                                 <th class="tr-color">(+) Cash & Banks</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$cash_banks_balance[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(+) Sales collection</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$sales_collection[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                <th class="tr-color">(-) Opening Customers Collections</th>
                                @foreach ($dates as $date)
                                    <td class="h5 text-dark td-style">
                                        {{ number_format(@$opening_customers_collection[$date]) }}</td>
                                @endforeach
                            </tr>
                            <tr class="tr-color">
                                <th class="tr-color">(-) Opening Debtors Collections</th>
                                @foreach ($dates as $date)
                                    <td class="h5 text-dark td-style">
                                        {{ number_format(@$opening_debtors_collection[$date]) }}</td>
                                @endforeach
                            </tr>
                            <tr class="tr-color">
                                <th class="tr-color">Total Cashin Flow</th>
                                @foreach ($dates as $date)
                                <?php
                                    $total_cash_in_flow =    ($cash_banks_balance[$date]??0) +
                                                ($sales_collection[$date]??0) +
                                                ($opening_customers_collection[$date]??0) +
                                                ($opening_debtors_collection[$date]??0) ;
                                ?>
                                    <td class="h5 text-dark td-style">
                                        {{ number_format(@$total_cash_in_flow) }}</td>
                                @endforeach
                            </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Raw Materials Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$rm_purchase_payment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">

                                 <th class="tr-color">(-) Labor Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$products_payment_labor_cost_total[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Manufacuring Overheads Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$products_payment_moh_cost_total[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Salaries Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$salaries_payment[$date]) }}</td>
                                 @endforeach
                             </tr>




                             <tr class="tr-color">
                                 <th class="tr-color">(-) Monthly Markting Expense</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$monthly_markting_expense[$date]) }}</td>
                                 @endforeach
                             </tr>

                             <tr class="tr-color">
                                 <th class="tr-color">(-) Other G&A Expenses</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$other_expenses[$date]) }}
                                     </td>
                                 @endforeach
                             </tr>

                             <tr class="tr-color">
                                 <th class="tr-color">(-) Sales Commission Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$sales_commission_payment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Marketing Campaign</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$marketing_campaign[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Start Up Fees</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$start_up_feez[$date]) }}
                                     </td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Total Distribution Expense</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$total_distribution_expenses[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Monthly General Expenses Amount</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$monthly_general_expense[$date]) }}</td>
                                 @endforeach
                             </tr>
                             {{-- <tr class="tr-color">
                                <th class="tr-color">monthly_deprication</th>
                                @foreach ($dates as $date)
                                    <td class="h5 text-dark td-style">{{number_format((@$monthly_deprication[$date]))}}</td>
                                @endforeach
                            </tr> --}}
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Interest Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$interest_payment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Installment Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$variable_installment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Opening Interest Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$opening_interest_payment[$date]) }}</td>
                                 @endforeach
                             </tr>




                             <tr class="tr-color">
                                 <th class="tr-color">(-) Opening Suppliers Payments</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$opening_suppliers_payment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Opening Creditors Payments</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$opening_creditors_payment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Opening Loan Installment Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$opeanning_loan_installment_payment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Other Long Liabilities Payments</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$other_long_liabilities[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) EBT Taxes Payment</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$ebt_taxes_payment[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(-) Total Cash Outflow</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$total_investement_cash_out[$date]) }}</td>
                                 @endforeach
                             </tr>
                             {{-- investement_net_cashflow --}}
                             <tr class="tr-color">
                                 <th class="tr-color">(+/-) Monthly Net Cash Flow</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$investement_net_cashflow[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">(+/-) Accumulated Cash Outflow</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$acumulated_investement_net_cashflow[$date]) }}</td>
                                 @endforeach
                             </tr>
                             {{-- total_investement_cash_out --}}
                         </tbody>

                     </table>
                 </div>

                 <br>
                 <div class="table-responsive">
                    <div class="seven">
                         <h1>Monthly Balance Sheet</h1>
                     </div>
                     <table class="table table-hover text-center datatablejs" class="display" style="width: 100%;">

                         <thead>
                             <tr class="tr-color">
                                 <th class="tr-color  text-center">Date</th>

                                 @foreach ($dates as $date)
                                     <th style="width: 60%;">{{ date('M/Y', strtotime($date)) }}</th>
                                 @endforeach
                             </tr>
                         </thead>

                         <tbody>


                             <tr class="tr-color">
                                 <th class="tr-color">Fixed Assets Gross Value</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_fixed_assets[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Accumulated Deprication</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_accumulated_deprication[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Net Fixed Assets</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_net_fixed_assets[$date]) }}</td>
                                 @endforeach
                             </tr>

                             <tr class="tr-color">
                                 <th class="tr-color">Cash & Banks Balance</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_cash_and_banks[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Customers Receivables</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_customers_receivables[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Inventory</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_inventory[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Other Debtors</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_other_debtors[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Total Current Assets</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_total_current_assets[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Total Assets</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_total_assets[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Suppliers Payables</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_suppliers_payables[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Other Creditors</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_other_creditors[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Total Current Liabilities</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_total_current_liabilities[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Long Term Loans</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_long_term_loans[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Other Long Term Liabilities</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_other_long_liabilities[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Total Long Term Liabilities</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_total_long_liabilities[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Paid up Capital</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_paid_up_capital[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Additional Paid up Capital</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_additional_paid_up_capital[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Retained Earning</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$retained_earning[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Profit of the Period</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">{{ number_format(@$net_profit[$date]) }}</td>
                                 @endforeach
                             </tr>
                             <tr class="tr-color">
                                 <th class="tr-color">Total Owners Equity</th>
                                 @foreach ($dates as $date)
                                     <td class="h5 text-dark td-style">
                                         {{ number_format(@$balance_sheet_total_owners_equity[$date]) }}</td>
                                 @endforeach
                             </tr>

                         </tbody>






                     </table>
                 </div>
             </div>
         </div>
     </div>

     <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
     <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
     <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
     <script>
         $(document).ready(function() {
             $('.datatablejs').DataTable({
                 dom: 'Bfrtip',
                 paging: false,
                 scrollX: true,
                 ordering: false,
                 buttons: [
                     'copy', 'csv', 'excel', 'pdf', 'print'
                 ]
             });
         });
     </script>
 </body>

 </html>
 </body>

 </html>
