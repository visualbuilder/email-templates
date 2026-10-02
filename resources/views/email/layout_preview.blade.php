
@php
    if (isset($data['colours'])) {
        $data['theme'] = $data['colours'];
    } elseif (isset($this) && isset($this->data['colours'])) {
        $data['theme'] = $this->data['colours'];
    }
@endphp

<div style="background-color: {{$data['theme']["body_bg_color"]}};">

@include('vb-email-templates::email.parts._body')

@include('vb-email-templates::email.parts._hero_title')

{!! $data['blocks'] ?? '' !!}

@include('vb-email-templates::email.parts._support_block_standalone')

@include('vb-email-templates::email.parts._footer')

@include('vb-email-templates::email.parts._unsubscribe')

</div>
