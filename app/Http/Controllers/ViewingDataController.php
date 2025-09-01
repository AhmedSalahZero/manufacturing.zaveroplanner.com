<?php

namespace App\Http\Controllers;

use App\Project;
use App\SharingLinkVisitor;
use App\Traits\ProjectTrait;
use App\Traits\Redirects;
use Illuminate\Http\Request;

class ViewingDataController extends Controller
{
    use ProjectTrait;
    public function viewStudy(Request $request ,$slug)
    {
        $project = $this->project($slug);
        // $products = (new Redirects)->completedproducts($project);
		// $products = $project->products;
        $user_ip = $request->ip() ;

        $found_user = SharingLinkVisitor::where('link_code',$slug)->where('ip',$user_ip)->first();
        if ($found_user === null) {
            SharingLinkVisitor::create(['link_code'=>$slug,'ip'=>$user_ip]);
        }
		// dd($project);
		return view('view-study-info',['project'=>$project]);
        // return view('viewing.study_info',compact('project','products','slug'));
    }

    public function viewManpowerPlan($slug)
    {
        $project = $this->project($slug);
        $products = (new Redirects)->completedproducts($project);
        $expenses_array = ['sales_salaries'=>'sales','operational_salaries'=>'operational_salaries','general_salaries'=>'general'];
        $years = (new ProjectController)->years($project,($project->new_company == 0 ?'project' : 'min_selling_date'));
        $manPower = $project->manPower;
        return view('viewing.manpower_plan',compact('project','manPower','products','years','expenses_array','slug'));
    }
    public function viewExpensesPlan($slug)
    {
        $project = $this->project($slug);
        $expense = $project->expense;
        $products = (new Redirects)->completedproducts($project);
        $years = (new ProjectController)->years($project,$project->new_company == 0 ?'project' : 'min_selling_date');
        return view('viewing.expenses_plan',compact('years','project','products','expense','slug'));
    }
    public function viewAssetsPlan($slug)
    {
        $project = $this->project($slug);
        $assets = $project->assets;
        $products = (new Redirects)->completedproducts($project);
        return view('viewing.assets_plan',compact('project','products','assets','slug'));
    }
    public function viewOpeningBalances($slug)
    {
        $project = $this->project($slug);
        $openning = $project->openingBalance;
        $products = (new Redirects)->completedproducts($project);
        $products = (new Redirects)->productsforms($project,'without-backlog');
        $years = (new ProjectController)->years($project);
        return view('viewing.opening_balances',compact('project','years','products','openning','years','slug'));
    }
    public function viewProducts($slug,$type)
    {
        $project = $this->project($slug);
        $products = (new Redirects)->completedproducts($project);
        $sesonalities = ['flat'=>__('Flat Monthly'),
                        'quarterly'=>__('Distribute Quarterly'),
                        'monthly'=>__('Distribute Monthly')
                        ];

        $product = $project->product($type);
        $years = (new ProjectController)->years($project,null,$type);

        return view('viewing.products',compact('project','products','product','years','type','sesonalities','slug'));
    }
}
