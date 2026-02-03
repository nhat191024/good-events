<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enum\ReportStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_bill_id')->nullable()->constrained('partner_bills')->onDelete('cascade');
            $table->unsignedInteger('thread_id')->nullable();
            $table->foreign('thread_id')->references('id')->on('threads')->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('status')->default(ReportStatus::PENDING->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
