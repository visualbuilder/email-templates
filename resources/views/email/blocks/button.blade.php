<!-- BLOCK: BUTTON -->
@include('vb-email-templates::email.parts._block_open', ['theme' => $theme, 'padding' => '10px 30px 10px 30px'])
@include('vb-email-templates::email.parts._block_button', ['theme' => $theme, 'label' => $block['label'] ?? '', 'url' => $block['url'] ?? '', 'align' => $block['align'] ?? 'center'])
@include('vb-email-templates::email.parts._block_close')
