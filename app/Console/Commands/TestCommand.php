<?php

namespace App\Console\Commands;

use App\Helpers\HArr;
use App\Project;
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
		
		$database = env('DB_DATABASE');

$columns = DB::table('information_schema.columns')
    ->select('table_name', 'column_name')
    ->where('table_schema', $database)
    ->where('column_name', 'like', '%\_id')
    ->orderBy('table_name')
	->whereNotIn('column_name',['project_id','user_id','category_id','business_sector_id'])
    ->orderBy('column_name')
    ->get();
	dd($columns);
return $columns;

		dd(getTableNamesThatHasColumn('manpower_id'));
    }
}
