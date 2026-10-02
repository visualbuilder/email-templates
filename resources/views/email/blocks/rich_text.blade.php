<!-- BLOCK: TEXT -->
@include('vb-email-templates::email.parts._block_open', ['theme' => $theme])
{!! $block['content'] ?? '' !!}
@include('vb-email-templates::email.parts._block_close')
