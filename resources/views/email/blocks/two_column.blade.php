<!-- BLOCK: TWO COLUMNS -->
@php
    // Each column holds (content_width - 80) / 2 of content; 10px side padding per
    // column gives a 20px gutter side by side and keeps the 30px edge when stacked.
    $column = intdiv((int) config('filament-email-templates.content_width') - 80, 2);
    $cellBg = $theme['content_bg_color'] ?? '#ffffff';
@endphp
@include('vb-email-templates::email.parts._block_open', ['theme' => $theme, 'padding' => '20px 20px 20px 20px', 'align' => 'center'])
<div style="font-size: 0; text-align: left;">
<!--[if mso]><table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td width="{{ $column + 20 }}" valign="top"><![endif]-->
@foreach(['left', 'right'] as $side)
@if($side === 'right')
<!--[if mso]></td><td width="{{ $column + 20 }}" valign="top"><![endif]-->
@endif
<div style="display: inline-block; width: 100%; max-width: {{ $column + 20 }}px; vertical-align: top;">
    <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td bgcolor="{{ $cellBg }}" valign="top" style="padding: 0 10px 10px 10px; background-color: {{ $cellBg }}; color: {{ $theme['body_color'] ?? '#333333' }}; font-family: {{ config('filament-email-templates.font_family') }}; font-size: 18px; font-weight: 400; mso-line-height-rule: exactly; line-height: 1.8; text-align: left; word-wrap: break-word; word-break: break-word; overflow-wrap: anywhere;">
                @if(filled($block[$side.'_image'] ?? null))
                    <img src="{{ $block[$side.'_image'] }}" alt="{{ $block[$side.'_image_alt'] ?? '' }}" width="{{ $column }}" style="display: block; width: 100%; max-width: {{ $column }}px; height: auto; border: 0; margin: 0 0 10px 0;">
                @endif
                {!! $block[$side.'_content'] ?? '' !!}
            </td>
        </tr>
    </table>
</div>
@endforeach
<!--[if mso]></td></tr></table><![endif]-->
</div>
@include('vb-email-templates::email.parts._block_close')
