<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

it('returns default language when locale missing and caches result', function () {
    Cache::flush();

    $key = 'welcome-email';
    $defaultLang = config('filament-email-templates.default_locale');

    $default = EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $defaultLang,
    ]);

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => 'fr',
    ]);

    $template = EmailTemplate::findEmailByKey($key);

    expect($template->id)->toBe($default->id);
    $cacheKey = "email_by_key_{$key}_";
    expect(Cache::has($cacheKey))->toBeTrue();
});

it('can clear the email template cache', function () {
    Cache::flush();

    $key = 'cache-clear-email';
    $lang = config('filament-email-templates.default_locale');

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
    ]);

    EmailTemplate::findEmailByKey($key, $lang);
    $cacheKey = "email_by_key_{$key}_{$lang}";
    expect(Cache::has($cacheKey))->toBeTrue();

    EmailTemplate::clearEmailTemplateCache($key, $lang);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('returns the mailable FQCN when the class exists', function () {
    $key = 'fake-mailable';
    EmailTemplate::factory()->create(['key' => $key]);

    $classDir = app_path('Mail/Visualbuilder/EmailTemplates');
    File::ensureDirectoryExists($classDir);

    $filePath = $classDir . '/FakeMailable.php';
    File::put($filePath, "<?php\nnamespace App\\Mail\\Visualbuilder\\EmailTemplates;\nuse Illuminate\\Mail\\Mailable;\nclass FakeMailable extends Mailable {}\n");
    require_once $filePath;

    $template = EmailTemplate::firstWhere('key', $key);
    $fqcn = $template->getMailableClass();

    expect($fqcn)->toBe('App\\Mail\\Visualbuilder\\EmailTemplates\\FakeMailable');
    File::delete($filePath);
});

it('throws an exception when the mailable class does not exist', function () {
    $record = EmailTemplate::factory()->create(['key' => 'missing-class']);

    expect(fn () => $record->getMailableClass())
        ->toThrow(Exception::class);
});
