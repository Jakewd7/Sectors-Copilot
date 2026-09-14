<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminPageController extends Controller
{
    /**
     * Render the admin panel page shells.
     *
     * All data below is DUMMY / display-only — wiring these pages to the real
     * models, policies and mutations is handled by the backend developer.
     */
    public function users(): View
    {
        // TODO: replace with User::with('roles')->paginate() on the backend side
        $allUsers = [
            ['name' => 'Dimas Pratama', 'email' => 'dimas@mail.com', 'role' => 'User', 'status' => 'active', 'created_at' => 'Aug 12, 2026'],
            ['name' => 'Rina Wijaya', 'email' => 'rina@mail.com', 'role' => 'Admin', 'status' => 'active', 'created_at' => 'Jul 30, 2026'],
            ['name' => 'Budi Santoso', 'email' => 'budi@mail.com', 'role' => 'User', 'status' => 'inactive', 'created_at' => 'Jun 21, 2026'],
            ['name' => 'Sari Melati', 'email' => 'sari@mail.com', 'role' => 'Analyst', 'status' => 'active', 'created_at' => 'Jun 14, 2026'],
            ['name' => 'Kevin Hartono', 'email' => 'kevin@mail.com', 'role' => 'User', 'status' => 'active', 'created_at' => 'May 28, 2026'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@mail.com', 'role' => 'Analyst', 'status' => 'inactive', 'created_at' => 'May 09, 2026'],
            ['name' => 'Andre Wijaya', 'email' => 'andre@mail.com', 'role' => 'User', 'status' => 'active', 'created_at' => 'Apr 22, 2026'],
            ['name' => 'Maya Putri', 'email' => 'maya@mail.com', 'role' => 'User', 'status' => 'active', 'created_at' => 'Apr 02, 2026'],
        ];

        // Simple query-string pagination over the dummy set.
        // TODO: swap for ->paginate(5) from the backend.
        $perPage = 5;
        $page = max(1, (int) request()->query('page', 1));
        $total = count($allUsers);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $users = array_slice($allUsers, ($page - 1) * $perPage, $perPage);

        // Counts ALL accounts, including inactive ones (total registered users).
        $stats = [
            ['label' => 'Total users', 'value' => (string) $total], // TODO: connect to backend
        ];

        return view('admin::users.index', [
            'stats' => $stats,
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'from' => $total === 0 ? 0 : ($page - 1) * $perPage + 1,
            'to' => min($page * $perPage, $total),
            'total' => $total,
        ]);
    }

    public function marketInsights(): View
    {
        // TODO: replace with MarketInsight::latest()->get() on the backend side
        $insights = [
            ['id' => 'd1', 'category' => 'Weekly review', 'title' => 'Bank Q3 profits beat expectations', 'date' => '29 Aug 2026', 'status' => 'active'],
            ['id' => 'd2', 'category' => 'Stock watch', 'title' => 'Energy sector slips as commodity prices decline', 'date' => '28 Aug 2026', 'status' => 'inactive'],
            ['id' => 'd3', 'category' => 'Weekly review', 'title' => 'IHSG closes stronger, consumer stocks in demand', 'date' => '25 Aug 2026', 'status' => 'active'],
        ];

        // Article status mirrors published_at (C8): set = active (published),
        // null = inactive (draft). TODO: connect to backend.
        $activeCount = count(array_filter($insights, fn (array $insight) => $insight['status'] === 'active'));

        $stats = [
            ['label' => 'Active articles', 'value' => (string) $activeCount], // TODO: connect to backend
        ];

        return view('admin::market-insights.index', [
            'stats' => $stats,
            'insights' => $insights,
        ]);
    }

    public function promptStarters(): View
    {
        // TODO: replace with PromptStarter::orderBy('display_order')->get() on the backend side
        $prompts = [
            ['id' => 'p1', 'text' => 'Compare the valuation of the 3 biggest banks against the sector average'],
            ['id' => 'p2', 'text' => 'Find high-dividend stocks with cheap valuation'],
            ['id' => 'p3', 'text' => 'Analyze the fundamental health of a single company in depth'],
        ];

        return view('admin::prompt-starters.index', compact('prompts'));
    }

    public function caches(): View
    {
        $stats = [
            ['label' => 'Remaining API credits', 'value' => '688 / 1.000'], // TODO: connect to backend
            ['label' => 'Stored caches', 'value' => '1.204'],               // TODO: connect to backend
        ];

        // Dummy LLM token usage for the agent (TODO: pull real aggregates from the backend,
        // e.g. SUM(input_tokens)/SUM(output_tokens) grouped by model over a rolling window).
        $llmUsage = [
            'daily' => [
                'label' => 'Daily usage',
                'percent' => 8.3,
                'meta' => 'Resets in 5 hours.',
                'models' => [
                    ['name' => 'gemini-3.6-flash', 'share' => 4.6, 'requests' => 14, 'color' => '#a9ddbe'],
                    ['name' => 'glm-5.3-flash', 'share' => 2.4, 'requests' => 6, 'color' => '#7fb896'],
                    ['name' => 'deepseek-v4-flash', 'share' => 1.3, 'requests' => 3, 'color' => '#5f9c78'],
                ],
            ],
        ];

        // TODO: replace with ApiCache::orderBy('expires_at')->get() on the backend side
        $entries = [
            ['key' => 'companies/BBCA/overview', 'expires_at' => 'Today, 16:00'],
            ['key' => 'sectors/financials/companies', 'expires_at' => 'Today, 18:30'],
            ['key' => 'screener/consumer_goods', 'expires_at' => 'Tomorrow, 09:00'],
        ];

        return view('admin::caches.index', compact('stats', 'entries', 'llmUsage'));
    }
}
