<?php

use Illuminate\Database\Migrations\Migration;

class CreateVisualizationsTable extends Migration {

	public function up()
	{
		Schema::create('visualizations', function($table)
		{
			$table->increments('id');
			$table->string('fusion_table_id', 128); 
			$table->string('display_name', 512);
			$table->text('description')->nullable();
			$table->boolean('published');
			$table->enum('type', array('point', 'polygon'));
			$table->string('default_column', 512);
			$table->enum('milestone_format', array('hour', 'day', 'month', 'year', 'decade', 'century', 'mixed'));
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('visualizations');
	}

}