<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use App\Models\Payment;
use App\Models\Order;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        $dailyStats = [
            'sales_usd' => Order::where('status', 'paid')->where('currency', 'USD')->where('created_at', '>=', $today)->sum('total'),
            'sales_ves' => Order::where('status', 'paid')->where('currency', 'VES')->where('created_at', '>=', $today)->sum('total'),
            'tickets_sold_today' => Order::where('status', 'paid')->where('created_at', '>=', $today)->sum('ticket_count'),
            'pending_reviews' => Payment::where('status', 'pending')->count(),
        ];

        // Chart data: Sales last 7 days
        $salesHistory = Order::where('status', 'paid')
            ->select(DB::raw("DATE(created_at) as date"), DB::raw("SUM(total) as amount"))
            ->where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Active raffles progress
        $rafflesProgress = Raffle::active()
            ->select('id', 'title', 'total_tickets', 'sold_count')
            ->get()
            ->map(fn($r) => [
                'title' => $r->title,
                'progress' => $r->total_tickets > 0 ? round(($r->sold_count / $r->total_tickets) * 100, 2) : 0,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'dailyStats' => $dailyStats,
            'salesHistory' => $salesHistory,
            'rafflesProgress' => $rafflesProgress,
            'recentOrders' => Order::with(['user', 'raffle'])->latest()->limit(10)->get(),
        ]);
    }
}
