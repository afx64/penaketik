<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Str;

trait GeneratesSlugs
{
    /**
     * @param  callable(string): bool  $exists
     */
    protected function uniqueSlug(string $value, callable $exists, string $fallback = 'untitled'): string
    {
        $base = Str::slug($value) ?: $fallback;
        $slug = $base;
        $suffix = 2;

        while ($exists($slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
