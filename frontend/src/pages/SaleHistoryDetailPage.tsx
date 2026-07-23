import { useParams, NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useSaleDetail, type SaleDevice } from '@/features/sale/useSale';
import './sale.css';

function SaleHistoryDetailPage() {
  const { saleId = '' } = useParams<{ saleId: string }>();

  const { data, isLoading, isError, refetch } = useSaleDetail(saleId);

  if (isLoading) return <Loading />;

  if (isError) {
    return (
      <>
        <div className="page-bar">
          <i className="fa fa-history" aria-hidden="true" />
          販売履歴詳細
        </div>
        <Alert variant="danger">
          販売履歴の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      </>
    );
  }

  if (!data) return null;

  const deviceColumns: Column<SaleDevice>[] = [
    { key: 'device_id', header: '端末ID' },
    { key: 'device_type', header: '端末区分' },
    { key: 'device_name', header: '端末名' },
    { key: 'device_serial', header: 'シリアル' },
    {
      key: 'status',
      header: '状態',
      render: () => (
        <span className="badge badge--success">販売完了</span>
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fa fa-history" aria-hidden="true" />
        販売履歴詳細
      </div>

      <div className="sale-detail__actions">
        <NavLink to="/sale/history" className="osm-btn">
          <i className="fas fa-arrow-left" aria-hidden="true" /> 一覧へ戻る
        </NavLink>
      </div>

      <div className="sale-detail__grid">
        <div className="device-card">
          <div className="device-card__header">販売情報</div>
          <div className="device-card__body">
            <table className="device-info-table">
              <tbody>
                <tr>
                  <th>販売ID</th>
                  <td>{data.sale_id}</td>
                </tr>
                <tr>
                  <th>販売先企業</th>
                  <td>{data.clients?.company || '-'}</td>
                </tr>
                <tr>
                  <th>担当者</th>
                  <td>{data.contacts?.name || '-'}</td>
                </tr>
                <tr>
                  <th>販売日</th>
                  <td>{data.sale_date_at?.split(' ')[0] || '-'}</td>
                </tr>
                <tr>
                  <th>ノート</th>
                  <td>{data.note || '-'}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div className="device-card">
          <div className="device-card__header">販売端末一覧</div>
          <div className="device-card__body">
            <DataTable
              columns={deviceColumns}
              rows={data.devices || []}
              rowKey={(row) => row.device_id}
              empty="販売端末がありません。"
            />
          </div>
        </div>
      </div>
    </>
  );
}

export default SaleHistoryDetailPage;
