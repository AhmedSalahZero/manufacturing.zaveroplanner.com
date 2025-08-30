<?php

namespace App\Http\Controllers;

use App\BusinessSector;
use App\Project;
use App\Traits\Redirects;
use Barryvdh\DomPDF\Facade\Pdf; // Correct import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class ReportController extends Controller
{
	public function generatePdf()
    {
		$project = Project::find(133);
		$pages = [
            'inputs' => route('projects.edit',['project'=>$project]),
        ];
		$imagePaths = [];
        foreach ($pages as $key => $url) {
            $path = "public/report/{$key}.png";
            Browsershot::url($url)
                ->windowSize(1280, 800) // Adjust as needed
                ->waitUntilNetworkIdle()
                ->save(Storage::path($path));
            $imagePaths[$key] = Storage::url($path);
        }
		
		$dd = Browsershot::url('https://google.com.eg')->save(public_path('/test.png'));
		

		$project = Project::find(133);
		 $step_data =(new Redirects)->steps($project,'edit');
        $project->month = isset($project->start_date) ? date("m",strtotime($project->start_date)) : null;
        $project->year = isset($project->start_date) ? date("Y",strtotime($project->start_date)) : null;
		$products  = $project->products ; 
        
        $sectors = BusinessSector::all();
        // return view('projects.edit',compact('project' , 'sectors','step_data','products'));
   
		$data = [
			'project'=>$project,
			'sectors'=>$sectors ,
			'step_data'=>$step_data,
			'products'=>$products
		];

        // Load the view and generate PDF
		$pdf = Pdf::loadView('projects.edit',$data);
        
        // Optional customizations (e.g., from config/dompdf.php)
        $pdf->setPaper('A4', 'portrait');
        
        // Stream the PDF (inline view) or download it
        return $pdf->download('planning_report.pdf'); // Or ->stream() to view in browser
    }
}
