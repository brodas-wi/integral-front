<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::published()
            ->whereIn('slug', ['home', 'inicio', 'index'])
            ->first();

        if (!$page) {
            return view('pages.coming-soon');
        }

        $page->html_content = $this->sanitizeContent($page->html_content ?? '');
        return view('pages.show', compact('page'));
    }

    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();
        $page->html_content = $this->sanitizeContent($page->html_content ?? '');
        return view('pages.show', compact('page'));
    }

    public function styles(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return response($page->css_content ?? '', 200)
            ->header('Content-Type', 'text/css; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function script(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return response($page->js_content ?? '', 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function sanitizeContent(string $html): string
    {
        $adminStorage = rtrim((string) config('app.storage_url', ''), '/');

        if (empty($adminStorage)) {
            return $html;
        }

        $html = preg_replace(
            '/\b(src|href)="\/storage\//i',
            '$1="' . $adminStorage . '/',
            $html
        );

        $html = str_replace(
            ["url('/storage/", 'url("/storage/'],
            ["url('" . $adminStorage . '/', 'url("' . $adminStorage . '/'],
            $html
        );

        return $html;
    }
}
