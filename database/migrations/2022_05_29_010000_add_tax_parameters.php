<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tax_classes') && !Schema::hasColumn('tax_classes', 'include_in_price')) {
            Schema::table('tax_classes', function(Blueprint $table): void {
                $table->boolean('include_in_price')->default(false);
                $table->text('limit_to_order_type')->nullable();
                $table->boolean('apply_to_previous_fees')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tax_classes', 'include_in_price')) {
            Schema::table('tax_classes', function(Blueprint $table): void {
                $table->dropColumn(['include_in_price', 'limit_to_order_type', 'apply_to_previous_fees']);
            });
        }
    }
};
