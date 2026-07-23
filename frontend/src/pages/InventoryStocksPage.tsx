import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import {
  useStocks,
  type InventoryStock,
} from '@/features/inventory/useStocks';

/**
 * 数量管理（在庫一覧）画面。
 * 旧 `inventory/stocks/index.blade.php` は開発中プレースホルダだったが、
 * `GET /api/inventory/stocks` が実データを返すため実テーブルとして実装する。
 */
function InventoryStocksPage() {
  const { data, isLoading, isError, refetch } = useStocks();

  const columns: Column<InventoryStock>[] = [
    { key: 'location', header: 'ロケーション' },
    { key: 'item_name', header: '品目', render: (row) => row.item_name ?? '-' },
    {
      key: 'quantity',
      header: '在庫数',
      render: (row) => (
        <span className={row.below_min ? 'text-danger' : undefined}>
          {row.quantity}
        </span>
      ),
    },
    { key: 'min_stock', header: '最低在庫' },
    {
      key: 'below_min',
      header: '状態',
      render: (row) =>
        row.below_min ? (
          <span className="text-danger">最低在庫を下回る</span>
        ) : (
          <span className="text-success">適正</span>
        ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-boxes" aria-hidden="true" />
        数量管理
      </div>

      <Alert variant="info">
        <strong>数量管理</strong>
        とは、ロケーション × 品目ごとに在庫数をまとめて管理する方式です。個体を特定せず、入庫・出庫・調整によって数量を増減させます。
      </Alert>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          在庫データの取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {data && (
        <DataTable
          columns={columns}
          rows={data}
          rowKey={(row) => row.id}
          empty="在庫データがありません。"
        />
      )}
    </>
  );
}

export default InventoryStocksPage;
