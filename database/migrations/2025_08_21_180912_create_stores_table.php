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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المتجر
            $table->string('slug')->unique(); // معرف فريد للمتجر
            $table->string('logo')->nullable(); // شعار المتجر
            $table->string('website_url'); // رابط الموقع الرسمي
            $table->string('affiliate_base_url')->nullable(); // رابط الأفلييت الأساسي
            $table->json('supported_countries'); // البلدان المدعومة
            $table->json('api_config')->nullable(); // إعدادات API للمتجر
            $table->boolean('is_active')->default(true); // حالة المتجر
            $table->integer('priority')->default(0); // أولوية الظهور
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
