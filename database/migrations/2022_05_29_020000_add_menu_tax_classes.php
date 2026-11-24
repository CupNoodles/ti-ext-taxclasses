<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menu items can belong to several tax classes, instead of one (menus.tax_class_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu_tax_classes')) {
            Schema::create('menu_tax_classes', function(Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->increments('menu_tax_class_id');
                $table->integer('menu_id');
                $table->integer('tax_class_id');
            });
        }

        if (Schema::hasColumn('menus', 'tax_class_id')) {
            DB::table('menus')->where('tax_class_id', '!=', 0)->orderBy('menu_id')
                ->get(['menu_id', 'tax_class_id'])
                ->each(fn($row) => DB::table('menu_tax_classes')->insert([
                    'menu_id' => $row->menu_id,
                    'tax_class_id' => $row->tax_class_id,
                ]));

            Schema::table('menus', function(Blueprint $table): void {
                $table->dropColumn('tax_class_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('menus', 'tax_class_id')) {
            Schema::table('menus', function(Blueprint $table): void {
                $table->integer('tax_class_id')->default(0);
            });
        }

        // Keeps one class per item, as before
        if (Schema::hasTable('menu_tax_classes')) {
            DB::table('menu_tax_classes')->orderBy('menu_tax_class_id')->get()
                ->unique('menu_id')
                ->each(fn($row) => DB::table('menus')->where('menu_id', $row->menu_id)->update(['tax_class_id' => $row->tax_class_id]));
        }

        Schema::dropIfExists('menu_tax_classes');
    }
};
