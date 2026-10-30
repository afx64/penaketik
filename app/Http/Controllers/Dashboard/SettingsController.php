<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Support\MailConfigurator;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SettingsController extends Controller
{
    public function branding()
    {
        return view('dashboard.settings.branding', [
            'fonts' => Settings::FONTS,
            'themes' => Settings::THEMES,
        ]);
    }

    public function updateBranding(Request $request)
    {
        $data = $request->validate([
            'theme_mode' => ['required', 'in:'.implode(',', array_keys(Settings::THEMES))],
            'font_family' => ['required', 'in:'.implode(',', array_keys(Settings::FONTS))],
            'brand_logo_light' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brand_logo_dark' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brand_favicon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:512'],
        ]);

        $uploads = [
            'brand_logo_light' => 'brand_logo_light_path',
            'brand_logo_dark' => 'brand_logo_dark_path',
            'brand_favicon' => 'brand_favicon_path',
        ];

        foreach ($uploads as $field => $key) {
            if ($request->hasFile($field)) {
                if ($old = Settings::get($key)) {
                    Storage::disk('public')->delete($old);
                }

                Settings::set($key, $request->file($field)->store('branding', 'public'));

                continue;
            }

            if ($request->boolean('remove_'.$field) && ($old = Settings::get($key))) {
                Storage::disk('public')->delete($old);
                Settings::set($key, '');
            }
        }

        Settings::set('theme_mode', $data['theme_mode']);
        Settings::set('font_family', $data['font_family']);

        return back()->with('status', 'Branding saved.');
    }

    public function seo()
    {
        return view('dashboard.settings.seo');
    }

    public function updateSeo(Request $request)
    {
        $data = $request->validate([
            'seo_site_title' => ['nullable', 'string', 'max:180'],
            'seo_site_description' => ['nullable', 'string', 'max:500'],
            'seo_site_keywords' => ['nullable', 'string', 'max:255'],
            'seo_google_verification' => ['nullable', 'string', 'max:190'],
            'site_tagline' => ['nullable', 'string', 'max:180'],
        ]);

        Settings::put($data);

        return back()->with('status', 'SEO settings saved.');
    }

    public function mail()
    {
        return view('dashboard.settings.mail');
    }

    public function updateMail(Request $request)
    {
        $data = $request->validate([
            'mail_mailer' => ['required', 'in:smtp,log'],
            'mail_host' => ['required_if:mail_mailer,smtp', 'nullable', 'string', 'max:190'],
            'mail_port' => ['required_if:mail_mailer,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:190'],
            'mail_password' => ['nullable', 'string', 'max:190'],
            'mail_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['required_if:mail_mailer,smtp', 'nullable', 'email', 'max:190'],
            'mail_from_name' => ['nullable', 'string', 'max:190'],
        ]);

        // An empty password field means "keep the saved password".
        if (blank($data['mail_password'] ?? null)) {
            unset($data['mail_password']);
        }

        Settings::put($data);
        Settings::flush();

        return back()->with('status', 'Mail settings saved.');
    }

    public function testMail(Request $request)
    {
        $data = $request->validate([
            'to' => ['required', 'email'],
        ]);

        try {
            MailConfigurator::apply();
            Mail::raw(
                'This is a test message from '.config('app.name').'. If you can read this, your mail settings work.',
                fn ($message) => $message->to($data['to'])->subject(config('app.name').' test email')
            );
        } catch (Throwable $exception) {
            return back()->withErrors(['mail' => 'Sending failed: '.$exception->getMessage()]);
        }

        return back()->with('status', 'Test email sent to '.$data['to'].'. Check the inbox (or storage/logs if the mailer is set to log).');
    }

    public function payment()
    {
        return view('dashboard.settings.payment');
    }

    public function updatePayment(Request $request)
    {
        $data = $request->validate([
            'currency' => ['required', 'string', 'max:8'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],
            'bank_account_number' => ['nullable', 'string', 'max:60'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        Settings::put($data);

        return back()->with('status', 'Payment settings saved.');
    }
}
