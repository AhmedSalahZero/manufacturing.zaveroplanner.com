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
}
