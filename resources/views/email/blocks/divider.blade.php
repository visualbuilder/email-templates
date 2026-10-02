<!-- BLOCK: DIVIDER -->
@php
    $space = ['small' => 16, 'medium' => 32, 'large' => 48][$block['spacing'] ?? 'medium'] ?? 32;
    $showLine = (bool) ($block['show_line'] ?? true);
    $cellBg = $theme['content_bg_color'] ?? '#ffffff';
@endphp
@include('vb-email-templates::email.parts._block_open', ['theme' => $theme, 'padding' => $space.'px 30px '.$space.'px 30px'])
<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td bgcolor="{{ $cellBg }}" height="1" style="height: 1px; font-size: 1px; line-height: 1px; mso-line-height-rule: exactly; background-color: {{ $cellBg }}; border-top: {{ $showLine ? '1px solid '.($block['line_color'] ?? '#e5e7eb') : '0' }};">&nbsp;</td>
    </tr>
</table>
@include('vb-email-templates::email.parts._block_close')
