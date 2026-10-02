@php
    $theme = \Visualbuilder\EmailTemplates\Models\EmailTemplateTheme::query()->where('is_default', true)->first()?->colours ?? [];
    $html = app(\Visualbuilder\EmailTemplates\Layout\EmailLayoutRenderer::class)->withTheme($theme)->renderBlock($type, $data, null);
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: {{ config('filament-email-templates.content_width') }}px; margin: 0 auto;">
    {!! $html !!}
</table>
