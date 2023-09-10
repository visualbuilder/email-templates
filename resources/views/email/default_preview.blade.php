
<?php $data['theme'] = $this->data['colours'] ?>

<div style="background-color: {{$data['theme']["body_bg_color"]}};">

@include('vb-email-templates::email.parts._body')

@include('vb-email-templates::email.parts._hero_title')

@include('vb-email-templates::email.parts._content')

@include('vb-email-templates::email.parts._support_block')

@include('vb-email-templates::email.parts._footer')

</div>

