<!-- FOOTER -->
<tr>
	<td bgcolor="{{$data['theme']["footer_bg_color"]}}" align="center" style="padding: 0px 10px 0px 10px;">
		<!--[if (gte mso 9)|(IE)]>
        <table align="center" border="0" cellspacing="0" cellpadding="0" width="{{config('filament-email-templates.content_width')}}">
            <tr>
                <td align="center" valign="top" width="{{config('filament-email-templates.content_width')}}">
        <![endif]-->
				<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: {{config('filament-email-templates.content_width')}}px;">
					<!-- NAVIGATION -->
					<tr>
						<td bgcolor="{{$data['theme']["body_bg_color"]}}" align="left" style="padding: 30px; color: {{$data['theme']["body_color"]}}; border-radius: 4px 4px 4px 4px;
		                font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;">
							<p style="margin: 0;">
								@foreach(config('filament-email-templates.links') as $link)
									<a href="{{$link['url']}}" target="_blank" style="font-weight: 700;" title="{{$link['title']}}">{{$link['name']}}</a>
									@if(! $loop->last)
										|
									@endif
								@endforeach
							</p>
						</td>
					</tr>
				</table>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: {{config('filament-email-templates.content_width')}}px; margin: 20px 0">

					<!-- ADDRESS -->
					<tr>
						<td bgcolor="{{$data['theme']["body_bg_color"]}}" align="left"
						    style="padding: 30px; color: {{$data['theme']["body_color"]}}; border-radius: 4px 4px 4px 4px; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 14px;
						    font-weight: 400; line-height: 18px;">
							<p style="margin: 0;"> &copy; <?= date('Y'); ?> {{config('app.name')}}. {{__('vb-email-templates::email-templates.general-labels.all-rights-reserved')}}.</p>
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

