<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tax_classes')) {
            Schema::create('tax_classes', function(Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->increments('tax_class_id');
                $table->string('name');
                $table->decimal('rate', 6, 4);
                $table->boolean('apply_to_delivery')->default(false);
            });
        }

        // v1 stored one class per item. Moved to menu_tax_classes and dropped in 2022_05_29_020000.
        if (!Schema::hasColumn('menus', 'tax_class_id')) {
            Schema::table('menus', function(Blueprint $table): void {
                $table->integer('tax_class_id')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menus', 'tax_class_id')) {
            Schema::table('menus', function(Blueprint $table): void {
                $table->dropColumn('tax_class_id');
            });
        }

        Schema::dropIfExists('tax_classes');
    }
};
