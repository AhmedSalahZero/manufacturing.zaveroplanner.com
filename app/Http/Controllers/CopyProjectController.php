<?php

namespace App\Http\Controllers;

use App\Project;
use App\Sharing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CopyProjectController extends Controller
{
    public function index(Request $request, Project $project)
    {
       $id = $project->id ;
		$newProject = $project->replicate(['id']);
		$newProject->name = $request->get('name');

		$tablesWithOnlyHospitalitySectorAsForeignKey =getTableNamesThatHasColumn('project_id') ;
		
		$newProject->save();
		
		foreach( $tablesWithOnlyHospitalitySectorAsForeignKey as $tableName){
			
			$rows = DB::table($tableName)->where('project_id', $id)->get(); // استرجاع الصف ككائن stdClass
			foreach($rows as $row){
				$data = (array) $row; // تحويله إلى مصفوفة
				unset($data['id']); // حذف الـ id حتى لا يحدث تعارض (أو المفتاح الأساسي)
				$data['project_id'] = $newProject->id ; 
				DB::table($tableName)->insert($data); // إدراج نسخة جديدة
			}
			
		}
		return redirect()->back()->with('success',__('Done!'));
    }
  
}
