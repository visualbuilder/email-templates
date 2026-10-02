{{--
    Bulletproof button: VML roundrect for Outlook desktop, a padded link elsewhere.
    Expects $theme, $label, $url; optional $align (left|center|right).
--}}
@php
    $buttonBg = $theme['button_bg_color'] ?? '#1e88e5';
    $buttonColor = $theme['button_color'] ?? '#ffffff';
    $buttonAlign = in_array($align ?? 'center', ['left', 'center', 'right'], true) ? ($align ?? 'center') : 'center';
    $buttonWidth = min(
        (int) config('filament-email-templates.content_width') - 60,
        max(160, mb_strlen((string) $label) * 11 + 60)
    );
@endphp
<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td align="{{ $buttonAlign }}" bgcolor="{{ $theme['content_bg_color'] ?? '#ffffff' }}" style="padding: 10px 0 10px 0; background-color: {{ $theme['content_bg_color'] ?? '#ffffff' }};">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:50px;v-text-anchor:middle;width:{{ $buttonWidth }}px;" arcsize="8%" stroke="f" fillcolor="{{ $buttonBg }}">
                <w:anchorlock/>
                <center style="color:{{ $buttonColor }};font-family:Helvetica, Arial, sans-serif;font-size:18px;font-weight:bold;">{{ $label }}</center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-- -->
            <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="display: inline-table;">
                <tr>
                    <td align="center" bgcolor="{{ $buttonBg }}" style="border-radius: 4px; background-color: {{ $buttonBg }};">
                        <a href="{{ $url }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-family: {{ config('filament-email-templates.font_family') }}; font-size: 18px; font-weight: 700; line-height: 22px; mso-line-height-rule: exactly; color: {{ $buttonColor }}; text-decoration: none; border-radius: 4px; border: 1px solid {{ $buttonBg }}; background-color: {{ $buttonBg }}; word-break: break-word;">{{ $label }}</a>
                    </td>
                </tr>
            </table>
            <!--<![endif]-->
        </td>
    </tr>
</table>
