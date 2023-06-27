@include('vendor.visual-builder.email-templates.header',['preHeaderText'=>$data['preheader']])

@include('vendor.visual-builder.email-templates.hero_title',['title'=>$data['title']])

@include('vendor.visual-builder.email-templates.content')

@include('vendor.visual-builder.email-templates.footer')