<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Description and details
            $table->longText('description')->nullable()->after('name');
            $table->string('brand_name', 100)->nullable()->after('description');
            
            // Dates
            $table->date('manufacturing_date')->nullable()->after('brand_name');
            $table->date('expiry_date')->nullable()->after('manufacturing_date');
            
            // Warranty and guarantee
            $table->integer('warranty_months')->nullable()->after('expiry_date');
            $table->integer('guarantee_months')->nullable()->after('warranty_months');
            
            // Tax and discount
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('guarantee_months');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('tax_percentage');
            
            // Stock details
            $table->json('stock_expiry_details')->nullable()->after('discount_percentage');
            
            // Add category_id if not exists
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('id');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key if exists
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
            
            $table->dropColumn([
                'description',
                'brand_name',
                'manufacturing_date',
                'expiry_date',
                'warranty_months',
                'guarantee_months',
                'tax_percentage',
                'discount_percentage',
                'stock_expiry_details'
            ]);
        });
    }
};
