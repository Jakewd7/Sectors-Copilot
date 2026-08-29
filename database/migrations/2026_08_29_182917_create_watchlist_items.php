<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('watchlist_id')->constrained('watchlists')->cascadeOnDelete();
            $table->string('stock_ticker', 10);
            $table->text('note')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            $table->unique(['watchlist_id', 'stock_ticker']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlist_items');
    }
};
