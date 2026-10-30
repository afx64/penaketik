<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccessGrant;
use App\Models\Chapter;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $bookIds = $user->books()->pluck('id');

        $stats = [
            'books' => $bookIds->count(),
            'chapters' => Chapter::whereIn('book_id', $bookIds)->count(),
            'grants' => AccessGrant::whereIn('book_id', $bookIds)->count(),
            'pending_orders' => Order::whereIn('book_id', $bookIds)->where('status', 'pending')->count(),
            'revenue' => Order::whereIn('book_id', $bookIds)->where('status', 'paid')->sum('amount'),
            'buyers' => Order::whereIn('book_id', $bookIds)->where('status', 'paid')->distinct()->count('user_id'),
        ];

        return view('dashboard.index', [
            'stats' => $stats,
            'books' => $user->books()->withCount(['chapters', 'grants'])->latest('id')->take(5)->get(),
            'recentOrders' => Order::whereIn('book_id', $bookIds)
                ->with('book', 'user')
                ->latest('id')
                ->take(5)
                ->get(),
        ]);
    }
}
