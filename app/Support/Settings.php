<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class Settings
{
    /** @var array<string, string|null>|null */
    private static ?array $cache = null;

    /** @var array<string, string|null> */
    public const DEFAULTS = [
        'site_tagline' => 'Stories, serialised at your own pace.',
        'currency' => 'IDR',
        'bank_name' => '',
        'bank_account_name' => '',
        'bank_account_number' => '',
        'payment_instructions' => "Transfer the exact amount to the account below, then upload your transfer receipt. The author verifies payments manually, usually within 1-2 days.",
        'font_family' => 'system',
        'theme_mode' => 'light',
        'brand_logo_light_path' => '',
        'brand_logo_dark_path' => '',
        'brand_favicon_path' => '',
        'seo_site_title' => '',
        'seo_site_description' => '',
        'seo_site_keywords' => '',
        'seo_google_verification' => '',
        'mail_mailer' => 'log',
        'mail_host' => '',
        'mail_port' => '587',
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => 'tls',
        'mail_from_address' => '',
        'mail_from_name' => '',
    ];

    /**
     * @var array<string, array{label: string, note: string}>
     */
    public const THEMES = [
        'light' => [
            'label' => 'Light',
            'note' => 'Warm paper with dark ink.',
        ],
        'dark' => [
            'label' => 'Dark',
            'note' => 'Warm charcoal for low-light reading.',
        ],
        'system' => [
            'label' => 'Follow device',
            'note' => 'Whatever the reader\'s device prefers.',
        ],
    ];

    /**
     * Fonts bundled in public/fonts. A null stack means "keep the PenakeTik default".
     *
     * @var array<string, array{label: string, note: string, stack: string|null, sample: string}>
     */
    public const FONTS = [
        'system' => [
            'label' => 'PenakeTik default',
            'note' => 'Warm serif headings with system sans text.',
            'stack' => null,
            'sample' => '"Iowan Old Style", "Palatino Linotype", Palatino, Charter, Georgia, serif',
        ],
        'nunito' => [
            'label' => 'Nunito',
            'note' => 'Rounded and friendly — kind to long reading sessions.',
            'stack' => '"Nunito", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
            'sample' => '"Nunito", sans-serif',
        ],
        'sora' => [
            'label' => 'Sora',
            'note' => 'Geometric and modern, with a technical edge.',
            'stack' => '"Sora", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
            'sample' => '"Sora", sans-serif',
        ],
        'exo-2' => [
            'label' => 'Exo 2',
            'note' => 'Futuristic and high contrast — a bold pick.',
            'stack' => '"Exo 2", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
            'sample' => '"Exo 2", sans-serif',
        ],
    ];

    /**
     * @return array<string, string|null>
     */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            if (! Schema::hasTable('settings')) {
                return self::$cache = self::DEFAULTS;
            }

            return self::$cache = array_merge(self::DEFAULTS, Setting::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            return self::$cache = self::DEFAULTS;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = self::all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set(string $key, ?string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);

        if (self::$cache !== null) {
            self::$cache[$key] = $value;
        }
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function flush(): void
    {
        self::$cache = null;
    }

    public static function themeMode(): string
    {
        $mode = (string) self::get('theme_mode', 'light');

        return array_key_exists($mode, self::THEMES) ? $mode : 'light';
    }

    public static function fontKey(): string
    {
        $key = (string) self::get('font_family', 'system');

        return array_key_exists($key, self::FONTS) ? $key : 'system';
    }

    public static function fontStack(): ?string
    {
        return self::FONTS[self::fontKey()]['stack'];
    }

    public static function fontSample(): string
    {
        return self::FONTS[self::fontKey()]['sample'];
    }

    /**
     * Inline style for <html> so the chosen family reaches the CSS variables.
     */
    public static function brandStyleAttribute(): string
    {
        $stack = self::fontStack();

        if ($stack === null) {
            return '';
        }

        $stack = e($stack);

        return "style=\"--brand-sans: {$stack}; --brand-serif: {$stack}\"";
    }

    /**
     * Logo for the given scheme. Falls back to the other scheme's logo when
     * only one has been uploaded, so a single upload always shows something.
     */
    public static function logoUrl(string $mode = 'light'): ?string
    {
        $keys = $mode === 'dark'
            ? ['brand_logo_dark_path', 'brand_logo_light_path']
            : ['brand_logo_light_path', 'brand_logo_dark_path'];

        foreach ($keys as $key) {
            if ($path = self::get($key)) {
                return asset('storage/'.$path);
            }
        }

        return null;
    }

    public static function faviconUrl(): ?string
    {
        $path = self::get('brand_favicon_path');

        return $path ? asset('storage/'.$path) : null;
    }
}
