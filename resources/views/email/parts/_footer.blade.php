<!-- FOOTER -->
<tr>
    <td bgcolor="{{config('email-templates.footer_bg_color')}}" align="center" style="padding: 0px 10px 0px 10px;">
        <!--[if (gte mso 9)|(IE)]>
        <table align="center" border="0" cellspacing="0" cellpadding="0" width="{{config('email-templates.content_width')}}">
            <tr>
                <td align="center" valign="top" width="{{config('email-templates.content_width')}}">
        <![endif]-->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: {{config('email-templates.content_width')}}px; margin-bottom: 30px" >
            <!-- NAVIGATION -->
            <tr>
                <td bgcolor="{{config('email-templates.body_bg_color')}}" align="left" style="padding: 30px 30px 30px 30px; color: {{config('email-templates.body_color')}}; border-radius: 4px 4px 4px 4px;
                font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;" >
                    <p style="margin: 0;">
                        @foreach(config('email-templates.links') as $link)
                        <a href="{{$link['url']}}" target="_blank" style="font-weight: 700;" title="{{$link['title']}}">{{$link['name']}}</a>
                           @if(! $loop->last) | @endif
                        @endforeach
                    </p>
                </td>
            </tr>

            <!-- ADDRESS -->
            <tr>
                <td bgcolor="{{config('email-templates.body_bg_color')}}" align="left" style="padding: 0px 30px 30px 30px; color: {{config('email-templates.body_color')}}; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;" >
                    <hr>
                    <p> &copy; <?= date('Y'); ?> {{config('app.name')}}. {{__('vb-email-templates::email-templates.general-labels.all-rights-reserved')}}.</p>
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
</table>

</body>
</html>
