<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class InventoryStockController extends Controller
{
    /**
     * 数量管理（ロケーション×品目の総数）の一覧
     */
    public function index(): View
    {
        return view('inventory.stocks.index');
    }
}
