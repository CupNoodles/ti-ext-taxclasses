<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * v4:
 * - categories can have tax classes (category_tax_classes)
 * - "apply to previous cart conditions" becomes explicit "tax the delivery charge" and "tax payment
 *   fees" options; classes that had it set get both
 * - label and description become optional, and an item is in a class at most once
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_classes', function(Blueprint $table): void {
            $table->boolean('tax_delivery')->default(false);
            $table->boolean('tax_payment_fee')->default(false);
            $table->text('label')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->boolean('include_in_price')->default(false)->change();
            $table->boolean('apply_to_previous_fees')->default(false)->change();
            $table->boolean('apply_to_delivery')->default(false)->change();
        });

        DB::table('tax_classes')->where('apply_to_previous_fees', true)
            ->update(['tax_delivery' => true, 'tax_payment_fee' => true]);

        if (!Schema::hasTable('category_tax_classes')) {
            Schema::create('category_tax_classes', function(Blueprint $table): void {
                $table->unsignedInteger('category_id');
                $table->unsignedInteger('tax_class_id');
                $table->primary(['category_id', 'tax_class_id']);
                $table->index('tax_class_id');
            });
        }

        // Remove duplicate item assignments before making them unique
        $keep = DB::table('menu_tax_classes')
            ->selectRaw('MIN(menu_tax_class_id) as id')
            ->groupBy('menu_id', 'tax_class_id')
            ->pluck('id');
        DB::table('menu_tax_classes')->whereNotIn('menu_tax_class_id', $keep)->delete();

        Schema::table('menu_tax_classes', function(Blueprint $table): void {
            $table->unique(['menu_id', 'tax_class_id']);
            $table->index('tax_class_id');
        });
    }

    public function down(): void
    {
        Schema::table('menu_tax_classes', function(Blueprint $table): void {
            $table->dropUnique(['menu_id', 'tax_class_id']);
            $table->dropIndex(['tax_class_id']);
        });

        Schema::dropIfExists('category_tax_classes');

        Schema::table('tax_classes', function(Blueprint $table): void {
            $table->dropColumn(['tax_delivery', 'tax_payment_fee']);
        });
    }
};
