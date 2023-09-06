<iframe
    src="data:text/html;base64,{{ $record->getBase64EmailPreviewData() }}"
    style="width: {{ $width??'100%' }}; height: {{ $height??'100vh;' }}"
    name="{{ $name??'' }}"
>
    {{__('vb-email-templates::email-templates.general-labels.browser-not-compatible')}}
</iframe>
