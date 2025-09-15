<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;

use App\Project;
use Illuminate\Http\Request;


class DashboardController extends Controller
{

	

	public function view(Request $request ,Project $project)
	{
		
		return view('dashboard.dashboard',$project->getDashboardViewVars());
	}
	public function submit(Request $request ,Project $project)
	{
		$project->update([
			'dashboard_comment_1'=>$request->get('dashboard_comment_1'),
			'dashboard_comment_2'=>$request->get('dashboard_comment_2'),
		]);
		
		 if ($request->get('submit_button') == 'save') {
            return redirect()->route('view.results.dashboard', ['project'=>$project->id]);
        }
		return redirect()->route('main.project.page', ['project'=>$project->id]);
		
	}
	
}
