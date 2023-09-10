<!-- HERO -->
<tr>
    <td bgcolor="{{$data['theme']["header_bg_color"]}}" align="center" style="padding: 0px 10px 0px 10px;color: {{$data['theme']["body_color"]}};">
        <!--[if (gte mso 9)|(IE)]>
        <table align="center" border="0" cellspacing="0" cellpadding="0" width="{{config('filament-email-templates.content_width')}}">
            <tr>
                <td align="center" valign="top" width="{{config('filament-email-templates.content_width')}}">
        <![endif]-->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: {{config('filament-email-templates.content_width')}}px;">
            <tr>
                <td bgcolor="{{$data['theme']["content_bg_color"]}}" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: {{config('filament-email-templates
                .body_color')}}; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                    <h1 style="font-size: 38px; font-weight: 400; margin: 0;">{{ $data['title'] ?? '' }}</h1>
                </td>
            </tr>
        </table>
        <!--[if (gte mso 9)|(IE)]>
        </td>
        </tr>
        </table>
        <![endif]-->
    </td>
</tr>