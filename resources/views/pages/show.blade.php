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

@section('navbar-styles')
    @if ($page->navbar && $page->navbar->is_active && !empty($page->navbar->inline_styles))
        <style>
            {!! $page->navbar->inline_styles !!}
        </style>
    @endif
@endsection

@section('footer-styles')
    @if ($page->footer && $page->footer->is_active && !empty($page->footer->inline_styles))
        <style>
            {!! $page->footer->inline_styles !!}
        </style>
    @endif
@endsection

@section('navbar')
    @if ($page->navbar && $page->navbar->is_active)
        {!! $page->navbar->html_content !!}
    @endif
@endsection

@section('content')
    {!! $page->html_content !!}
@endsection

@section('footer')
    @if ($page->footer && $page->footer->is_active)
        {!! $page->footer->html_content !!}
    @endif
@endsection

@section('page-scripts')
    @if ($page->js_content)
        <script type="module" src="{{ route('page.script', $page->slug) }}?v={{ $page->updated_at->timestamp }}"></script>
    @endif
@endsection
