<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // Thống kê cho component quản lý sản phẩm
        $templateCount = 0;
        $productCount = 0;

        if ($team) {
            $templateCount = ProductTemplate::byTeam($team->id)->count();
            $productCount = Product::byTeam($team->id)->count();
        }

        return view('dashboard', compact('templateCount', 'productCount'));
    }
}
