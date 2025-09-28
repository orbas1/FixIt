@component('mail::message')
            <h3 class="card-title">{{ @$content['title'][$locale] }}</h3>
            <p class="card-text">{!! @$emailContent !!}</p>
            @component('mail::button', ['url' => @$content['button_url'][$locale]])
                {{@$content['button_text'][$locale] }}
            @endcomponent  
@endcomponent

