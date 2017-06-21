<?php namespace HeathDutton\LogoStash\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHeathduttonLogostashEmployer extends Migration
{
    public function up()
    {
        Schema::create('heathdutton_logostash_logo', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('employer_name', 255)->nullable()->unsigned(false)->default(null)->unique();
            $table->string('logo_location', 1024)->nullable()->unsigned(false)->default(null);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('auto_update')->default(1);
            $table->smallInteger('attempts')->nullable()->unsigned()->default(0);
            $table->boolean('status')->default(1);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('heathdutton_logostash_logo');
    }
}
