<iframe
    src="{{ $getRecord()->previewUrl }}"
    style="width: {{ $width??'700' }}px; height: {{ $height??'500' }}px"
    name="{{ $name??'' }}"
    >
    {{__('vb-email-templates::email-templates.general-labels.browser-not-compatible')}}
</iframe>
