import { NavLink, useParams } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import StatusLegend from '@/components/StatusLegend';
import {
  useDeviceCategory,
  type CategoryDevice,
} from '@/features/inventory/useDeviceCategory';
import './inventory.css';

/**
 * 個別管理（カテゴリ別 端末一覧）画面。
 * 旧 `devices/device_list.blade.php` を移植。カテゴリタブ・サマリー件数・
 * ステータスアイコン・端末詳細リンクを再現する。
 * 一括選択チェックボックス（カート）と検索フォームは後続フェーズ（3-10 / 検索）で対応。
 */
function InventoryUnitsPage() {
  const { code = '' } = useParams<{ code: string }>();
  const { data, isLoading, isError, refetch } = useDeviceCategory(code);

  const columns: Column<CategoryDevice>[] = [
    {
      key: 'status',
      header: 'ステータス',
      render: (row) => (
        <span className="device-status">
          {row.lending_now ? (
            <i
              className="fas fa-dove fa-lg"
              title="貸出中"
              aria-label="貸出中"
            />
          ) : null}
          {row.sale_id ? (
            <i
              className="fas fa-yen-sign"
              title="販売済"
              aria-label="販売済"
            />
          ) : null}
          {row.has_images ? (
            <i
              className="fas fa-images"
              title="画像あり"
              aria-label="画像あり"
            />
          ) : null}
          {row.defective ? (
            <span className="badge badge--danger">不具合</span>
          ) : null}
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
      key: 'note',
      header: 'ノート',
      className: 'device-note',
      render: (row) => row.note ?? '',
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-barcode" aria-hidden="true" />
        個別管理
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          端末一覧の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {data && (
        <>
          <ul className="device-tabs">
            {data.categories.map((cat) => (
              <li key={cat.code}>
                <NavLink
                  to={`/inventory/units/${cat.code}`}
                  className={({ isActive }) =>
                    isActive ? 'device-tab active' : 'device-tab'
                  }
                >
                  {cat.icon && <i className={`fa ${cat.icon}`} aria-hidden="true" />}{' '}
                  {cat.name}
                </NavLink>
              </li>
            ))}
          </ul>

          <div className="summary-card-title">{data.current.name}</div>
          <div className="summary-cards">
            <div className="summary-card summary-card--success">
              <span className="summary-card__label">貸出可能</span>
              <span className="summary-card__value">
                {data.counts.all - (data.counts.defective + data.counts.lending)}
              </span>
            </div>
            <div className="summary-card summary-card--primary">
              <span className="summary-card__label">貸出中</span>
              <span className="summary-card__value">{data.counts.lending}</span>
            </div>
            <div className="summary-card summary-card--danger">
              <span className="summary-card__label">不具合</span>
              <span className="summary-card__value">{data.counts.defective}</span>
            </div>
          </div>

          <StatusLegend />

          <DataTable
            columns={columns}
            rows={data.data}
            rowKey={(row) => row.device_id}
            empty="このカテゴリの端末はありません。"
          />
        </>
      )}
    </>
  );
}

export default InventoryUnitsPage;
