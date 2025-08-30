<?php

namespace App\Http\Controllers;

use App\Project;
use App\Sharing;
use Illuminate\Http\Request;

class SharingController extends Controller
{
    public function index(Project $project)
    {
        $project = $project->load(['sharingLinks'=>function($query){
            $query->orderBy('closed');
        }]);

        return view('sharing',compact('project'));
    }
    public function newLink(Request $request, Project $project)
    {
        $request['project_id'] = $project->id;
        Sharing::create($request->all());
        return redirect()->back();
    }
    public function changeStatus(Project $project,Sharing $sharing)
    {
        $sharing->closed = $sharing->closed == 0 ? 1 : 0;
        $sharing->save();
        return redirect()->back();
    }
}
