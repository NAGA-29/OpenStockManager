import { useState } from 'react';
import { useParams, NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useRentalDetail, useReturnDevice, type RentalDevice } from '@/features/rental/useRental';
import './rental.css';

function RentalHistoryDetailPage() {
  const { lendId = '' } = useParams<{ lendId: string }>();
  const [returnDeviceId, setReturnDeviceId] = useState('');
  const [returnMessage, setReturnMessage] = useState('');

  const { data, isLoading, isError, refetch } = useRentalDetail(lendId);
  const { mutate: returnDevice, isPending: isReturning } = useReturnDevice();

  const handleReturnDevice = (deviceId: string) => {
    if (!window.confirm('この端末を返却してもよろしいですか？')) {
      return;
    }

    returnDevice({
      lendId,
      device_id: deviceId,
      return_at: new Date().toISOString().split('T')[0],
    }, {
      onSuccess: () => {
        setReturnMessage('返却処理が完了しました。');
        refetch();
        setReturnDeviceId('');
        setTimeout(() => setReturnMessage(''), 3000);
      },
    });
  };

  if (isLoading) return <Loading />;

  if (isError) {
    return (
      <>
        <div className="page-bar">
          <i className="fa fa-history" aria-hidden="true" />
          レンタル履歴詳細
        </div>
        <Alert variant="danger">
          レンタル履歴の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      </>
    );
  }

  if (!data) return null;

  const deviceColumns: Column<RentalDevice>[] = [
    { key: 'device_id', header: '端末ID' },
    { key: 'device_type', header: '端末区分' },
    { key: 'device_name', header: '端末名' },
    { key: 'device_serial', header: 'シリアル' },
    {
      key: 'status',
      header: '状態',
      render: (row) => (
        row.pivot?.return_at ? (
          <span className="badge badge--success">返却済</span>
        ) : (
          <span className="badge badge--warning">貸出中</span>
        )
      ),
    },
    {
      key: 'action',
      header: '',
      render: (row) => (
        !row.pivot?.return_at && (
          <button
            type="button"
            onClick={() => handleReturnDevice(row.device_id)}
            disabled={isReturning || returnDeviceId === row.device_id}
            className="osm-btn osm-btn--small osm-btn--success"
          >
            {isReturning && returnDeviceId === row.device_id ? '処理中...' : '返却'}
          </button>
        )
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fa fa-history" aria-hidden="true" />
        レンタル履歴詳細
      </div>

      <div className="rental-detail__actions">
        <NavLink to="/rental/history" className="osm-btn">
          <i className="fas fa-arrow-left" aria-hidden="true" /> 一覧へ戻る
        </NavLink>
      </div>

      {returnMessage && (
        <Alert variant="success">{returnMessage}</Alert>
      )}

      <div className="rental-detail__grid">
        <div className="device-card">
          <div className="device-card__header">レンタル情報</div>
          <div className="device-card__body">
            <table className="device-info-table">
              <tbody>
                <tr>
                  <th>レンタルID</th>
                  <td>{data.lend_id}</td>
                </tr>
                <tr>
                  <th>貸出先企業</th>
                  <td>{data.clients?.company || '-'}</td>
                </tr>
                <tr>
                  <th>担当者</th>
                  <td>{data.contacts?.name || '-'}</td>
                </tr>
                <tr>
                  <th>貸出日</th>
                  <td>{data.checkout_at?.split(' ')[0] || '-'}</td>
                </tr>
                <tr>
                  <th>返却予定日</th>
                  <td>{data.schedule_return_at?.split(' ')[0] || '-'}</td>
                </tr>
                <tr>
                  <th>返却日</th>
                  <td>
                    {data.return_at ? (
                      <>
                        <i className="fas fa-check-circle text-success" />
                        {' '}
                        {data.return_at.split(' ')[0]}
                      </>
                    ) : (
                      '-'
                    )}
                  </td>
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
          <div className="device-card__header">貸出端末一覧</div>
          <div className="device-card__body">
            <DataTable
              columns={deviceColumns}
              rows={data.devices || []}
              rowKey={(row) => row.device_id}
              empty="貸出端末がありません。"
            />
          </div>
        </div>
      </div>
    </>
  );
}

export default RentalHistoryDetailPage;
