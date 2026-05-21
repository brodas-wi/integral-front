<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::published()
            ->with(['navbar', 'footer'])
            ->whereIn('slug', ['home', 'inicio', 'index'])
            ->first();

        if (!$page) {
            return view('pages.coming-soon');
        }

        $page->html_content = $this->sanitizeContent($page->html_content ?? '');

        if ($page->navbar) {
            $extracted = $this->extractStyles($this->sanitizeContent($page->navbar->html_content ?? ''));
            $page->navbar->html_content  = $extracted['html'];
            $page->navbar->inline_styles = $extracted['styles'];
        }
        if ($page->footer) {
            $extracted = $this->extractStyles($this->sanitizeContent($page->footer->html_content ?? ''));
            $page->footer->html_content  = $extracted['html'];
            $page->footer->inline_styles = $extracted['styles'];
        }

        return view('pages.show', compact('page'));
    }

    public function show(string $slug)
    {
        $page = Page::published()
            ->with(['navbar', 'footer'])
            ->where('slug', $slug)
            ->firstOrFail();

        $page->html_content = $this->sanitizeContent($page->html_content ?? '');

        if ($page->navbar) {
            $extracted = $this->extractStyles($this->sanitizeContent($page->navbar->html_content ?? ''));
            $page->navbar->html_content  = $extracted['html'];
            $page->navbar->inline_styles = $extracted['styles'];
        }
        if ($page->footer) {
            $extracted = $this->extractStyles($this->sanitizeContent($page->footer->html_content ?? ''));
            $page->footer->html_content  = $extracted['html'];
            $page->footer->inline_styles = $extracted['styles'];
        }

        return view('pages.show', compact('page'));
    }

    public function styles(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $etag = md5($page->updated_at . $page->css_content);

        if (request()->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        return response($page->css_content ?? '', 200)
            ->header('Content-Type', 'text/css; charset=UTF-8')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('ETag', $etag)
            ->header('Last-Modified', $page->updated_at->toRfc7231String());
    }

    public function script(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $etag = md5($page->updated_at . $page->js_content);

        if (request()->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        return response($page->js_content ?? '', 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('ETag', $etag)
            ->header('Last-Modified', $page->updated_at->toRfc7231String());
    }

    private function sanitizeContent(string $html): string
    {
        $adminStorage = rtrim((string) config('app.storage_url', ''), '/');

        if (!empty($adminStorage)) {
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
        }

        return $html;
    }

    public function extractStyles(string $html): array
    {
        $styles = [];
        $clean  = preg_replace_callback(
            '/<style[^>]*>(.*?)<\/style>/is',
            function ($m) use (&$styles) {
                $styles[] = $m[1];
                return '';
            },
            $html
        );
        return ['html' => $clean ?? $html, 'styles' => implode("\n", $styles)];
    }
}
