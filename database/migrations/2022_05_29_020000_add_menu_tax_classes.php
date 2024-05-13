<?php

namespace CupNoodles\TaxClasses\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * 
 */
class AddMenuTaxClasses extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu_tax_classes')) {
            Schema::create('menu_tax_classes', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('menu_tax_class_id');
                $table->integer('menu_id');
                $table->integer('tax_class_id');
            });
        }

        // menus.tax_class_is is the old way (can't support many-to-many)
        if (Schema::hasColumn('menus', 'tax_class_id')) {
            $this->migrate_tax_classes();
            Schema::table('menus', function (Blueprint $table) {
                
                $table->dropColumn('tax_class_id');
            });
        }
        
    }

    public function migrate_tax_classes(){

        $previous_tax_classes = DB::table('menus')->select('menu_id', 'tax_class_id')->where('tax_class_id', '!=', 0)->get();
        foreach($previous_tax_classes as $row){
            DB::table('menu_tax_classes')->insert(
                [
                    'menu_id' => $row->menu_id,
                    'tax_class_id' => $row->tax_class_id
                ]
            );
        }

    }

    public function down()
    {
        /* TODO see note about migration above */
        Schema::dropIfExists('menu_tax_classes');
    }

}