<?php

namespace App\Console\Commands;

use App\Helpers\HArr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		$arr = [
			4 => 0 ,
			5=>100,
			6=>200,
			7=>300
		];
		
		$offset = 6 ;
		// dd();
	// 	DB::table('business_sectors')->delete();
    //    foreach(getBusinessSectors() as $sectorName){
	// 	DB::table('business_sectors')->insert([
	// 		'name_en'=>$sectorName,
	// 		'name_ar'=>$sectorName,
	// 	]);
	//    }
    }
}
