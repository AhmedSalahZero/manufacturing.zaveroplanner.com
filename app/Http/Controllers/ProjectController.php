<?php

namespace App\Http\Controllers;

use App\BusinessSector;
use App\Http\Requests\storeProjectDetailRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Project;
use App\Traits\Redirects;
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
        //Start date
      //  $request->year == null || $request->month == null ? null : $request['start_date'] = $request->year."-".$request->month."-01";
        // Selling Start date
        // $request->selling_start_year == null || $request->selling_start_month == null ? null : $request['selling_start_date'] = $request->selling_start_year."-".$request->selling_start_month."-01";
        // $products_selling_dates_fields = ['product_first_selling_'=>'product_first_selling_date','product_second_selling_'=>'product_second_selling_date','product_third_selling_'=>'product_third_selling_date','product_fourth_selling_'=>'product_fourth_selling_date','product_fifth_selling_'=>'product_fifth_selling_date'];
        $except = ['products','rawMaterials','submit_button','selling_start_date'];
        // foreach ($products_selling_dates_fields as $main_field_name => $field) {
			//     $name_of_new_field_month = $main_field_name.'month';
			//     $name_of_new_field_year = $main_field_name.'year';
			//     array_push($except,$name_of_new_field_month);
			//     array_push($except,$name_of_new_field_year);
			//     $request->$name_of_new_field_year == null || $request->$name_of_new_field_month == null ? null : $request[$field] = $request->$name_of_new_field_year."-".$request->$name_of_new_field_month."-01";
			// }
			// 'selling_start_month'  ,'selling_start_year'
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
			// (new Redirects)->foundedProducts($project);
			// if(isset($result['redirect_route'])){
			// 	return $result['redirect_route'] ;
			// }
			return redirect()->route('products.form',['project'=>$project->id,'product'=>$project->products->first()->id]);
			
	
    }
    // public function years($project,$requested_from=null,$type=null)
    // {
    //     $start_date = $project->start_date;

    //     $duration = date("m", strtotime($project->start_date)) == 1 ?  $project->duration : $project->duration+1;
    //     $duration = $duration - (date("Y", strtotime($start_date))-date("Y", strtotime($project->start_date)));


    //     $type = str_replace('product_','',$type);

    //     $years = [];
    //     $counter= ['first','second','third','fourth','fifth','sixth'];

    //     $count = ($type !== null) ?array_search($type,$counter) : 0;
    //     $name_selling_start_date = ($type !== null) ? "product_".$counter[$count]."_selling_date" :'';

    //     $duration_year = date("Y",strtotime(date("Y-m-d", strtotime($start_date)) . "+ ".($duration-1) ."  year"));
    //     if ($requested_from == "Dashboard"  ) {
    //         $start_date = $project->start_date;
    //     }elseif ($requested_from == 'min_selling_date' || $requested_from == 'full_min_selling_date') {
    //         $dates = [];
    //         foreach ($counter as $value) {
    //             $field = "product_".$value."_selling_date"  ;
    //             $date = $project->$field;

    //             $date === null ?:$dates[$value] = strtotime($date);
    //         }

    //         $start_date = date('Y-m-d',min($dates));

    //     }elseif ($requested_from == 'project') {
    //         $start_date = $project->start_date;

    //     }
    //     else {
    //         $start_date = $project->$name_selling_start_date;
    //     }

    //     $key = 0;
    //     if ($requested_from == 'full_min_selling_date') {
    //         return $start_date;
    //     }
    //     for ($year=0; $year < $duration ; $year++) {


    //         $date =  date("Y",strtotime(date("Y-m-d", strtotime($start_date)) . "+$year  year"));
    //         if (strtotime($date) <= strtotime($duration_year)) {
    //             if($requested_from == "Dashboard" || $requested_from == "Dashboard_sales" ){
    //                 $years[$date] = $counter[$key];
    //             }else{
    //                 $years[$key] = [$counter[$key]=>$date];
    //             }
    //             $key++;
    //         }
    //     }

    //     return $years;

    // }


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
