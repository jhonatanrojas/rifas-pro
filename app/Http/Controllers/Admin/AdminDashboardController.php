<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
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
            'tickets_sold_today' => DB::table('order_tickets')
                ->join('orders', 'orders.id', '=', 'order_tickets.order_id')
                ->where('orders.status', 'paid')
                ->where('orders.created_at', '>=', $today)
                ->count(),
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

        $pendingPayments = Payment::with(['user', 'order.raffle'])
            ->where('status', 'pending')
            ->latest()
            ->limit(8)
            ->get();

        $topAffiliates = Affiliate::with('user')
            ->orderByDesc('total_earned')
            ->limit(5)
            ->get();

        $salesByMethod = Payment::where('status', 'approved')
            ->select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'dailyStats' => $dailyStats,
            'salesHistory' => $salesHistory,
            'rafflesProgress' => $rafflesProgress,
            'recentOrders' => Order::with(['user', 'raffle'])->latest()->limit(10)->get(),
            'pendingPayments' => $pendingPayments,
            'topAffiliates' => $topAffiliates,
            'salesByMethod' => $salesByMethod,
        ]);
    }
}
