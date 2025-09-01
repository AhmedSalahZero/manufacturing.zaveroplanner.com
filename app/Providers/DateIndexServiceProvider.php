<?php 
namespace App\Providers;

use App\Project;
use App\Traits\ProjectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class DateIndexServiceProvider extends ServiceProvider
{
	use ProjectTrait;
	public function register()
	{
		
	}
	public function boot(Request $request) 
	{
		$yearIndexWithYear = [];
		$dateIndexWithDate = [];
		$dateWithDateIndex = [];
		
		$projectId = Request()->segment(3);
		$projectId =is_string($projectId) ? Request()->segment(2) : $projectId;
		/**
		 * * start in case of line sharing
		 */
		if(!is_numeric($projectId) && $projectId != 'projects' && DB::table('sharing_links')->where('link_code',$projectId)->first()){
			$projectId =is_string($projectId) ? $this->project(Request()->segment(2)) : null;
			if(!is_null($projectId) && $projectId instanceof Project){
				$projectId = $projectId->id ; 
			}
		}
		/**
		 * * end in case of line sharing
		 */
		if(is_numeric($projectId)){
			$project = Project::find($projectId);
			/**
			 * @var Project $project 
			 */
			if($project){
				$datesAndIndexesHelpers = $project->getDatesIndexesHelper();
				$datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex']; 
				$yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear']; 
				$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate']; 
				$dateIndexWithMonthNumber=$datesAndIndexesHelpers['dateIndexWithMonthNumber']; 
				$dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber']; 
				$dateWithDateIndex=$datesAndIndexesHelpers['dateWithDateIndex']; 
				app()->singleton('datesIndexWithYearIndex',function() use ($datesIndexWithYearIndex){
					return $datesIndexWithYearIndex;
				});
				app()->singleton('yearIndexWithYear',function() use ($yearIndexWithYear){
					return $yearIndexWithYear;
				});
				app()->singleton('dateIndexWithDate',function() use ($dateIndexWithDate){
					return $dateIndexWithDate;
				});
				app()->singleton('dateWithMonthNumber',function() use ($dateWithMonthNumber){
					return $dateWithMonthNumber;
				});
				app()->singleton('dateIndexWithMonthNumber',function() use ($dateIndexWithMonthNumber){
					return $dateIndexWithMonthNumber;
				});
				app()->singleton('dateWithDateIndex',function() use ($dateWithDateIndex){
					return $dateWithDateIndex;
				});
				foreach([
					// [0 => '']
					'datesIndexWithYearIndex'=>$datesIndexWithYearIndex , 
					'yearIndexWithYear'=>$yearIndexWithYear 
				// ,'dateIndexWithDate'=>$dateIndexWithDate 
				// ,'dateWithMonthNumber'=>$dateWithMonthNumber
				,'dateIndexWithMonthNumber'=>$dateIndexWithMonthNumber
				// ,'dateWithDateIndex'=>$dateWithDateIndex
				 ] as $key => $dateArr){
					View::share($key,$dateArr);
				}
			}
			
		}
	}
		
}
