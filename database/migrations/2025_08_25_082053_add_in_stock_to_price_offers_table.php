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
        Schema::table('price_offers', function (Blueprint $table) {
            // ✅✅✅ هذا هو السطر الذي يضيف العمود المفقود 'in_stock' ✅✅✅
            // سيتم إضافته بعد عمود 'url' لترتيب الجدول
            $table->boolean('in_stock')->default(true)->after('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_offers', function (Blueprint $table) {
            // هذا السطر يضمن أنه يمكنك التراجع عن الـ migration إذا احتجت لذلك
            $table->dropColumn('in_stock');
        });
    }
};
