<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class InventoryUnitController extends Controller
{
    /**
     * 個別管理（シリアル番号・個体追跡）の一覧
     */
    public function index(): View
    {
        return view('inventory.units.index');
    }
}
