<?php

namespace CupNoodles\TaxClasses\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * 
 */
class AddTaxClasses extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tax_classes')) {
            Schema::create('tax_classes', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('tax_class_id');
                $table->string('name');
                $table->decimal('rate', 6, 4);
                $table->boolean('apply_to_delivery');
            });
        }

        if (!Schema::hasColumn('menus', 'tax_class_id')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->integer('tax_class_id');
            });
        }

    }

    public function down()
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['tax_class_id', 'uom_id']);
        });
        Schema::dropIfExists('tax_classes');

    }

}
