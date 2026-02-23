<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }} — Travel Blog</title>
        <link>{{ route('home') }}</link>
        <description>Travel stories and destinations from around the world.</description>
        <language>en-us</language>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        @foreach ($posts as $post)
        <item>
            <title><![CDATA[{{ $post->title }}]]></title>
            <link>{{ route('posts.show', $post->slug) }}</link>
            <guid>{{ route('posts.show', $post->slug) }}</guid>
            <description><![CDATA[{{ $post->excerpt }}]]></description>
            <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
            <category>{{ $post->category->value }}</category>
            @if ($post->coverPhoto)
            <enclosure
                url="{{ url('/storage/' . $post->coverPhoto->path) }}"
                type="image/webp"
                length="0"
            />
            @endif
        </item>
        @endforeach
    </channel>
</rss>
