{{--
    Opens one block row: a full-width band in body_bg_color (as _content.blade.php),
    an Outlook ghost table at content_width, and the content cell.
    Expects $theme; optional $padding and $align for the content cell.
    Close with _block_close.
--}}
@php
    $contentWidth = (int) config('filament-email-templates.content_width');
    $bandBg = $theme['body_bg_color'] ?? '#ffffff';
    $cellBg = $theme['content_bg_color'] ?? '#ffffff';
@endphp
<tr>
    <td bgcolor="{{ $bandBg }}" align="center" style="padding: 0 10px 0 10px; background-color: {{ $bandBg }};">
        <!--[if (gte mso 9)|(IE)]>
        <table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0" width="{{ $contentWidth }}">
            <tr>
                <td align="center" valign="top" width="{{ $contentWidth }}">
        <![endif]-->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: {{ $contentWidth }}px; table-layout: fixed;">
            <tr>
                <td bgcolor="{{ $cellBg }}" align="{{ $align ?? 'left' }}" style="padding: {{ $padding ?? '20px 30px 20px 30px' }}; background-color: {{ $cellBg }}; color: {{ $theme['body_color'] ?? '#333333' }}; font-family: {{ config('filament-email-templates.font_family') }}; font-size: 18px; font-weight: 400; mso-line-height-rule: exactly; line-height: 1.8; word-wrap: break-word; word-break: break-word; overflow-wrap: anywhere;">
