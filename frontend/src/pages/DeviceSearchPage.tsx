import { useState, type FormEvent } from 'react';
import { NavLink, useSearchParams } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import StatusLegend from '@/components/StatusLegend';
import { useDeviceSearch } from '@/features/inventory/useDeviceSearch';
import type { CategoryDevice } from '@/features/inventory/useDeviceCategory';
import './inventory.css';
import './clients.css';

/**
 * 端末検索画面（旧 `devices/search_results.blade.php` を移植）。
 * device_id / device_serial / note のキーワード AND 部分一致。10 件ページネーション。
 * 検索条件は URL クエリ（`word`/`page`）に保持し、リロード・共有でも再現する。
 */
function DeviceSearchPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const word = searchParams.get('word') ?? '';
  const page = Number(searchParams.get('page') ?? '1');

  const [input, setInput] = useState(word);
  const { data, isLoading, isError, refetch } = useDeviceSearch(word, '', page);

  const handleSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const trimmed = input.trim();
    if (trimmed === '') return;
    setSearchParams({ word: trimmed, page: '1' });
  };

  const goToPage = (target: number) => {
    setSearchParams({ word, page: String(target) });
  };

  const columns: Column<CategoryDevice>[] = [
    {
      key: 'status',
      header: 'ステータス',
      render: (row) => (
        <span className="device-status">
          {row.lending_now ? (
            <i className="fas fa-dove fa-lg" title="貸出中" aria-label="貸出中" />
          ) : null}
          {row.sale_id ? (
            <i className="fas fa-yen-sign" title="販売済" aria-label="販売済" />
          ) : null}
          {row.has_images ? (
            <i className="fas fa-images" title="画像あり" aria-label="画像あり" />
          ) : null}
          {row.defective ? <span className="badge badge--danger">不具合</span> : null}
          {row.not_for_sale ? (
            <span className="badge badge--danger">販売不可</span>
          ) : null}
        </span>
      ),
    },
    {
      key: 'device_id',
      header: '端末ID',
      render: (row) => (
        <NavLink to={`/devices/${encodeURIComponent(row.device_id)}`}>
          {row.device_id}
        </NavLink>
      ),
    },
    { key: 'device_name', header: '端末名', render: (row) => row.device_name ?? '-' },
    {
      key: 'device_serial',
      header: 'シリアル',
      render: (row) => row.device_serial ?? '-',
    },
    { key: 'condition', header: 'コンディション', render: (row) => row.condition ?? '-' },
    {
      key: 'note',
      header: 'ノート',
      className: 'device-note',
      render: (row) => row.note ?? '',
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-search" aria-hidden="true" />
        端末検索
      </div>

      <form className="clients-search" onSubmit={handleSearch}>
        <input
          type="text"
          placeholder="端末ID / シリアル番号 / ノート"
          value={input}
          onChange={(e) => setInput(e.target.value)}
        />
        <button type="submit" className="osm-btn">
          <i className="fas fa-search" aria-hidden="true" /> 検索
        </button>
      </form>

      {word === '' && (
        <Alert variant="info">
          キーワードを入力して端末を検索してください。
        </Alert>
      )}

      {word !== '' && (
        <>
          <StatusLegend />

          {isLoading && <Loading />}

          {isError && (
            <Alert variant="danger">
              検索に失敗しました。{' '}
              <button type="button" onClick={() => void refetch()}>
                再試行
              </button>
            </Alert>
          )}

          {data && (
            <>
              <p className="search-summary">
                「{data.meta.keywords}」の検索結果: {data.meta.total} 件
              </p>

              <DataTable
                columns={columns}
                rows={data.data}
                rowKey={(row) => row.device_id}
                empty="該当する端末がありません。"
              />

              {data.meta.last_page > 1 && (
                <div className="search-pagination">
                  <button
                    type="button"
                    className="osm-btn"
                    disabled={data.meta.current_page <= 1}
                    onClick={() => goToPage(data.meta.current_page - 1)}
                  >
                    前へ
                  </button>
                  <span className="search-pagination__info">
                    {data.meta.current_page} / {data.meta.last_page}
                  </span>
                  <button
                    type="button"
                    className="osm-btn"
                    disabled={data.meta.current_page >= data.meta.last_page}
                    onClick={() => goToPage(data.meta.current_page + 1)}
                  >
                    次へ
                  </button>
                </div>
              )}
            </>
          )}
        </>
      )}
    </>
  );
}

export default DeviceSearchPage;
