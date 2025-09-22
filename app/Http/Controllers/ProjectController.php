<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeProjectDetailRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $project = auth()->user()->projects->orderBy('id','DESC')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {

        Project::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name, '-'),
            'user_id' => auth()->user()->id,
        ]);
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $project->getViewVars();
        return view('projects.edit',$project->getViewVars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(storeProjectDetailRequest $request,Project $project)
    {
		/**
		 * @var Project $project
		 */
		// $result = $this->Validation($request,$project);
			$inputs = $request->all();
			$inputs['user_id'] = auth()->user()->id;
			$request['slug'] =  Str::slug($request->name, '-');
			$request['is_completed'] =  1;
			$except = ['products','rawMaterials','submit_button','selling_start_date'];
			
			$project->update($request->except($except));
			
			$datesAsStringAndIndex = $project->getDatesAsStringAndIndex();
			
			$datesAndIndexesHelpers = $project->datesAndIndexesHelpers();
			$datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex']; 
			$yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear']; 
			$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate']; 
			$dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber']; 
			$project->updateStudyAndOperationDates($datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate,$dateWithMonthNumber);
			
			
			$products = [];
			foreach($request->get('products') as $index=>&$productArr){
				$productArr['vat_rate'] = $productArr['vat_rate']?:0;
				$productArr['withhold_tax_rate'] = $productArr['withhold_tax_rate']?:0;
				$productArr['selling_start_date'] = $productArr['selling_start_date'].'-01';
				$productArr['selling_start_date'] = $project->getIndexDateFromString($productArr['selling_start_date']);
				$products[$index] = $productArr;
			}
		
			$request->merge([
				'products'=>$products
			]);
			
			$except =array_merge($except,['submit_button','month','year','_token']) ;
			$project->storeRepeaterRelations($request,['products','rawMaterials']);
			
			
			
		
			
			if ($request->new_company == 1) {
				isset($project->openingBalance) ? $project->openingBalance()->delete() : '';
			}
			if($request->get('submit_button') != 'next'){
			return redirect()->route('main.project.page',['project'=>$project->id]);
		}
		
			return redirect()->route('products.form',['project'=>$project->id,'product'=>$project->products->first()->id]);
			
	
    }


    public function durationYear(Request $request)
    {
        $duration = $request->duration;
        $fullDate = '1-'.$request->month.'-'.$request->year;
        $duration_date = date("12/Y", strtotime('-1 month' . $duration . 'year', strtotime($fullDate)));
        $original_date = date("Y-12-01", strtotime('-1 month' . $duration . 'year', strtotime($fullDate)));
        $years = [];
        for ($year=0; $year < 5; $year++) {

            $date = date("Y",strtotime('+' . $year . 'year', strtotime($fullDate)));
            array_push($years , $date );
        }
        return [
            'original_date' => $original_date,
            'duration' => $duration_date,
            'years' => $years,
        ];

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->back();
    }

    /**************** First Form Get **********************/
    public function firstFormGet($id , $type)
    {
		$project = Project::findOrFail($id);
        if($project->product_first != null ){
            $type = 'first';
        }
        return view('products.form')->with('project' , $project)->with('type' , $type);
    }

    public function firstFormPost($id)
    {
        $project = Project::findOrFail($id);
        $route = redirect()->route('expenses.form',['project_id'=> $project , 'type' => 'first']);
        return $route;
    }
    public function Validation($request,$data)
    {
         if($request->submit_button == "next" ){
			 $project_product_validation = ($request->product_first == null) && ($request->product_second == null) && ($request->product_third == null) && ($request->product_fourth == null) && ($request->product_fifth == null) ? 'required' : '';
			 $project_product_validation = '' ;

              $validation['month'] = 'required';
                $validation['year'] = 'required';
                $validation['duration'] = 'required';
                // $validation['selling_start_month'] ='required';
                // $validation['selling_start_year'] ='required';

                $products_selling_dates_fields = ['product_first_selling_'=>'product_first_selling_date','product_second_selling_'=>'product_second_selling_date','product_third_selling_'=>'product_third_selling_date','product_fourth_selling_'=>'product_fourth_selling_date','product_fifth_selling_'=>'product_fifth_selling_date'];
                $except = [];
                if(isset($request->start_date)){

                    foreach ($products_selling_dates_fields as $main_field_name => $field) {
                        $name_of_new_field_month = $main_field_name.'month';
                        $name_of_new_field_year = $main_field_name.'year';
                        $name_of_new_field_product = str_replace('_selling_','',$main_field_name);
                        $product_name = $request->$name_of_new_field_product;
                        if (isset($product_name)) {

                            $request->$name_of_new_field_year == null || $request->$name_of_new_field_month == null ? null : $request[$field] = $request->$name_of_new_field_year."-".$request->$name_of_new_field_month."-01";

                            $validation[$field] = "after_or_equal:".$request->start_date."|before_or_equal:".$request->end_date ;
                        }



                    }
                }

                $validation['tax_rate'] ='required';
                // $validation['land_cost'] ='required';
                // $validation['installment_count'] = "nullable|numeric|min:1";
                // if(isset($request->start_date)){
                //     $validation['selling_start_date'] = "after_or_equal:".$request->start_date."|before_or_equal:".$request->end_date ;

                // }
                $validation['project_product_validation'] = $project_product_validation;

            $this->validate($request,@$validation,[
                'project_product_validation.required' => __('You must fill at least one product'),

                'product_first_selling_date.after_or_equal' => __('The selling start date must be after or equal to ') .date('m/Y',strtotime($request->start_date)) ,
                'product_first_selling_date.before_or_equal' => __('The selling start date must be before or equal to ') .date('m/Y',strtotime($request->end_date)) ,
                'product_second_selling_date.after_or_equal' => __('The selling start date must be after or equal to ') .date('m/Y',strtotime($request->start_date)) ,
                'product_second_selling_date.before_or_equal' => __('The selling start date must be before or equal to ') .date('m/Y',strtotime($request->end_date)) ,
                'product_third_selling_date.after_or_equal' => __('The selling start date must be after or equal to ') .date('m/Y',strtotime($request->start_date)) ,
                'product_third_selling_date.before_or_equal' => __('The selling start date must be before or equal to ') .date('m/Y',strtotime($request->end_date)) ,
                'product_fourth_selling_date.after_or_equal' => __('The selling start date must be after or equal to ') .date('m/Y',strtotime($request->start_date)) ,
                'product_fourth_selling_date.before_or_equal' => __('The selling start date must be before or equal to ') .date('m/Y',strtotime($request->end_date)) ,
                'product_fifth_selling_date.after_or_equal' => __('The selling start date must be after or equal to ') .date('m/Y',strtotime($request->start_date)) ,
                'product_fifth_selling_date.before_or_equal' => __('The selling start date must be before or equal to ') .date('m/Y',strtotime($request->end_date)) ,
            ]);
        }elseif ($request->submit_button == "save") {
            return ['redirect_route' => redirect()->route('main.project.page',[$data->id])];
        }

    }
}
