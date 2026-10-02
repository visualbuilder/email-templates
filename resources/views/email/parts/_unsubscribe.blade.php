@if(! empty($data['unsubscribeUrl']))
@php($unsubscribeBg = $data['theme']['footer_bg_color'] ?? $data['theme']['body_bg_color'] ?? '#ffffff')
<!-- UNSUBSCRIBE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="{{ $unsubscribeBg }}" align="center" style="padding: 0 10px 30px 10px; background-color: {{ $unsubscribeBg }}; font-family: {{ config('filament-email-templates.font_family') }}; font-size: 12px; line-height: 18px; mso-line-height-rule: exactly; color: {{ $data['theme']['footer_color'] ?? $data['theme']['body_color'] ?? '#333333' }};">
            You are receiving this because you subscribed. <a href="{{ $data['unsubscribeUrl'] }}" target="_blank" style="color: {{ $data['theme']['anchor_color'] ?? '#1e88e5' }};">Unsubscribe</a>
        </td>
    </tr>
</table>
@endif
