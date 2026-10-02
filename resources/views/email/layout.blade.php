@include('vb-email-templates::email.parts._head')

@include('vb-email-templates::email.parts._body')

@include('vb-email-templates::email.parts._hero_title')

{!! $data['blocks'] ?? '' !!}

@include('vb-email-templates::email.parts._support_block_standalone')

@include('vb-email-templates::email.parts._footer')

@include('vb-email-templates::email.parts._unsubscribe')

@include('vb-email-templates::email.parts._closing_tags')
