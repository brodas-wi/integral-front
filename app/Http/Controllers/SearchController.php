<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    private const MIN_LENGTH = 2;
    private const MAX_LENGTH = 100;
    private const MAX_RESULTS = 30;
    private const MAX_CANDIDATES = 150;
    private const CACHE_SECONDS = 300;

    public function index(Request $request): JsonResponse
    {
        $query = trim(preg_replace('/\s+/u', ' ', (string) $request->query('q', '')) ?? '');
        $query = mb_substr($query, 0, self::MAX_LENGTH);

        if (mb_strlen($query) < self::MIN_LENGTH) {
            return response()->json(['query' => $query, 'results' => []]);
        }

        $needle = $this->fold($query);

        $results = Cache::remember(
            'site-search:' . md5($needle),
            self::CACHE_SECONDS,
            fn () => $this->search($query, $needle)
        );

        return response()->json(['query' => $query, 'results' => $results]);
    }

    private function search(string $query, string $needle): array
    {
        $like = '%' . addcslashes($query, '%_\\') . '%';

        $pages = Page::published()
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('html_content', 'like', $like);
            })
            ->select('id', 'title', 'slug', 'html_content')
            ->limit(self::MAX_CANDIDATES)
            ->get();

        return $pages
            ->map(function ($page) use ($needle) {
                $text = $this->plainText((string) $page->html_content);
                $inTitle = str_contains($this->fold((string) $page->title), $needle);
                $inSlug = str_contains($this->fold(str_replace('-', ' ', (string) $page->slug)), $needle);
                $occurrences = $text === '' ? 0 : substr_count($this->fold($text), $needle);

                if (!$inTitle && !$inSlug && $occurrences === 0) {
                    return null;
                }

                return [
                    'score' => ($inTitle ? 100 : 0) + ($inSlug ? 20 : 0) + min($occurrences, 20),
                    'title' => (string) $page->title,
                    'url' => url('/' . ltrim((string) $page->slug, '/')),
                    'excerpt' => $this->excerpt($text, $needle),
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take(self::MAX_RESULTS)
            ->map(fn ($item) => [
                'title' => $item['title'],
                'url' => $item['url'],
                'excerpt' => $item['excerpt'],
            ])
            ->values()
            ->all();
    }

    private function fold(string $value): string
    {
        return Str::lower(Str::ascii($value));
    }

    private function plainText(string $html): string
    {
        $html = preg_replace('#<(script|style|noscript|template)\b[^>]*>.*?</\1>#is', ' ', $html) ?? '';
        $html = preg_replace('/<[^>]+>/', ' ', $html) ?? '';
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    private function excerpt(string $text, string $needle): string
    {
        if ($text === '') {
            return '';
        }

        $folded = $this->fold($text);
        $position = mb_strlen($folded) === mb_strlen($text) ? mb_strpos($folded, $needle) : false;

        if ($position === false) {
            return Str::limit($text, 140);
        }

        $start = max(0, $position - 50);
        $length = 150;
        $snippet = trim(mb_substr($text, $start, $length));

        return ($start > 0 ? '…' : '') . $snippet . (($start + $length) < mb_strlen($text) ? '…' : '');
    }
}