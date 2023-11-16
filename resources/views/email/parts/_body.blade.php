
    <!-- HIDDEN PREHEADER TEXT -->
    <div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: 'Lato', Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        {{ $data['preheader'] ?? '' }}
    </div>

    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <!-- LOGO -->
        <tr>
            <td bgcolor="{{$data['theme']["header_bg_color"]}}" align="center" style="background-color: {{$data['theme']["header_bg_color"]}}">
                <!--[if (gte mso 9)|(IE)]>
                <table align="center" border="0" cellspacing="0" cellpadding="0" width="{{config('filament-email-templates.content_width')}}">
                    <tr>
                        <td align="center" valign="top" width="{{config('filament-email-templates.content_width')}}">
                <![endif]-->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: {{config('filament-email-templates.content_width')}}px;" >
                    <tr>
                        <td align="center" valign="top" style="padding: 30px 10px 30px 10px;">
                            <a href="{{\Illuminate\Support\Facades\URL::to('/')}}" target="_blank" title="{{config('app.name')}}">
                                <img alt="{{config('app.name')}} Logo"
                                     src="{{$data['logo']}}"
                                     width="{{config('filament-email-templates.logo_width')}}"
                                     height="{{config('filament-email-templates.logo_height')}}"
                                     style="display: block; width: {{config('filament-email-templates.logo_width')}}px; max-width: {{config('filament-email-templates.logo_width')}}px; min-width: {{config
                                     ('email-templates.logo_width')}}px;" border="0">
                            </a>
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