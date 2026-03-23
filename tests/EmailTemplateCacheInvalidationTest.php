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

it('prevents race condition when template is updated and immediately retrieved', function () {
    // This test replicates NB-1574: sam's exact scenario where:
    // 1. User edits template in admin UI and saves
    // 2. User immediately refreshes wizard and clicks next
    // 3. Updated content should be visible, not cached old content

    $template = EmailTemplate::factory()->create([
        'key' => 'race-condition-test',
        'language' => config('filament-email-templates.default_locale'),
        'subject' => 'Test Subject',
        'content' => '<p>Original Content v1</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    // Step 1: User views wizard (this caches the template)
    $firstView = EmailTemplate::findEmailByKey('race-condition-test', config('filament-email-templates.default_locale'));
    expect($firstView->content)->toBe('<p>Original Content v1</p>');

    // Step 2: User edits template in admin and saves
    $template->update(['content' => '<p>Updated Content v2</p>']);

    // Step 3: User IMMEDIATELY refreshes and goes to wizard (within 1 second)
    // This simulates the race condition where cache clearing hasn't propagated yet
    $immediateView = EmailTemplate::findEmailByKey('race-condition-test', config('filament-email-templates.default_locale'));

    // CRITICAL: Must get updated content, not cached original
    expect($immediateView->content)->toBe('<p>Updated Content v2</p>')
        ->and($immediateView->content)->not->toBe('<p>Original Content v1</p>');

    // Step 4: Subsequent views should also get updated content
    $laterView = EmailTemplate::findEmailByKey('race-condition-test', config('filament-email-templates.default_locale'));
    expect($laterView->content)->toBe('<p>Updated Content v2</p>');
});

it('handles multiple rapid updates correctly', function () {
    // Tests scenario where admin makes multiple quick edits
    $template = EmailTemplate::factory()->create([
        'key' => 'rapid-updates-test',
        'language' => config('filament-email-templates.default_locale'),
        'content' => '<p>Version 1</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    // Initial cache
    EmailTemplate::findEmailByKey('rapid-updates-test', config('filament-email-templates.default_locale'));

    // Rapid updates (like admin fixing typos quickly)
    $template->update(['content' => '<p>Version 2</p>']);
    $v2 = EmailTemplate::findEmailByKey('rapid-updates-test', config('filament-email-templates.default_locale'));
    expect($v2->content)->toBe('<p>Version 2</p>');

    $template->update(['content' => '<p>Version 3</p>']);
    $v3 = EmailTemplate::findEmailByKey('rapid-updates-test', config('filament-email-templates.default_locale'));
    expect($v3->content)->toBe('<p>Version 3</p>');

    $template->update(['content' => '<p>Final Version</p>']);
    $final = EmailTemplate::findEmailByKey('rapid-updates-test', config('filament-email-templates.default_locale'));
    expect($final->content)->toBe('<p>Final Version</p>');
});
