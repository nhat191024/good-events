<?php

use Cmgmyr\Messenger\Models\Models;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Models::table('messages'), function (Blueprint $table) {
            $table->string('type')->default('text')->after('user_id');
            $table->text('body')->nullable()->change();
            $table->decimal('location_latitude', 10, 7)->nullable()->after('body');
            $table->decimal('location_longitude', 10, 7)->nullable()->after('location_latitude');
            $table->string('location_label')->nullable()->after('location_longitude');
            $table->string('location_address')->nullable()->after('location_label');
        });
    }

    public function down(): void
    {
        Schema::table(Models::table('messages'), function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'location_latitude',
                'location_longitude',
                'location_label',
                'location_address',
            ]);

            $table->text('body')->nullable(false)->change();
        });
    }
};
