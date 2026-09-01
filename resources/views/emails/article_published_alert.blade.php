<x-mail::message>
# New Article Published: {{ $article->title }}

Hello {{ $subscriber->first_name ?? 'Subscriber' }},

We have just published a new article that matches your subscribed topics!

## {{ $article->title }}
{{ $article->summary }}

<x-mail::button :url="config('app.url') . '/articles/' . $article->slug">
Read Article
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
