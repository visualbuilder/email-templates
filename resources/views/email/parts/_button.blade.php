<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center" style="padding: 20px 30px 20px 30px;">
            <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td align="center" style="border-radius: 3px;" bgcolor="{{$data['theme']["button_bg_color"]}}">
                        <a data-py="actionBtn" href="{{$url??''}}" target="_blank"
                            style="font-size: 20px;
                            font-family: Helvetica, Arial, sans-serif;
                            color: {{$data['theme']["button_color"]}};
                            text-decoration: none; padding: 15px 25px; border-radius: 2px;
                            border: 1px solid {{$data['theme']["button_bg_color"]}}; display: inline-block;">
                            {{$title??''}}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
