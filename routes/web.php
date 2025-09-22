<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteProductProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['lang']
    ],
    function () {

        // Route::get('project_slug', function () {
        //     $projects = App\Project::all();
        //     foreach ($projects as $key => $project) {
        //         $project->slug = \Str::slug($project->name, '-');
        //         $project->save();
        //     }
        //     return 'Done';
        // });
        Route::get('projects_by_user', function () {
            $users = App\User::with('projects')->get()->sortByDesc(function($user)
            {
                return $user->projects->count();
            });

            return view('users_projects',compact('users'));
        })->name('all.users.projects');
        ###################### Viewing Data Views ########################################
        Route::prefix('{project}')->group(function () {
            //Study Info
            Route::get('/study_info/view', 'ViewingDataController@viewStudy')->name('study_info.view');
            //Products
            Route::get('/{type}/products/view', 'ViewingDataController@viewProducts')->name('products.view');
            //Backlog
            // Route::get('/backlog/view','ViewingDataController@viewBacklog')->name('backlog.view');
            //Manpower Plan
            Route::get('/manpower_plan/view', 'ViewingDataController@viewManpowerPlan')->name('manpower_plan.view');
            //Expenses Plan
            Route::get('/expenses_plan/view', 'ViewingDataController@viewExpensesPlan')->name('expenses_plan.view');
            //Assets Plan
            Route::get('/assets_plan/view', 'ViewingDataController@viewAssetsPlan')->name('assets_plan.view');
            //Opening Balances
            Route::get('/opening_balances/view', 'ViewingDataController@viewOpeningBalances')->name('opening_balances.view');
            //Study Result
            Route::get('/study_results/view', 'DashboardController@index')->name('study_result.view');
            ########################## Contact Project Owner ###############################################
            //ContactUs
            Route::get('/ContactProjectOwner', 'ContactUsController@getViewContactProjectOwner')->name('ContactProjectOwner');
            Route::post('/Send_ContactProjectOwner', 'ContactUsController@sendMessageContactProjectOwner')->name('Send_ContactProjectOwner');
        });
        #################################################################################




        Auth::routes(['verify' => true]);

        Route::any('User_login/{token}/{id}', function (Request $request, $token, $id) {
            Auth::loginUsingId($id);
            if (Auth::check()) {
                return redirect()->route('home');
            }
        });

		             //Ajax
                Route::get('/get_date', 'HomeController@getdate')->name('get.date');
				
        Route::group(['middleware' => ['auth']], function () {

            //Privacy Policy
            Route::get('PrivacyPolicy', 'HomeController@privacyPolicy')->name('policy');
            Route::post('PrivacyPolicy/submit', 'HomeController@privacyPolicySubmit')->name('policy.submit');
            //  Route::get('delete_account', 'UsersController@deleteAccount')->name('delete_account');
            Route::group(['middleware' => ['PrivacyPolicy', 'user_projects']], function () {
                Route::get('/', 'HomeController@index')->name('home');
                Route::resource('projects', 'ProjectController', ['except' => 'show']);
                Route::get('/sharingPage/{project}', 'SharingController@index')->name('sharing.page');
                Route::post('/sharingLink/{project}', 'SharingController@newLink')->name('sharingLink.store');
                Route::get('/sharingLinkStatus/{project}/{sharing}', 'SharingController@changeStatus')->name('sharingLink.status');
				
                Route::post('/copy-project/{project}', 'CopyProjectController@index')->name('copy.project');
                //Team Capacity
                Route::post('/duration_year', 'ProjectController@durationYear')->name('duration.year');
                Route::post('/business_sector', 'BusinessSectorController@businessSectorChildren')->name('businessSectorChildren');
   
                //ContactUs
                Route::get('ContactUs', 'ContactUsController@getView')->name('ContactUs');
                Route::post('Send_ContactUs', 'ContactUsController@sendMessage')->name('Send_ContactUs');
                /***Prefix Project***/
                Route::group(['prefix' => '{project}'], function () {

                    Route::get('/mainProjectPage', 'HomeController@mainProjectPage')->name('main.project.page');
                    # Back Log
                    Route::get('/backlog', 'RedirectionController@backLogGet')->name('backLog.form');
                    Route::post('/backLog/store', 'RedirectionController@backLogPost')->name('backlog.submit');
                    # Expenses
                    Route::get('/expenses', 'RedirectionController@expensesGet')->name('expenses.form');
                    Route::post('/expenses', 'RedirectionController@expensesPost')->name('expenses.submit');
					 # Fixed Assets
					 Route::get('/fixed-assets', 'RedirectionController@fixedAssetsGet')->name('fixed.assets.form');
					 Route::post('/fixed-assets', 'RedirectionController@fixedAssetsPost')->name('assets.submit');
					 
					          # Opening Balances
                    Route::get('/opening-balances', 'RedirectionController@openingBalancesGet')->name('openingBalances.form');
                    Route::post('/opening-balances', 'RedirectionController@openingBalancesPost')->name('openingBalances.submit');
					
					 # Assets
					 Route::get('/financial-results', 'RedirectionController@financialResultsGet')->name('financial.result');
					 Route::get('/cash-in-out-flow', 'RedirectionController@cashInOutFlowGet')->name('cash.in.out.flow.result');
					 Route::get('/balance-sheet', 'RedirectionController@balanceSheetGet')->name('balance.sheet.result');
					 route::get('dashboard','DashboardController@view')->name('view.results.dashboard');
					 route::post('dashboard','DashboardController@submit')->name('submit.results.dashboard');
					 route::get('dashboard-with-sensitivity','DashboardController@view')->name('view.results.dashboard.with.sensitivity');
	
           
                    # Man Power
                    Route::get('/manPower', 'RedirectionController@manPowerGet')->name('manPower.form');
                    Route::post('/manPower/store', 'RedirectionController@manPowerPost')->name('manPower.submit');
                    # Sensitivity
                    Route::get('/sensitivity', 'RedirectionController@sensitivityGet')->name('sensitivity.form');
                    Route::post('/sensitivity/store', 'RedirectionController@sensitivityPost')->name('sensitivity.submit');
                    # Dashboards
                    // Route::get('/dashboard', 'DashboardController@index')->name('dashboard.index');
                    // Route::get('/table_dashboard', 'DashboardController@index')->name('table.index');
              //      Route::get('/RecurringDashboard', 'RecurringProductDashboardController@index')->name('recurring.dashboard.index');
                    Route::get('/ExpensesDashboard', 'ExpensesDashboardController@index')->name('expenses.dashboard.index');
                    Route::group(['prefix' => '/products/{product}'], function () {
                        Route::get('/', 'RedirectionController@productsGet')->name('products.form');
                        Route::post('/', 'RedirectionController@productsPost')->name('products.submit');
                    });
					
					Route::get('raw-material-payments', 'RedirectionController@rawMaterialPaymentsGet')->name('raw.material.payments.form');
					Route::post('raw-material-payments', 'RedirectionController@rawMaterialPaymentsPost')->name('raw.material.payments.submit');
					
					
                });
            });
        });
    }
);

Route::get('/generate-report', 'ReportController@generatePdf')->name('generate.report');
