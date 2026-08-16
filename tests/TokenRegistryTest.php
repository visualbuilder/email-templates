<?php

use Visualbuilder\EmailTemplates\Tests\Models\Order;
use Visualbuilder\EmailTemplates\Tests\Models\User;
use Visualbuilder\EmailTemplates\TokenRegistry;

beforeEach(function () {
    config()->set('filament-email-templates.recipients', [User::class]);
    config()->set('filament-email-templates.config_keys', ['app.name', 'app.url']);
    config()->set('filament-email-templates.known_tokens', ['tokenUrl', 'message']);
    config()->set('filament-email-templates.token_models', []);
    config()->set('filament-email-templates.preview_models', []);
});

it('is bound as a singleton', function () {
    expect(app(TokenRegistry::class))->toBe(app(TokenRegistry::class));
});

it('enumerates recipient model attributes from fillable minus hidden', function () {
    $groups = (new TokenRegistry)->groups();

    expect($groups->get('User')->pluck('token')->all())
        ->toBe(['##user.email##', '##user.name##']);
});

it('never exposes hidden attributes', function () {
    config()->set('filament-email-templates.token_models', ['order' => Order::class]);

    $tokens = (new TokenRegistry)->tokens()->pluck('token');

    expect($tokens)->not->toContain('##order.secret_note##')
        ->and($tokens)->not->toContain('##user.password##');
});

it('includes appended accessor attributes and declared tokenAttributes', function () {
    config()->set('filament-email-templates.token_models', ['order' => Order::class]);

    expect((new TokenRegistry)->groups()->get('Order')->pluck('token')->all())
        ->toBe(['##order.contact_name##', '##order.reference##', '##order.summary##', '##order.total##']);
});

it('still applies hidden and denylist to declared tokenAttributes', function () {
    config()->set('filament-email-templates.token_models', ['order' => Order::class]);

    $tokens = (new TokenRegistry)->tokens()->pluck('token');

    // Order::tokenAttributes() deliberately declares secret_note (hidden)
    // and api_token (denylisted) - neither may surface.
    expect($tokens)->not->toContain('##order.secret_note##')
        ->and($tokens)->not->toContain('##order.api_token##');
});

it('enumerates whitelisted config keys under the Config group', function () {
    expect((new TokenRegistry)->groups()->get('Config')->pluck('token')->all())
        ->toBe(['##config.app.name##', '##config.app.url##']);
});

it('enumerates known singular tokens under the General group', function () {
    expect((new TokenRegistry)->groups()->get('General')->pluck('token')->all())
        ->toBe(['##message##', '##tokenUrl##']);
});

it('registers preview models under their token prefix', function () {
    config()->set('filament-email-templates.preview_models', ['customer' => User::class]);

    expect((new TokenRegistry)->groups()->get('Customer')->pluck('token')->all())
        ->toBe(['##customer.email##', '##customer.name##']);
});

it('supports token_models with explicit attributes and label', function () {
    config()->set('filament-email-templates.token_models', [
        'order' => ['class' => Order::class, 'attributes' => ['reference'], 'label' => 'Sales Order'],
    ]);

    $groups = (new TokenRegistry)->groups();

    expect($groups->get('Sales Order')->pluck('token')->all())->toBe(['##order.reference##'])
        ->and($groups->has('Order'))->toBeFalse();
});

it('supports runtime model registration overriding config', function () {
    $registry = (new TokenRegistry)
        ->registerModel('user', User::class, ['email'], 'Recipient');

    expect($registry->groups()->get('Recipient')->pluck('token')->all())->toBe(['##user.email##'])
        ->and($registry->groups()->has('User'))->toBeFalse();
});

it('supports runtime config key and custom token registration', function () {
    $registry = (new TokenRegistry)
        ->registerConfigKeys(['mail.from.address'])
        ->registerToken('##unsubscribeUrl##', 'Unsubscribe link', 'Campaign');

    expect($registry->groups()->get('Config')->pluck('token'))->toContain('##config.mail.from.address##')
        ->and($registry->groups()->get('Campaign')->first())
        ->toBe(['token' => '##unsubscribeUrl##', 'label' => 'Unsubscribe link']);
});

it('sorts groups and flat tokens alphabetically', function () {
    config()->set('filament-email-templates.token_models', ['order' => Order::class]);

    $registry = new TokenRegistry;

    expect($registry->groups()->keys()->all())
        ->toBe(['Config', 'General', 'Order', 'User']);

    $flat = $registry->tokens();

    expect($flat->pluck('token')->all())->toBe($flat->pluck('token')->sort()->values()->all())
        ->and($flat->first())->toHaveKeys(['token', 'label', 'group']);
});

it('builds the editor menu payload grouped with items', function () {
    $menu = (new TokenRegistry)->menu();

    expect($menu)->toBeArray()
        ->and($menu[0])->toHaveKeys(['label', 'items'])
        ->and(collect($menu)->firstWhere('label', 'User')['items'])
        ->toBe([
            ['token' => '##user.email##', 'label' => 'email'],
            ['token' => '##user.name##', 'label' => 'name'],
        ]);
});
