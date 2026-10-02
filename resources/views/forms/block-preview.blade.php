@php
    $theme = \Visualbuilder\EmailTemplates\Models\EmailTemplateTheme::query()->where('is_default', true)->first()?->colours ?? [];
    $html = app(\Visualbuilder\EmailTemplates\Layout\EmailLayoutRenderer::class)->withTheme($theme)->renderBlock($type, $data, null);

    // The block HTML holds editor content, so it is shown in a sandboxed iframe (no scripts,
    // opaque origin) rather than inline in the admin page. Blade escapes it into srcdoc.
    $document = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="margin: 0;">'
        .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: '.(int) config('filament-email-templates.content_width').'px; margin: 0 auto;">'
        .$html
        .'</table></body></html>';
@endphp
<iframe
    sandbox
    srcdoc="{{ $document }}"
    title="{{ __('Email block preview') }}"
    loading="lazy"
    style="display: block; width: 100%; height: 320px; border: 0;"
></iframe>
