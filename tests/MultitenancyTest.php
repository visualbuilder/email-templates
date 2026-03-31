<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;
use Visualbuilder\EmailTemplates\Tests\Models\Tenant;

beforeEach(function () {
    Cache::flush();
    $this->enableMultitenancy();

    $this->theme = EmailTemplateTheme::factory()->create(['is_default' => true]);

    $this->tenantA = $this->createTenant(['name' => 'Agency Alpha', 'slug' => 'alpha']);
    $this->tenantB = $this->createTenant(['name' => 'Agency Beta', 'slug' => 'beta']);
});

// ── Configuration tests ──

it('multitenancy is disabled by default', function () {
    Config::set('filament-email-templates.multitenancy.enabled', false);
    Config::set('filament-email-templates.multitenancy.tenant_model', null);
    Config::set('filament-email-templates.multitenancy.tenant_foreign_key', null);
    Config::set('filament-email-templates.multitenancy.ownership_relationship', null);

    expect(EmailTemplate::isMultitenancyEnabled())->toBeFalse();
});

it('multitenancy can be enabled via config', function () {
    expect(EmailTemplate::isMultitenancyEnabled())->toBeTrue();
});

it('tenant foreign key is derived from model class', function () {
    expect(EmailTemplate::getTenantForeignKeyName())->toBe('tenant_id');
});

it('ownership relationship is derived from model class', function () {
    expect(EmailTemplate::getOwnershipRelationshipName())->toBe('tenant');
});

it('tenant FK is added to fillable when enabled', function () {
    $template = new EmailTemplate;

    expect($template->getFillable())->toContain('tenant_id');
});

// ── findEmailByKey fallback tests ──

it('returns tenant-specific template when it exists', function () {
    $key = 'welcome';
    $lang = config('filament-email-templates.default_locale');

    // Global template
    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Global Welcome</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    // Tenant-specific template
    $tenantTemplate = EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Tenant A Welcome</p>',
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $result = EmailTemplate::findEmailByKey($key, $lang, $this->tenantA->id);

    expect($result->id)->toBe($tenantTemplate->id);
    expect($result->content)->toBe('<p>Tenant A Welcome</p>');
});

it('falls back to global template when tenant-specific is missing', function () {
    $key = 'fallback-test';
    $lang = config('filament-email-templates.default_locale');

    $globalTemplate = EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Global Content</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $result = EmailTemplate::findEmailByKey($key, $lang, $this->tenantA->id);

    expect($result->id)->toBe($globalTemplate->id);
    expect($result->content)->toBe('<p>Global Content</p>');
});

it('returns null when no template exists', function () {
    $result = EmailTemplate::findEmailByKey('nonexistent-key', 'en', $this->tenantA->id);

    expect($result)->toBeNull();
});

it('tenant-specific template takes priority over global', function () {
    $key = 'priority-test';
    $lang = config('filament-email-templates.default_locale');

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Global Version</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Tenant Version</p>',
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $result = EmailTemplate::findEmailByKey($key, $lang, $this->tenantA->id);

    expect($result->content)->toBe('<p>Tenant Version</p>');
});

it('different tenants get their own templates', function () {
    $key = 'tenant-isolation';
    $lang = config('filament-email-templates.default_locale');

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Alpha Content</p>',
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Beta Content</p>',
        'tenant_id' => $this->tenantB->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    $resultA = EmailTemplate::findEmailByKey($key, $lang, $this->tenantA->id);
    $resultB = EmailTemplate::findEmailByKey($key, $lang, $this->tenantB->id);

    expect($resultA->content)->toBe('<p>Alpha Content</p>');
    expect($resultB->content)->toBe('<p>Beta Content</p>');
});

it('findEmailByKey returns global template when no tenant context', function () {
    $key = 'no-context';
    $lang = config('filament-email-templates.default_locale');

    $global = EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Global Template</p>',
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    // No tenant ID passed, and no Filament context available in tests
    $result = EmailTemplate::findEmailByKey($key, $lang);

    expect($result->id)->toBe($global->id);
});

// ── Cache tests ──

it('cache keys include tenant ID', function () {
    $key = 'cache-tenant-key';
    $lang = config('filament-email-templates.default_locale');

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    EmailTemplate::findEmailByKey($key, $lang, $this->tenantA->id);

    $cacheKey = "email_by_key_{$key}_{$lang}_{$this->tenantA->id}";
    expect(Cache::has($cacheKey))->toBeTrue();
});

it('different tenants have separate cache entries', function () {
    $key = 'cache-separate';
    $lang = config('filament-email-templates.default_locale');

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Alpha</p>',
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Beta</p>',
        'tenant_id' => $this->tenantB->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    EmailTemplate::findEmailByKey($key, $lang, $this->tenantA->id);
    EmailTemplate::findEmailByKey($key, $lang, $this->tenantB->id);

    $cacheKeyA = "email_by_key_{$key}_{$lang}_{$this->tenantA->id}";
    $cacheKeyB = "email_by_key_{$key}_{$lang}_{$this->tenantB->id}";

    expect(Cache::has($cacheKeyA))->toBeTrue();
    expect(Cache::has($cacheKeyB))->toBeTrue();
});

it('updating tenant template clears tenant-specific cache', function () {
    $key = 'cache-clear-tenant';
    $lang = config('filament-email-templates.default_locale');

    $template = EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'content' => '<p>Original</p>',
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    EmailTemplate::findEmailByKey($key, $lang, $this->tenantA->id);
    $cacheKey = "email_by_key_{$key}_{$lang}_{$this->tenantA->id}";
    expect(Cache::has($cacheKey))->toBeTrue();

    $template->update(['content' => '<p>Updated</p>']);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('cache keys use none suffix when multitenancy disabled', function () {
    Config::set('filament-email-templates.multitenancy.enabled', false);

    $key = 'cache-none-suffix';
    $lang = config('filament-email-templates.default_locale');

    EmailTemplate::factory()->create([
        'key' => $key,
        'language' => $lang,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    EmailTemplate::findEmailByKey($key, $lang);

    $cacheKey = "email_by_key_{$key}_{$lang}_none";
    expect(Cache::has($cacheKey))->toBeTrue();
});

// ── isGlobal tests ──

it('isGlobal returns true for templates without tenant', function () {
    $template = EmailTemplate::factory()->create([
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    expect($template->isGlobal())->toBeTrue();
});

it('isGlobal returns false for tenant-specific templates', function () {
    $template = EmailTemplate::factory()->create([
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    expect($template->isGlobal())->toBeFalse();
});

it('isGlobal always returns true when multitenancy disabled', function () {
    $template = EmailTemplate::factory()->create([
        'tenant_id' => $this->tenantA->id,
        'view' => 'default',
        'from' => ['email' => 'test@example.com', 'name' => 'Test'],
    ]);

    Config::set('filament-email-templates.multitenancy.enabled', false);

    expect($template->isGlobal())->toBeTrue();
});

// ── Theme tests ──

it('themes support tenant assignment', function () {
    $theme = EmailTemplateTheme::factory()->create([
        'tenant_id' => $this->tenantA->id,
    ]);

    expect($theme->tenant_id)->toBe($this->tenantA->id);
});

it('isGlobal works on themes', function () {
    $globalTheme = EmailTemplateTheme::factory()->create();
    $tenantTheme = EmailTemplateTheme::factory()->create([
        'tenant_id' => $this->tenantA->id,
    ]);

    expect($globalTheme->isGlobal())->toBeTrue();
    expect($tenantTheme->isGlobal())->toBeFalse();
});
