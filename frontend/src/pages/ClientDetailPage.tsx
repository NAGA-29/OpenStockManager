import { NavLink, useParams } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import {
  useClient,
  type ClientContact,
} from '@/features/clients/useClient';
import './inventory.css';
import './clients.css';

/**
 * クライアント詳細画面（旧 `client/client_detail.blade.php` を移植）。
 * 企業情報＋担当者一覧を表示する読み取り画面。情報変更は CRM 側で行う前提。
 */
function ClientDetailPage() {
  const { id = '' } = useParams<{ id: string }>();
  const { data, isLoading, isError, refetch } = useClient(id);

  const contactColumns: Column<ClientContact>[] = [
    { key: 'name', header: '名前', render: (row) => row.name ?? '-' },
    { key: 'tel', header: '電話番号', render: (row) => row.tel ?? '-' },
    { key: 'email', header: 'Email', render: (row) => row.email ?? '-' },
    { key: 'note', header: 'ノート', render: (row) => row.note ?? '' },
    {
      key: 'modified_at',
      header: '更新日',
      render: (row) => row.modified_at ?? '-',
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-building" aria-hidden="true" />
        クライアント詳細情報
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          クライアント情報の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {data && (
        <>
          <div className="device-detail__actions">
            <NavLink to="/clients" className="osm-btn">
              <i className="fas fa-arrow-left" aria-hidden="true" /> 一覧へ戻る
            </NavLink>
          </div>

          <Alert variant="info">
            ※ クライアント情報の変更は専門 CRM で行ってください。
          </Alert>

          <div className="device-card">
            <div className="device-card__header">企業情報</div>
            <div className="device-card__body">
              <table className="device-info-table">
                <tbody>
                  <tr>
                    <th>会社名</th>
                    <td>{data.company ?? ''}</td>
                  </tr>
                  <tr>
                    <th>URL</th>
                    <td>{data.url ?? ''}</td>
                  </tr>
                  <tr>
                    <th>電話番号</th>
                    <td>{data.tel ?? ''}</td>
                  </tr>
                  <tr>
                    <th>住所</th>
                    <td>
                      {data.post_code ? `〒${data.post_code} ` : ''}
                      {data.street_address ?? ''}
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

          <div className="device-card" style={{ marginTop: '1rem' }}>
            <div className="device-card__header">担当者一覧</div>
            <div className="device-card__body">
              <DataTable
                columns={contactColumns}
                rows={data.contacts}
                rowKey={(row) => row.id}
                empty="担当者が登録されていません。"
              />
            </div>
          </div>
        </>
      )}
    </>
  );
}

export default ClientDetailPage;
