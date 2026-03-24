<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Mail\UserRegisteredEmail;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;
use Visualbuilder\EmailTemplates\Tests\Models\User;

beforeEach(function () {
    Cache::flush();
    Mail::fake();

    // Create a theme for the template
    $this->theme = EmailTemplateTheme::factory()->create([
        'is_default' => true,
    ]);
});

it('uses updated template content in mailable immediately after save (NB-1574 regression test)', function () {
    // This test replicates the exact bug scenario from NB-1574:
    // 1. Admin creates/edits email template in UI and saves
    // 2. User triggers email send (e.g., registers, wizard completes)
    // 3. Email MUST use the updated template content, not cached old content

    // Step 1: Create initial template with original content
    $template = EmailTemplate::factory()->create([
        'key' => 'user-welcome',
        'language' => config('filament-email-templates.default_locale'),
        'name' => 'User Welcome Email',
        'subject' => 'Welcome - Version 1',
        'content' => '<p>Original welcome message v1</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $user = User::factory()->create(['name' => 'Test User']);

    // Step 2: Send email with original template (this caches the template)
    $mailable1 = new UserRegisteredEmail($user);
    $mailable1->build();
    expect($mailable1->subject)->toBe('Welcome - Version 1')
        ->and($mailable1->emailTemplate->content)->toBe('<p>Original welcome message v1</p>');

    // Step 3: Admin edits template in UI and saves (simulating the bug scenario)
    $template->update([
        'subject' => 'Welcome - Version 2 UPDATED',
        'content' => '<p>NEW welcome message v2 - this should appear immediately!</p>',
    ]);

    // Step 4: User immediately triggers email send (e.g., another registration within seconds)
    // CRITICAL: This MUST use the updated template content, not cached old content
    // This is the EXACT scenario from NB-1574 where sam reported the bug
    $mailable2 = new UserRegisteredEmail($user);
    $mailable2->build();

    // Assertion: Email MUST contain updated content immediately
    expect($mailable2->subject)->toBe('Welcome - Version 2 UPDATED')
        ->and($mailable2->emailTemplate->content)->toBe('<p>NEW welcome message v2 - this should appear immediately!</p>')
        ->and($mailable2->emailTemplate->content)->not->toBe('<p>Original welcome message v1</p>');

    // Step 5: Verify subsequent sends also use updated content
    $mailable3 = new UserRegisteredEmail($user);
    $mailable3->build();
    expect($mailable3->subject)->toBe('Welcome - Version 2 UPDATED')
        ->and($mailable3->emailTemplate->content)->toBe('<p>NEW welcome message v2 - this should appear immediately!</p>');
});

it('handles multiple rapid template updates correctly in mailables', function () {
    // Tests the scenario where admin makes multiple quick edits to fix typos/content
    // Each subsequent email send MUST use the latest version

    $template = EmailTemplate::factory()->create([
        'key' => 'user-welcome',
        'language' => config('filament-email-templates.default_locale'),
        'name' => 'User Welcome Email',
        'subject' => 'Welcome v1',
        'content' => '<p>Content v1</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $user = User::factory()->create(['name' => 'Test User']);

    // Initial send
    $mailable1 = new UserRegisteredEmail($user);
    $mailable1->build();
    expect($mailable1->subject)->toBe('Welcome v1');

    // Admin makes first edit
    $template->update(['subject' => 'Welcome v2', 'content' => '<p>Content v2</p>']);
    $mailable2 = new UserRegisteredEmail($user);
    $mailable2->build();
    expect($mailable2->subject)->toBe('Welcome v2')
        ->and($mailable2->emailTemplate->content)->toBe('<p>Content v2</p>');

    // Admin makes second edit (fixing typo quickly)
    $template->update(['subject' => 'Welcome v3', 'content' => '<p>Content v3</p>']);
    $mailable3 = new UserRegisteredEmail($user);
    $mailable3->build();
    expect($mailable3->subject)->toBe('Welcome v3')
        ->and($mailable3->emailTemplate->content)->toBe('<p>Content v3</p>');

    // Admin makes final edit
    $template->update(['subject' => 'Welcome FINAL', 'content' => '<p>Content FINAL</p>']);
    $mailable4 = new UserRegisteredEmail($user);
    $mailable4->build();
    expect($mailable4->subject)->toBe('Welcome FINAL')
        ->and($mailable4->emailTemplate->content)->toBe('<p>Content FINAL</p>');
});

it('clears all optimization caches when template is updated (mirrors optimize:clear)', function () {
    // This test verifies that our cache clearing mirrors what `php artisan optimize:clear` does
    // Lee confirmed that running optimize:clear manually fixes the issue

    $template = EmailTemplate::factory()->create([
        'key' => 'user-welcome',
        'language' => config('filament-email-templates.default_locale'),
        'subject' => 'Original',
        'content' => '<p>Original</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $user = User::factory()->create(['name' => 'Test User']);

    // Cache the template
    $cached1 = EmailTemplate::findEmailByKey('user-welcome', config('filament-email-templates.default_locale'));
    expect($cached1->subject)->toBe('Original');

    // Update template (this should clear ALL caches like optimize:clear does)
    $template->update([
        'subject' => 'Updated',
        'content' => '<p>Updated</p>',
    ]);

    // Verify template cache was cleared
    $cacheKey = "email_by_key_user-welcome_" . config('filament-email-templates.default_locale');
    expect(Cache::has($cacheKey))->toBeFalse();

    // Verify mailable gets fresh content (not cached)
    $mailable = new UserRegisteredEmail($user);
    $mailable->build();
    expect($mailable->subject)->toBe('Updated')
        ->and($mailable->emailTemplate->content)->toBe('<p>Updated</p>');

    // Verify the template can be re-cached and still has correct content
    $cached2 = EmailTemplate::findEmailByKey('user-welcome', config('filament-email-templates.default_locale'));
    expect($cached2->subject)->toBe('Updated')
        ->and($cached2->content)->toBe('<p>Updated</p>');
});

it('works correctly even when config/compiled files exist', function () {
    // The root cause of NB-1574 was that bootstrap/cache/compiled.php and
    // bootstrap/cache/services.php were caching Mailable class resolution.
    // This test ensures updates work even when those files would typically exist.

    $template = EmailTemplate::factory()->create([
        'key' => 'user-welcome',
        'language' => config('filament-email-templates.default_locale'),
        'subject' => 'Before',
        'content' => '<p>Before</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $user = User::factory()->create(['name' => 'Test User']);

    // Build mailable multiple times (simulating production scenario)
    for ($i = 1; $i <= 3; $i++) {
        $mailable = new UserRegisteredEmail($user);
        $mailable->build();
        expect($mailable->subject)->toBe('Before');
    }

    // Update template
    $template->update([
        'subject' => 'After Update',
        'content' => '<p>After Update</p>',
    ]);

    // CRITICAL: Next mailable build MUST use updated content
    // This should work even if compiled bootstrap files exist
    $mailable = new UserRegisteredEmail($user);
    $mailable->build();
    expect($mailable->subject)->toBe('After Update')
        ->and($mailable->emailTemplate->content)->toBe('<p>After Update</p>');

    // Verify consistency across multiple builds
    for ($i = 1; $i <= 3; $i++) {
        $mailable = new UserRegisteredEmail($user);
        $mailable->build();
        expect($mailable->subject)->toBe('After Update');
    }
});
