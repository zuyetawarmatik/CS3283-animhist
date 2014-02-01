<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddThumbFieldsToVisualizationsTable extends Migration {

	/**
	 * Make changes to the table.
	 *
	 * @return void
	 */
	public function up()
	{	
		Schema::table('visualizations', function(Blueprint $table) {		
			
			$table->string("thumb_file_name")->nullable();
			$table->integer("thumb_file_size")->nullable();
			$table->string("thumb_content_type")->nullable();
			$table->timestamp("thumb_updated_at")->nullable();

		});

	}

	/**
	 * Revert the changes to the table.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('visualizations', function(Blueprint $table) {

			$table->dropColumn("thumb_file_name");
			$table->dropColumn("thumb_file_size");
			$table->dropColumn("thumb_content_type");
			$table->dropColumn("thumb_updated_at");

		});
	}

}