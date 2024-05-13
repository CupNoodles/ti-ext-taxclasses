<?php

namespace CupNoodles\TaxClasses\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * 
 */
class AddTaxParameters extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tax_classes')) {
            if (!Schema::hasColumn('tax_classes', 'include_in_price')) {
                Schema::table('tax_classes', function (Blueprint $table) {
                    $table->boolean('include_in_price');
                    $table->text('limit_to_order_type')->nullable();
                    $table->boolean('apply_to_previous_fees');
                });
            }


        }


    }

    public function down()
    {
        if (Schema::hasTable('tax_classes')) {
            Schema::table('tax_classes', function (Blueprint $table) {
                if (!Schema::hasColumn('tax_classes', 'include_in_price')) {
                    $table->dropColumn( 'include_in_price' );
                }
                if (!Schema::hasColumn('tax_classes', 'limit_to_order_type')) {
                    $table->dropColumn( 'limit_to_order_type' );
                }
                if (!Schema::hasColumn('tax_classes', 'apply_to_previous_fees')) {
                    $table->dropColumn( 'apply_to_previous_fees' );
                }
            });
        };


    }

}
