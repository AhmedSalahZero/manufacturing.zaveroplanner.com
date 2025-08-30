<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSeasonalityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seasonality', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('model_id');
			$table->string('model_name');
			$table->enum('type',['flat','quarterly','monthly'])->default('flat');
			$table->json('percentages')->comment('زي ما هي في الفورم بالظبط علشان لما نيجي نجيب الاولد داتا في الفيو');
			$table->json('distributed_percentages')->comment('بنفرد الكولوم السابق شهور يعني شهر واحد قيمته كذا وشهر اتنين قيمته كذا وهكذا لحد اخر شهر في السنه');
            $table->timestamps();
        });
		
		
		  DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_seasonality_before_insert
BEFORE INSERT ON `seasonality`
FOR EACH ROW
BEGIN
  DECLARE p0 DOUBLE DEFAULT 0;
  DECLARE p1 DOUBLE DEFAULT 0;
  DECLARE p2 DOUBLE DEFAULT 0;
  DECLARE p3 DOUBLE DEFAULT 0;

  IF NEW.`type` = 'flat' THEN
    SET NEW.distributed_percentages = JSON_OBJECT(
      '01', 1/12, '02', 1/12, '03', 1/12,
      '04', 1/12, '05', 1/12, '06', 1/12,
      '07', 1/12, '08', 1/12, '09', 1/12,
      '10', 1/12, '11', 1/12, '12', 1/12
    );

  ELSEIF NEW.`type` = 'quarterly' THEN
    SET p0 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[0]')) AS DECIMAL(20,10)),0) / 3 / 100;
    SET p1 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[1]')) AS DECIMAL(20,10)),0) / 3 / 100;
    SET p2 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[2]')) AS DECIMAL(20,10)),0) / 3 / 100;
    SET p3 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[3]')) AS DECIMAL(20,10)),0) / 3 / 100;

    SET NEW.distributed_percentages = JSON_OBJECT(
      '01', p0, '02', p0, '03', p0,
      '04', p1, '05', p1, '06', p1,
      '07', p2, '08', p2, '09', p2,
      '10', p3, '11', p3, '12', p3
    );

  ELSEIF NEW.`type` = 'monthly' THEN
    SET NEW.distributed_percentages = JSON_OBJECT(
      '01', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[0]'))  AS DECIMAL(20,10))/100,0),
      '02', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[1]'))  AS DECIMAL(20,10))/100,0),
      '03', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[2]'))  AS DECIMAL(20,10))/100,0),
      '04', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[3]'))  AS DECIMAL(20,10))/100,0),
      '05', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[4]'))  AS DECIMAL(20,10))/100,0),
      '06', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[5]'))  AS DECIMAL(20,10))/100,0),
      '07', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[6]'))  AS DECIMAL(20,10))/100,0),
      '08', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[7]'))  AS DECIMAL(20,10))/100,0),
      '09', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[8]'))  AS DECIMAL(20,10))/100,0),
      '10', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[9]'))  AS DECIMAL(20,10))/100,0),
      '11', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[10]')) AS DECIMAL(20,10))/100,0),
      '12', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[11]')) AS DECIMAL(20,10))/100,0)
    );
  END IF;
END
SQL
        );

        // BEFORE UPDATE
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_seasonality_before_update
BEFORE UPDATE ON `seasonality`
FOR EACH ROW
BEGIN
  DECLARE p0 DOUBLE DEFAULT 0;
  DECLARE p1 DOUBLE DEFAULT 0;
  DECLARE p2 DOUBLE DEFAULT 0;
  DECLARE p3 DOUBLE DEFAULT 0;

  IF NEW.`type` = 'flat' THEN
    SET NEW.distributed_percentages = JSON_OBJECT(
      '01', 1/12, '02', 1/12, '03', 1/12,
      '04', 1/12, '05', 1/12, '06', 1/12,
      '07', 1/12, '08', 1/12, '09', 1/12,
      '10', 1/12, '11', 1/12, '12', 1/12
    );

  ELSEIF NEW.`type` = 'quarterly' THEN
    SET p0 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[0]')) AS DECIMAL(20,10)),0) / 3 / 100;
    SET p1 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[1]')) AS DECIMAL(20,10)),0) / 3 / 100;
    SET p2 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[2]')) AS DECIMAL(20,10)),0) / 3 / 100;
    SET p3 = COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[3]')) AS DECIMAL(20,10)),0) / 3 / 100;

    SET NEW.distributed_percentages = JSON_OBJECT(
      '01', p0, '02', p0, '03', p0,
      '04', p1, '05', p1, '06', p1,
      '07', p2, '08', p2, '09', p2,
      '10', p3, '11', p3, '12', p3
    );

  ELSEIF NEW.`type` = 'monthly' THEN
    SET NEW.distributed_percentages = JSON_OBJECT(
      '01', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[0]'))  AS DECIMAL(20,10))/100,0),
      '02', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[1]'))  AS DECIMAL(20,10))/100,0),
      '03', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[2]'))  AS DECIMAL(20,10))/100,0),
      '04', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[3]'))  AS DECIMAL(20,10))/100,0),
      '05', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[4]'))  AS DECIMAL(20,10))/100,0),
      '06', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[5]'))  AS DECIMAL(20,10))/100,0),
      '07', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[6]'))  AS DECIMAL(20,10))/100,0),
      '08', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[7]'))  AS DECIMAL(20,10))/100,0),
      '09', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[8]'))  AS DECIMAL(20,10))/100,0),
      '10', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[9]'))  AS DECIMAL(20,10))/100,0),
      '11', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[10]')) AS DECIMAL(20,10))/100,0),
      '12', COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(NEW.percentages, '$[11]')) AS DECIMAL(20,10))/100,0)
    );
  END IF;
END
SQL
        );
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collection_policies');
    }
}
