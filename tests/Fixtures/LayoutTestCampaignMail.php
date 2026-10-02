<?php

namespace Visualbuilder\EmailTemplates\Tests\Fixtures;

/** Like a campaign mailable: carries a public unsubscribe URL. */
class LayoutTestCampaignMail extends LayoutTestMail
{
    public string $unsubscribeUrl = 'https://example.com/unsubscribe/abc123';
}
