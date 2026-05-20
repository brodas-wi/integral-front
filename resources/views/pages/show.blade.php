@extends('layouts.app')

@section('title', $page->title)

@section('meta')
    <meta name="description" content="{{ Str::limit(strip_tags($page->html_content ?? ''), 160) }}">
    <meta property="og:title" content="{{ $page->title }}">
    <meta property="og:type" content="website">
@endsection

@section('page-styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: { preflight: false }
        }
    </script>
    @if ($page->css_content)
        <link rel="stylesheet" href="{{ route('page.styles', $page->slug) }}">
    @endif
@endsection

@section('content')
    {!! $page->html_content !!}
@endsection

@section('page-scripts')
    @if ($page->js_content)
        <script type="module" src="{{ route('page.script', $page->slug) }}"></script>
    @endif
@endsection
