<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('account_otps')) {
            Schema::create('account_otps', function (Blueprint $table) {
                $table->id();
                $table->string('email', 120)->index();
                $table->string('purpose', 32)->index();
                $table->string('code_hash');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamp('expires_at');
                $table->timestamp('consumed_at')->nullable();
                $table->timestamps();

                $table->index(['email', 'purpose', 'consumed_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('account_otps');
    }
};
