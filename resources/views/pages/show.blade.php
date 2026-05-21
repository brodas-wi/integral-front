@extends('layouts.app')

@section('title', $page->title)

@section('meta')
    <meta name="description" content="{{ Str::limit(strip_tags($page->html_content ?? ''), 160) }}">
    <meta property="og:title" content="{{ $page->title }}">
    <meta property="og:type" content="website">
@endsection

@section('page-styles')
    @if ($page->css_content)
        <link rel="stylesheet" href="{{ route('page.styles', $page->slug) }}?v={{ $page->updated_at->timestamp }}">
    @endif
@endsection

@section('content')
    {!! $page->html_content !!}
@endsection

@section('footer')
    @if($page->footer && $page->footer->is_active)
        @if($page->footer->css_content)
            <style>{!! $page->footer->css_content !!}</style>
        @endif

        {!! $page->footer->html_content !!}

        @if($page->footer->js_content)
            <script>{!! $page->footer->js_content !!}</script>
        @endif
    @endif
@endsection

@section('page-scripts')
    @if ($page->js_content)
        <script type="module" src="{{ route('page.script', $page->slug) }}?v={{ $page->updated_at->timestamp }}"></script>
    @endif
@endsection
