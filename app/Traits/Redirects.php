<?php
namespace App\Traits;

use App\Http\Controllers\ProjectController;

class Redirects
    {
        public function redirectFun($project)
        {
            $type =null;
            if ($project->product_first) {
                $type = 'product_first';
            }elseif ($project->product_second ) {
                $type = 'product_second';
            }elseif ($project->product_third ) {
                $type = 'product_third';
            }elseif ($project->product_fourth) {
                $type = 'product_fourth';
            } elseif ($project->product_fifth) {
                $type = 'product_fifth';
            }

            if($type == 'product_first' || $type == 'product_second' || $type == 'product_third' || $type == 'product_fourth' || $type == 'product_fifth'){
                $route = redirect()->route('products.form',[$project,$type]);
            }else{
                $route = redirect()->route('manPower.form',$project);
            }
            $array = [
                'route' => $route,
                'type' => $type
            ];
            return $array;
        }

        public function forms($project)
        {
            $forms=[];
            if ($project->product_first) {
                array_push($forms,'product_first');
            }
            if ($project->product_second ) {
                array_push($forms,'product_second');
            }
            if ($project->product_third ) {
                array_push($forms,'product_third');
            }
            if ($project->product_fourth) {
                array_push($forms,'product_fourth');
            }
            if ($project->product_fifth) {
                array_push($forms,'product_fifth');
            }


            return $forms;
        }


        public function steps($project,$step)
        {
            $forms=[];
            $forms[] =  ['route' => 'edit', 'name' => "Study Info"];
            if ($project->product_first) {
                $forms[] =  ['route' => 'product_first', 'name' => $project->product_first];
            }
            if ($project->product_second ) {
                $forms[] =  ['route' => 'product_second', 'name' => $project->product_second];
            }
            if ($project->product_third ) {
                $forms[] =  ['route' => 'product_third', 'name' => $project->product_third];
            }
            if ($project->product_fourth) {
                $forms[] =  ['route' => 'product_fourth', 'name' => $project->product_fourth];
            }
            if ($project->product_fifth) {
                $forms[] =  ['route' => 'product_fifth', 'name' => $project->product_fifth];
            }

            $forms[] =  ['route' => 'manPower', 'name' => 'Manpower Plan'];
            $forms[] =  ['route' => 'expenses', 'name' => 'Expenses Plan'];
            $forms[] =  ['route' => 'assets', 'name' => 'Assets Plan'];
            if ($project->new_company == 0 ) {
                $forms[] =  ['route' => 'openingBalances', 'name' => 'Opening Balances'];
            }
            $number = @count($forms);
            $step_index = array_search($step,array_column($forms,'route'));

            $data =[
                'count' => $number,
                'place_num' =>$step_index+1,
                'route_name' =>$forms[$step_index]['name'],
            ];

            return $data;

        }

        public function productsforms($project)
        {
            $forms=[];

            if ($project->product_first) {
                array_push($forms,'product_first');
            }
            if ($project->product_second) {
                array_push($forms,'product_second');
            }
            if ($project->product_third ) {
                array_push($forms,'product_third');
            }
            if ($project->product_fourth) {
                array_push($forms,'product_fourth');
            }
            if ($project->product_fifth) {
                array_push($forms,'product_fifth');
            }



           return $forms;
        }

        public function completedproducts($project)
        {
            $forms = [];
            $products = ['product_first','product_second','product_third','product_fourth','product_fifth' ];
            $product_controller_obj = (new ProjectController);

            foreach ($products as $key => $product_name) {
                $rm_cost_rates = [];
                $labor_cost_rates = [];
                $moh_cost_rates = [];
                $product = $project->product($product_name);

                if(isset($product) ) {
                    $years = $product_controller_obj->years($project,null,$product_name);
                    foreach ($years as $key => $year) {
                        $name_of_year = array_key_first($year);
                        $year_num = $year[$name_of_year];
                        $rm_cost_rate_field = 'rm_cost_'.$name_of_year.'_rate' ;
                        $labor_cost_rate_field = 'labor_cost_'.$name_of_year.'_rate' ;
                        $moh_cost_rate_field = 'moh_cost_'.$name_of_year.'_rate' ;
                        $rm_cost_rates[$year_num] = $product->$rm_cost_rate_field;
                        $labor_cost_rates[$year_num] = $product->$labor_cost_rate_field;
                        $moh_cost_rates[$year_num] = $product->$moh_cost_rate_field;
                    }

                    $any_nulls_in_rm_cost_rates = array_search(null ,$rm_cost_rates);
                    $any_nulls_in_labor_cost_rates = array_search(null,$labor_cost_rates);
                    $any_nulls_in_moh_cost_rates = array_search(null,$moh_cost_rates);
                    ($product->seasonality != null && $product->first_contract!= null && false === $any_nulls_in_rm_cost_rates  &&
                    false === $any_nulls_in_labor_cost_rates && false === $any_nulls_in_moh_cost_rates) ? array_push($forms,$product_name): '' ;
                }
            }
           
            return $forms;
        }
        public function foundedProducts($project)
        {
            // $products = ['product_first','product_second','product_third','product_fourth','product_fifth' ];
            // foreach ($products as $key => $product_name) {
            //     $product = $project->product($product_name);
            //     if(isset($product) && ($project->$product_name) == null) {
            //         $product->delete();
            //     }
            // }
        }

    }


?>
