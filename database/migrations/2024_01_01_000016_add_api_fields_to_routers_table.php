<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('routers', function (Blueprint $table) {
            $table->string('api_username')->default('admin')->after('notes');
            $table->string('api_password')->nullable()->after('api_username');
            $table->unsignedSmallInteger('api_port')->default(80)->after('api_password');
        });
    }

    public function down(): void {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['api_username', 'api_password', 'api_port']);
        });
    }
};
