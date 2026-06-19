<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RentalHist;
use App\Models\SaleHist;
use App\Traits\Keyword;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class HistoryController extends Controller
{
    use Keyword;

    private const PER_PAGE = 10;

    /**
     * レンタル・販売を統合した履歴一覧。
     *
     * クエリ:
     *  - `type` : all|rental|sale（既定 all）
     *  - `word` : キーワード（取引先 / ノートの AND 部分一致）
     *  - `page` : ページ番号
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type', 'all');
        $word = $request->filled('word') ? $request->query('word') : null;

        $items = collect();

        if ($type === 'all' || $type === 'rental') {
            $items = $items->concat($this->rentalItems($word));
        }
        if ($type === 'all' || $type === 'sale') {
            $items = $items->concat($this->saleItems($word));
        }

        // 日付の新しい順。日付が同じ場合は販売を後ろにして安定させる。
        $sorted = $items
            ->sortByDesc(fn ($item) => $item['date'] ?? '')
            ->values();

        $page = max(1, (int) $request->query('page', 1));
        $total = $sorted->count();
        $lastPage = (int) max(1, ceil($total / self::PER_PAGE));
        $paged = $sorted->forPage($page, self::PER_PAGE)->values();

        return response()->json([
            'data' => $paged,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => self::PER_PAGE,
                'total' => $total,
            ],
        ]);
    }

    /**
     * レンタル履歴を共通フォーマットへ正規化。
     */
    private function rentalItems(?string $word): Collection
    {
        $query = RentalHist::with(['clients', 'contacts']);
        $this->applyKeyword($query, $word);

        return $query->get()->map(fn (RentalHist $rental) => [
            'id' => $rental->lend_id,
            'type' => 'rental',
            'company' => $rental->clients->company ?? null,
            'contact' => $rental->contacts->name ?? null,
            'date' => optional($rental->checkout_at)->format('Y-m-d'),
            'status' => $rental->all_returned ? 'returned' : 'lending',
            'note' => $rental->note,
        ]);
    }

    /**
     * 販売履歴を共通フォーマットへ正規化。
     */
    private function saleItems(?string $word): Collection
    {
        $query = SaleHist::with(['clients', 'contacts']);
        $this->applyKeyword($query, $word);

        return $query->get()->map(fn (SaleHist $sale) => [
            'id' => $sale->sale_id,
            'type' => 'sale',
            'company' => $sale->clients->company ?? null,
            'contact' => $sale->contacts->name ?? null,
            'date' => optional($sale->sale_date_at)->format('Y-m-d'),
            'status' => 'sold',
            'note' => $sale->note,
        ]);
    }

    /**
     * ノート・取引先企業名への AND 部分一致検索を適用。
     *
     * @param \Illuminate\Database\Eloquent\Builder<*> $query
     */
    private function applyKeyword($query, ?string $word): void
    {
        if (!$word) {
            return;
        }

        foreach ($this->extractKeywords($word) as $keyword) {
            $keyword = '%' . addcslashes($keyword, '%_\\') . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('note', 'like', $keyword)
                    ->orWhereHas('clients', fn ($c) => $c->where('company', 'like', $keyword));
            });
        }
    }
}
