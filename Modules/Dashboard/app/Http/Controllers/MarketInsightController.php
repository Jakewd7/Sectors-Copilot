<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MarketInsight;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketInsightController extends Controller
{
    public function index(Request $request): View
    {
        $categories = MarketInsight::query()
            ->published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $paginator = MarketInsight::query()
            ->published()
            ->with('author')
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->latest('published_at')
            ->paginate(6)
            ->withQueryString();

        $insights = collect($paginator->items())->map(fn (MarketInsight $item) => [
            'id' => $item->id,
            'slug' => $item->slug,
            'title' => $item->title,
            'category' => $item->category ?? 'General',
            'excerpt' => $this->excerpt($item->content),
            'date' => $item->published_at?->format('M d, Y'),
            'read_time' => $this->readTime($item->content),
            'author' => $item->author?->name,
        ]);

        return view('dashboard::insights.index', [
            'featured' => $insights->first(),
            'insights' => $insights->slice(1)->values(),
            'categories' => $categories,
            'page' => $paginator->currentPage(),
            'totalPages' => $paginator->lastPage(),
            'from' => $paginator->firstItem() ?? 0,
            'to' => $paginator->lastItem() ?? 0,
            'total' => $paginator->total(),
        ]);
    }

    public function show(string $slug): View
    {
        $insight = MarketInsight::query()
            ->published()
            ->with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = MarketInsight::query()
            ->published()
            ->with('author')
            ->where('id', '!=', $insight->id)
            ->when($insight->category, fn ($q) => $q->where('category', $insight->category))
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (MarketInsight $item) => [
                'slug' => $item->slug,
                'title' => $item->title,
                'category' => $item->category ?? 'General',
                'date' => $item->published_at?->format('M d, Y'),
            ]);

        $related = $related->isNotEmpty() ? $related : MarketInsight::query()
            ->published()
            ->where('id', '!=', $insight->id)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (MarketInsight $item) => [
                'slug' => $item->slug,
                'title' => $item->title,
                'category' => $item->category ?? 'General',
                'date' => $item->published_at?->format('M d, Y'),
            ]);

        return view('dashboard::insights.show', [
            'insight' => $insight,
            'excerpt' => $this->excerpt($insight->content),
            'readTime' => $this->readTime($insight->content),
            'tickers' => $this->tickers($insight->content),
            'related' => $related,
        ]);
    }

    protected function excerpt(?string $html, int $limit = 160): string
    {
        $text = strip_tags(str_replace(
            ['</p>', '</li>', '</h2>', '</h3>', '</h1>', '<br>', '<br/>', '<br />'],
            ' ',
            (string) $html
        ));

        $text = trim(preg_replace('/\s+/', ' ', $text));

        return mb_strlen($text) <= $limit ? $text : mb_substr($text, 0, $limit).'…';
    }

    protected function readTime(?string $html): int
    {
        $words = str_word_count(strip_tags((string) $html));

        return max(1, (int) ceil($words / 200));
    }

    protected function tickers(?string $html): array
    {
        $text = strip_tags(str_replace(['</p>', '</li>', '</h2>', '</h3>', '<br'], ' ', (string) $html));

        preg_match_all('/(?<![A-Za-z0-9])[A-Z]{4}(?![A-Za-z0-9])/', $text, $matches);

        return array_values(array_unique($matches[0] ?? []));
    }
}
