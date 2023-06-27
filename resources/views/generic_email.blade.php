@include('vb::emails.header',['preHeaderText'=>$preHeaderText])

@include('vb::emails.hero_title',['title'=>$title])

@include('vb::emails.content')

@include('vb::emails.footer')