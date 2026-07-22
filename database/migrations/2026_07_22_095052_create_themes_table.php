<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // A curated, platform-wide library of theme presets (colors + font stack) — managed
        // by a Global platform admin at /admin/themes, picked per-tenant on the Organization
        // Settings page. Only the 6 color shades actually used anywhere in the app (see
        // resources/css/app.css) are overridable, and font_stack stays within safe, universally
        // available generic font families (no webfont loading, no new dependency).
        Schema::create('themes', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color_50');
            $table->string('color_100');
            $table->string('color_500');
            $table->string('color_600');
            $table->string('color_700');
            $table->string('color_800');
            $table->string('font_stack');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        $now = now();

        DB::table('themes')->insert([
            [
                'name' => 'Emerald', 'slug' => 'emerald',
                'color_50' => '#ecfdf5', 'color_100' => '#d1fae5', 'color_500' => '#10b981',
                'color_600' => '#059669', 'color_700' => '#047857', 'color_800' => '#065f46',
                'font_stack' => "'Instrument Sans', ui-sans-serif, system-ui, sans-serif",
                'is_default' => true, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Ocean', 'slug' => 'ocean',
                'color_50' => '#f0f9ff', 'color_100' => '#e0f2fe', 'color_500' => '#0ea5e9',
                'color_600' => '#0284c7', 'color_700' => '#0369a1', 'color_800' => '#075985',
                'font_stack' => 'ui-sans-serif, system-ui, -apple-system, sans-serif',
                'is_default' => false, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Sunset', 'slug' => 'sunset',
                'color_50' => '#fff7ed', 'color_100' => '#ffedd5', 'color_500' => '#f97316',
                'color_600' => '#ea580c', 'color_700' => '#c2410c', 'color_800' => '#9a3412',
                'font_stack' => "ui-serif, Georgia, Cambria, 'Times New Roman', Times, serif",
                'is_default' => false, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Slate Mono', 'slug' => 'slate-mono',
                'color_50' => '#f8fafc', 'color_100' => '#f1f5f9', 'color_500' => '#64748b',
                'color_600' => '#475569', 'color_700' => '#334155', 'color_800' => '#1e293b',
                'font_stack' => 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
                'is_default' => false, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
