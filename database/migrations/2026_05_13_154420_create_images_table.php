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
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            /*
            Relación polimórfica
            */
            $table->unsignedBigInteger('imageable_id');

            $table->string('imageable_type');

            /*
            Ruta archivo
            */
            $table->string('path');

            /*
            public, s3, etc
            */
            $table->string('disk')
                ->default('public');

            /*
            Nombre original
            */
            $table->string('original_name')
                ->nullable();

            /*
            Mime type
            */
            $table->string('mime_type')
                ->nullable();

            /*
            Peso KB
            */
            $table->integer('size')
                ->nullable();

            /*
            Alt para SEO
            */
            $table->string('alt_text')
                ->nullable();

            /*
            Imagen principal
            */
            $table->boolean('is_primary')
                ->default(false);

            /*
            Orden visual
            */
            $table->integer('sort_order')
                ->default(0);

            $table->timestamps();

            /*
            Índice importante
            */
            $table->index([
                'imageable_id',
                'imageable_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
