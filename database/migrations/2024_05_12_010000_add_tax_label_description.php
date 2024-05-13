<?php

namespace CupNoodles\TaxClasses\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * 
 */
class AddTaxLabelDescription extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tax_classes')) {
            if (!Schema::hasColumn('tax_classes', 'label')) {
                Schema::table('tax_classes', function (Blueprint $table) {
                    $table->text('label');
                });
            }

            if (!Schema::hasColumn('tax_classes', 'description')) {
                Schema::table('tax_classes', function (Blueprint $table) {
                    $table->text('description');
                });
            }

        }

    }

    public function down()
    {
        if (Schema::hasTable('tax_classes')) {
            Schema::table('tax_classes', function (Blueprint $table) {
                if (Schema::hasColumn('tax_classes', 'label')) {
                    $table->dropColumn( 'label' );
                }
                if (Schema::hasColumn('tax_classes', 'description')) {
                    $table->dropColumn( 'description' );
                }
            });
        };


    }

}
