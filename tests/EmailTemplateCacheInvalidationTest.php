<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Mail\UserRegisteredEmail;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;

beforeEach(function () {
    Cache::flush();
    Mail::fake();

    // Create a theme for the template
    $this->theme = EmailTemplateTheme::factory()->create([
        'is_default' => true,
    ]);
});

it('uses updated template content immediately after save', function () {
    // 1. Create an email template with original content
    $template = EmailTemplate::factory()->create([
        'key' => 'test-cache-invalidation',
        'language' => config('filament-email-templates.default_locale'),
        'subject' => 'Original Subject',
        'content' => '<p>Original Content</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    // 2. Retrieve the template (this will cache it)
    $cachedTemplate = EmailTemplate::findEmailByKey('test-cache-invalidation', config('filament-email-templates.default_locale'));
    expect($cachedTemplate->content)->toBe('<p>Original Content</p>');
    expect($cachedTemplate->subject)->toBe('Original Subject');

    // 3. Update the template content
    $template->update([
        'subject' => 'Updated Subject',
        'content' => '<p>Updated Content</p>',
    ]);

    // 4. Retrieve the template again - should get updated content, not cached
    $updatedTemplate = EmailTemplate::findEmailByKey('test-cache-invalidation', config('filament-email-templates.default_locale'));

    // 5. Assert the new template contains updated content immediately
    expect($updatedTemplate->content)->toBe('<p>Updated Content</p>');
    expect($updatedTemplate->subject)->toBe('Updated Subject');

    // 6. Verify database has the updated content
    $freshTemplate = EmailTemplate::where('key', 'test-cache-invalidation')->first();
    expect($freshTemplate->content)->toBe('<p>Updated Content</p>');
    expect($freshTemplate->subject)->toBe('Updated Subject');
});

it('clears cache when template is updated', function () {
    $template = EmailTemplate::factory()->create([
        'key' => 'cache-test',
        'language' => config('filament-email-templates.default_locale'),
        'content' => '<p>Original</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    // Cache the template
    EmailTemplate::findEmailByKey('cache-test', config('filament-email-templates.default_locale'));
    $cacheKey = "email_by_key_cache-test_" . config('filament-email-templates.default_locale');

    // Verify cache exists
    expect(Cache::has($cacheKey))->toBeTrue();

    // Update the template (should clear cache)
    $template->update(['content' => '<p>Updated</p>']);

    // Verify cache was cleared
    expect(Cache::has($cacheKey))->toBeFalse();
});

it('clears cache when template is deleted', function () {
    $template = EmailTemplate::factory()->create([
        'key' => 'delete-test',
        'language' => config('filament-email-templates.default_locale'),
        'content' => '<p>Content</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    // Cache the template
    EmailTemplate::findEmailByKey('delete-test', config('filament-email-templates.default_locale'));
    $cacheKey = "email_by_key_delete-test_" . config('filament-email-templates.default_locale');

    // Verify cache exists
    expect(Cache::has($cacheKey))->toBeTrue();

    // Delete the template (should clear cache)
    $template->delete();

    // Verify cache was cleared
    expect(Cache::has($cacheKey))->toBeFalse();
});
