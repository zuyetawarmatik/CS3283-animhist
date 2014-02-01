<?php

use Illuminate\Database\Migrations\Migration;

class CreateVisualizationsTable extends Migration {

	public function up()
	{
		Schema::create('visualizations', function($table)
		{
			$table->increments('id');
			$table->string('fusion_table_id', 128);
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->string('display_name', 512);
			$table->string('category', 128)->nullable();
			$table->text('description')->nullable();
			$table->boolean('published');
			$table->enum('type', array('point', 'polygon'));			
			$table->string('default_column', 512)->nullable();
			$table->boolean('html_data_enabled');
			$table->enum('milestone_format', array('hour', 'day', 'month', 'year', 'decade', 'century', 'mixed'));
			$table->text('milestones')->nullable();
			$table->float('zoom');
			$table->float('center_latitude'); $table->float('center_longitude');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('visualizations');
	}

}