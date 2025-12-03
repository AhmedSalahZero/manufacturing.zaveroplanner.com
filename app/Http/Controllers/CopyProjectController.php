<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Project;
use App\Seasonality;
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
		$tablesWithForeignKeysColumns = [
			'products'=>['product_id','products'],
			'raw_materials'=>['raw_material_id'],
			'fixed_assets'=>['fixed_asset_id']
		];
		/**
		 * * وليكن مثلا الاي دي القديم بتاع المنتج بقي ايه في النسخه الجديدة
		 */
		$newColumnsIdsMapping = [];
		foreach( $tablesWithOnlyHospitalitySectorAsForeignKey as $tableName){
			
			$rows = DB::table($tableName)->where('project_id', $id)->get(); // استرجاع الصف ككائن stdClass
			foreach($rows as $row){
				$data = (array) $row; // تحويله إلى مصفوفة
				$oldRowId = $data['id'];
				unset($data['id']); // حذف الـ id حتى لا يحدث تعارض (أو المفتاح الأساسي)
				$data['project_id'] = $newProject->id ; 
				$newRowId=DB::table($tableName)->insertGetId($data); // إدراج نسخة جديدة
				if(in_array($tableName,array_keys($tablesWithForeignKeysColumns))){
					$newColumnsIdsMapping[$tableName][$oldRowId] = $newRowId ;
				}
			}
			
		}
		foreach($tablesWithForeignKeysColumns as $tableName => $columnNames){
			foreach($columnNames as $columnName){
				$tables = getTableNamesThatHasColumn($columnName);
				foreach($tables as $tableN){
					$oldValues = $newColumnsIdsMapping[$tableName]??[] ;
					foreach($oldValues as $oldValue => $newValue){
						DB::table($tableN)->where('project_id',$newProject->id)->where($columnName,$oldValue)->update([$columnName=>$newValue]);
					}
				}
			}
		}
		$seasonality = Seasonality::where('project_id',$newProject->id)->where('model_name','Product')->get();
		foreach($seasonality as $season){
			$val= $newColumnsIdsMapping['products'][$season->model_id]??null;
			if($val){
				$season->update([
					'model_id'=>$val
				]);
				
			}
		}
		
		$expenses = Expense::where('project_id',$newProject->id)->get();
		foreach($expenses as $expense){
			$oldProductIds = $expense->getProductArr();
			if(count($oldProductIds)){
				$newIds = [];
				foreach($oldProductIds as $oldProductId){
					$newIds[] = (int)$newColumnsIdsMapping['products'][$oldProductId];
				}
				$expense->update([
					'products'=>$newIds
				]);
				
			}
		}
		
		
		return redirect()->back()->with('success',__('Done!'));
    }
  
}
