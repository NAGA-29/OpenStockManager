import { NavLink, useParams } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import {
  useDevice,
  type DeviceCustomField,
  type DeviceRentalHist,
  type DeviceSaleHist,
} from '@/features/inventory/useDevice';
import './inventory.css';

/** カスタムフィールドの表示値を整形する（boolean はチェック/ダッシュ）。 */
function renderCustomValue(field: DeviceCustomField) {
  if (field.type === 'boolean') {
    return field.value ? (
      <i className="fas fa-check text-success" aria-label="該当" />
    ) : (
      '—'
    );
  }
  const display = field.display;
  return display === null || display === '' ? '' : String(display);
}

/**
 * 端末詳細（読み取り表示）画面。
 * 旧 `devices/show.blade.php` の表示部を移植。
 * 編集／貸出／販売／返却／バーコード印刷の操作は後続フェーズ（3-10 モーダル等）で対応する。
 */
function DeviceDetailPage() {
  const { id = '' } = useParams<{ id: string }>();
  const { data, isLoading, isError, refetch } = useDevice(id);

  const rentalColumns: Column<DeviceRentalHist>[] = [
    { key: 'lend_id', header: 'レンタルID' },
    { key: 'company', header: '貸出先', render: (row) => row.company ?? '-' },
    { key: 'checkout_at', header: '貸出日', render: (row) => row.checkout_at ?? '-' },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <NavLink to={`/rental/history/${row.lend_id}`}>詳細</NavLink>
      ),
    },
  ];

  const saleColumns: Column<DeviceSaleHist>[] = [
    { key: 'sale_id', header: 'セールID' },
    { key: 'company', header: '販売先', render: (row) => row.company ?? '-' },
    { key: 'sale_date_at', header: '販売日', render: (row) => row.sale_date_at ?? '-' },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <NavLink to={`/sale/history/${row.sale_id}`}>詳細</NavLink>
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-tablet-alt" aria-hidden="true" />
        端末詳細情報
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          端末情報の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {data && (
        <>
          <div className="device-detail__actions">
            {data.device_type && (
              <NavLink
                to={`/inventory/units/${data.device_type}`}
                className="osm-btn"
              >
                <i className="fas fa-arrow-left" aria-hidden="true" /> 一覧へ戻る
              </NavLink>
            )}
          </div>

          <div className="device-detail__grid">
            {/* 左: 端末情報 */}
            <div className="device-card">
              <div className="device-card__header">端末情報</div>
              <div className="device-card__body">
                <div className="device-detail__images">
                  {data.images.length > 0 ? (
                    data.images.map((img) => (
                      <img
                        key={img.path}
                        src={img.path}
                        alt={img.filename ?? data.device_id}
                      />
                    ))
                  ) : (
                    <div className="device-detail__noimage">No Image</div>
                  )}
                </div>

                {data.sale_id ? (
                  <span className="device-badge device-badge--danger">販売済</span>
                ) : data.lending_now ? (
                  <span className="device-badge device-badge--success">貸出中</span>
                ) : null}

                <table className="device-info-table">
                  <tbody>
                    <tr>
                      <th>端末ID</th>
                      <td>{data.device_id}</td>
                    </tr>
                    <tr>
                      <th>端末区分</th>
                      <td>{data.device_type}</td>
                    </tr>
                    <tr>
                      <th>端末名</th>
                      <td>{data.device_name ?? ''}</td>
                    </tr>
                    <tr>
                      <th>端末シリアル</th>
                      <td>{data.device_serial ?? ''}</td>
                    </tr>
                    {data.custom_fields.map((field) => (
                      <tr key={field.key}>
                        <th>{field.label}</th>
                        <td>{renderCustomValue(field)}</td>
                      </tr>
                    ))}
                    <tr>
                      <th>初稼働日</th>
                      <td>{data.first_work_date_at ?? ''}</td>
                    </tr>
                    <tr>
                      <th>購入日</th>
                      <td>{data.purchase_date_at ?? ''}</td>
                    </tr>
                    <tr>
                      <th>オプション</th>
                      <td>{data.option ?? ''}</td>
                    </tr>
                    <tr>
                      <th>使用ID</th>
                      <td>{data.using_user_id ?? ''}</td>
                    </tr>
                    <tr>
                      <th>コンディション</th>
                      <td>{data.condition ?? ''}</td>
                    </tr>
                    <tr className={data.defective ? 'is-danger' : undefined}>
                      <th>不具合</th>
                      <td>
                        {data.defective ? (
                          <i
                            className="fas fa-check-circle text-danger"
                            aria-label="該当"
                          />
                        ) : (
                          ''
                        )}
                      </td>
                    </tr>
                    <tr className={data.not_for_sale ? 'is-danger' : undefined}>
                      <th>販売不可</th>
                      <td>
                        {data.not_for_sale ? (
                          <i
                            className="fas fa-check-circle text-danger"
                            aria-label="該当"
                          />
                        ) : (
                          ''
                        )}
                      </td>
                    </tr>
                    <tr>
                      <th>貸出中</th>
                      <td>
                        {data.lending_now ? (
                          <i
                            className="fas fa-check-circle text-success"
                            aria-label="該当"
                          />
                        ) : (
                          ''
                        )}
                      </td>
                    </tr>
                    <tr>
                      <th>ノート</th>
                      <td>{data.note ?? ''}</td>
                    </tr>
                    <tr>
                      <th>更新日</th>
                      <td>{data.modified_at ?? ''}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            {/* 右: 履歴 */}
            <div>
              <div className="device-card">
                <div className="device-card__header">貸出履歴</div>
                <div className="device-card__body">
                  <DataTable
                    columns={rentalColumns}
                    rows={data.rental_hists}
                    rowKey={(row) => row.lend_id}
                    empty="貸出履歴はありません。"
                  />
                </div>
              </div>

              <div className="device-card" style={{ marginTop: '1rem' }}>
                <div className="device-card__header">販売履歴</div>
                <div className="device-card__body">
                  <DataTable
                    columns={saleColumns}
                    rows={data.sale_hists}
                    rowKey={(row) => row.sale_id}
                    empty="販売履歴はありません。"
                  />
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </>
  );
}

export default DeviceDetailPage;
