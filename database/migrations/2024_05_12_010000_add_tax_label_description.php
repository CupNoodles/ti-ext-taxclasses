<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tax_classes') && !Schema::hasColumn('tax_classes', 'label')) {
            Schema::table('tax_classes', function(Blueprint $table): void {
                $table->text('label')->nullable();
            });
        }

        if (Schema::hasTable('tax_classes') && !Schema::hasColumn('tax_classes', 'description')) {
            Schema::table('tax_classes', function(Blueprint $table): void {
                $table->text('description')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tax_classes', 'label')) {
            Schema::table('tax_classes', function(Blueprint $table): void {
                $table->dropColumn(['label', 'description']);
            });
        }
    }
};
