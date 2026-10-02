<?php

use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Tests\Fixtures\LayoutTestCampaignMail;
use Visualbuilder\EmailTemplates\Tests\Fixtures\LayoutTestMail;
use Visualbuilder\EmailTemplates\Tests\Models\User;

beforeEach(function () {
    $this->makeTheme();
    $this->user = User::factory()->create(['name' => 'Grace Hopper']);
});

function layoutTemplate(?array $layout): EmailTemplate
{
    return EmailTemplate::factory()->create([
        'key' => 'layout-test',
        'title' => 'Hello there',
        'content' => '<p>Classic body for ##user.name##</p>',
        'layout' => $layout,
    ]);
}

it('sends a layout template through the layout view with blocks in order, support block and footer', function () {
    layoutTemplate([
        'u1' => ['type' => 'rich_text', 'data' => ['content' => '<p>First block for ##user.name##</p>']],
        'u2' => ['type' => 'button', 'data' => ['label' => 'Second block', 'url' => 'https://example.com', 'align' => 'center']],
        'u3' => ['type' => 'divider', 'data' => ['spacing' => 'small', 'show_line' => true]],
    ]);

    $mailable = new LayoutTestMail($this->user);
    $html = $mailable->render();

    expect($mailable->view)->toBe('vb-email-templates::email.layout')
        ->and($html)->toContain('<p>First block for Grace Hopper</p>')
        ->toContain('<!-- SUPPORT CALLOUT -->')
        ->toContain('<!-- FOOTER -->')
        ->toContain('Hello there')
        ->not->toContain('Classic body')
        ->not->toContain('<!-- UNSUBSCRIBE -->')
        ->and(strpos($html, 'First block'))->toBeLessThan(strpos($html, 'Second block'))
        ->and(strpos($html, 'Second block'))->toBeLessThan(strpos($html, '<!-- BLOCK: DIVIDER -->'))
        ->and(strpos($html, '<!-- BLOCK: DIVIDER -->'))->toBeLessThan(strpos($html, '<!-- SUPPORT CALLOUT -->'))
        ->and(strpos($html, '<!-- SUPPORT CALLOUT -->'))->toBeLessThan(strpos($html, '<!-- FOOTER -->'));
});

it('keeps sending the content body through the default view when layout is null', function () {
    layoutTemplate(null);

    $mailable = new LayoutTestMail($this->user);
    $html = $mailable->render();

    expect($mailable->view)->toBe('vb-email-templates::email.default')
        ->and($html)->toContain('<p>Classic body for Grace Hopper</p>')
        ->toContain('<!-- COPY BLOCK -->')
        ->not->toContain('<!-- BLOCK:');
});

it('treats an empty layout array as classic mode', function () {
    $template = layoutTemplate([]);

    expect($template->usesLayout())->toBeFalse()
        ->and($template->renderViewPath())->toBe('vb-email-templates::email.default')
        ->and((new LayoutTestMail($this->user))->render())->toContain('Classic body for Grace Hopper');
});

it('renders the unsubscribe row when the mailable has an unsubscribe URL', function () {
    layoutTemplate([['type' => 'rich_text', 'data' => ['content' => '<p>Campaign</p>']]]);

    $html = (new LayoutTestCampaignMail($this->user))->render();

    expect($html)->toContain('<!-- UNSUBSCRIBE -->')
        ->toContain('<a href="https://example.com/unsubscribe/abc123"')
        ->toContain('bgcolor="#34495E"')
        ->and(strpos($html, '<!-- FOOTER -->'))->toBeLessThan(strpos($html, '<!-- UNSUBSCRIBE -->'));
});

it('does not render the unsubscribe row for a classic template', function () {
    layoutTemplate(null);

    expect((new LayoutTestCampaignMail($this->user))->render())->not->toContain('<!-- UNSUBSCRIBE -->');
});
