<!-- BLOCK: IMAGE -->
@php($imageWidth = (int) config('filament-email-templates.content_width') - 60)
@if(filled($block['image'] ?? null))
@include('vb-email-templates::email.parts._block_open', ['theme' => $theme, 'align' => 'center'])
@if(filled($block['link_url'] ?? null))<a href="{{ $block['link_url'] }}" target="_blank" style="display: block;">@endif
<img src="{{ $block['image'] }}" alt="{{ $block['alt'] ?? '' }}" width="{{ $imageWidth }}" style="display: block; width: 100%; max-width: {{ $imageWidth }}px; height: auto; border: 0; margin: 0 auto;">
@if(filled($block['link_url'] ?? null))</a>@endif
@include('vb-email-templates::email.parts._block_close')
@endif
