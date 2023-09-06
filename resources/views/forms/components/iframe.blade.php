<iframe
    src="{{ $record->previewUrl() }}"
    style="width: {{ $width??'100%' }}; height: {{ $height??'100vh;' }}"
    name="{{ $name??'' }}"
    >
    {{__('vb-email-templates::email-templates.general-labels.browser-not-compatible')}}
</iframe>
