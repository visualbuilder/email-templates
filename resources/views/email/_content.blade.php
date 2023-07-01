<!-- COPY BLOCK -->
<tr>
    <td bgcolor="{{config('email-templates.body_bg_color')}}" align="center" style="padding: 0px 10px 0px 10px; background-color: {{config('email-templates.body_bg_color')}}">
        <!--[if (gte mso 9)|(IE)]>
        <table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
            <tr>
                <td align="center" valign="top" width="600">
        <![endif]-->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin-bottom: 30px" >
            <!-- COPY -->
            <tr>
                <td bgcolor="{{config('email-templates.content_bg_color')}}" align="left" style="padding: 20px 30px 40px 30px; color: {{config('email-templates.body_color')}}; font-family: 'Lato',
                Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 1.8;" >

                    {!! $data['content']??'' !!}

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