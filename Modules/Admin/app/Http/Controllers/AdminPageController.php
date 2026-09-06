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
        $stats = [
            ['label' => 'Total users', 'value' => '148'],           // TODO: connect to backend
            ['label' => 'Remaining API credits', 'value' => '688'], // TODO: connect to backend
            ['label' => 'Stored caches', 'value' => '1.204'],       // TODO: connect to backend
        ];

        // TODO: replace with User::with('roles')->get() on the backend side
        $users = [
            ['name' => 'Dimas Pratama', 'email' => 'dimas@mail.com', 'role' => 'User', 'status' => 'active'],
            ['name' => 'Rina Wijaya', 'email' => 'rina@mail.com', 'role' => 'Admin', 'status' => 'active'],
            ['name' => 'Budi Santoso', 'email' => 'budi@mail.com', 'role' => 'User', 'status' => 'suspended'],
        ];

        return view('admin::users.index', compact('stats', 'users'));
    }

    public function marketInsights(): View
    {
        // TODO: replace with MarketInsight::latest()->get() on the backend side
        $insights = [
            ['id' => 'd1', 'category' => 'Weekly review', 'title' => 'Bank Q3 profits beat expectations', 'date' => '29 Aug 2026'],
            ['id' => 'd2', 'category' => 'Stock watch', 'title' => 'Energy sector slips as commodity prices decline', 'date' => '28 Aug 2026'],
            ['id' => 'd3', 'category' => 'Weekly review', 'title' => 'IHSG closes stronger, consumer stocks in demand', 'date' => '25 Aug 2026'],
        ];

        return view('admin::market-insights.index', compact('insights'));
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
        ];

        // TODO: replace with ApiCache::orderBy('expires_at')->get() on the backend side
        $entries = [
            ['key' => 'companies/BBCA/overview', 'expires_at' => 'Today, 16:00'],
            ['key' => 'sectors/financials/companies', 'expires_at' => 'Today, 18:30'],
            ['key' => 'screener/consumer_goods', 'expires_at' => 'Tomorrow, 09:00'],
        ];

        return view('admin::caches.index', compact('stats', 'entries'));
    }
}
