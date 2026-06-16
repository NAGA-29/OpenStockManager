<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use Illuminate\Http\JsonResponse;

class InventoryStockController extends Controller
{
    /**
     * 数量管理（ロケーション×品目の総数）の一覧を JSON で返す。
     */
    public function index(): JsonResponse
    {
        $stocks = InventoryStock::with('item')
            ->orderBy('location')
            ->get()
            ->map(fn (InventoryStock $stock) => [
                'id'             => $stock->id,
                'location'       => $stock->location,
                'item_name'      => $stock->item->name ?? null,
                'quantity'       => $stock->quantity,
                'min_stock'      => $stock->min_stock,
                'below_min'      => $stock->isBelowMinStock(),
            ]);

        return response()->json(['data' => $stocks]);
    }
}
