<!-- BLOCK: HERO -->
@php($imageWidth = (int) config('filament-email-templates.content_width') - 60)
@include('vb-email-templates::email.parts._block_open', ['theme' => $theme, 'padding' => '30px 30px 30px 30px'])
@if(filled($block['image'] ?? null))
    <img src="{{ $block['image'] }}" alt="{{ $block['image_alt'] ?? '' }}" width="{{ $imageWidth }}" style="display: block; width: 100%; max-width: {{ $imageWidth }}px; height: auto; border: 0; margin: 0 0 20px 0;">
@endif
<h2 style="margin: 0 0 10px 0; font-family: {{ config('filament-email-templates.font_family') }}; font-size: 28px; font-weight: 700; mso-line-height-rule: exactly; line-height: 34px; color: {{ $theme['body_color'] ?? '#333333' }};">{{ $block['heading'] ?? '' }}</h2>
@if(filled($block['text'] ?? null))
    <p style="margin: 0 0 10px 0; mso-line-height-rule: exactly; line-height: 28px;">{!! nl2br(e($block['text'])) !!}</p>
@endif
@if(filled($block['button_label'] ?? null) && filled($block['button_url'] ?? null))
    @include('vb-email-templates::email.parts._block_button', ['theme' => $theme, 'label' => $block['button_label'], 'url' => $block['button_url'], 'align' => 'left'])
@endif
@include('vb-email-templates::email.parts._block_close')
