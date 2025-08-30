<?php

namespace App\Http\Controllers;

use App\Project;
use App\Providers\RouteServiceProvider;
use App\Traits\Redirects;
use Auth;
use Illuminate\Http\Request;
class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $projects = auth()->user()->projects()->orderBy('id','DESC')->get();
        return view('index')->with('projects' , $projects);
    }
    public function mainProjectPage(Project $project)
    {
        // $products = (new Redirects)->forms($project,true);
		$products = $project->products ;

        return view('project_main_page',compact('project','products'));
    }
    public function privacyPolicy()
    {
        return view('privacy_policy');
    }
    public function privacyPolicySubmit(Request $request)
    {
        $user = auth()->user();
        isset($request->acceptance_of_privacy_policy) && $request->acceptance_of_privacy_policy == 1 ? $user->acceptance_of_privacy_policy = 1 : $user->acceptance_of_privacy_policy = 0 ;
        $user->save();
        return redirect(env('ZAVERO'));
    }
    public function getdate(Request $request)
    {

        $month = $request->month;
        $year = $request->year;
        $date = "01-".$month."-".$year;
        $duration = $request->duration != 0 ? ($request->duration*12)-1 : 0;
        $full_date 	= date("Y-m-01",strtotime($date));
        $view_date = date("12/Y",strtotime(date("Y-m-d", strtotime($date)) . " +$duration  month"));
        $end_date = date("Y-m-01",strtotime(date("Y-m-d", strtotime($date)) . " +$duration  month"));
        return ['full_date'=>$full_date,'view_date' =>$view_date,'end_date'=>$end_date] ;
    }
}
