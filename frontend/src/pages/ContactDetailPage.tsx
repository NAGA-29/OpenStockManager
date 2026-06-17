import { NavLink, useParams } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useContact } from '@/features/contacts/useContact';
import './inventory.css';
import './clients.css';

/**
 * 担当者詳細画面（旧 `contacts/detail.blade.php` を移植）。
 * 所属企業名込みの担当者情報を表示する読み取り画面。情報変更は CRM 側で行う前提。
 */
function ContactDetailPage() {
  const { id = '' } = useParams<{ id: string }>();
  const { data, isLoading, isError, refetch } = useContact(id);

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-address-book" aria-hidden="true" />
        担当者情報
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          担当者情報の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {data && (
        <>
          <div className="device-detail__actions">
            <NavLink to="/contacts" className="osm-btn">
              <i className="fas fa-arrow-left" aria-hidden="true" /> 一覧へ戻る
            </NavLink>
          </div>

          <Alert variant="info">
            ※ 担当者情報の変更は専門 CRM で行ってください。
          </Alert>

          <div className="device-card">
            <div className="device-card__header">担当者情報</div>
            <div className="device-card__body">
              <table className="device-info-table">
                <tbody>
                  <tr>
                    <th>所属企業名</th>
                    <td>
                      {data.company ? (
                        <NavLink
                          to={`/clients/${encodeURIComponent(data.client_id)}`}
                        >
                          {data.company}
                        </NavLink>
                      ) : (
                        ''
                      )}
                    </td>
                  </tr>
                  <tr>
                    <th>名前</th>
                    <td>{data.name ?? ''}</td>
                  </tr>
                  <tr>
                    <th>電話番号</th>
                    <td>{data.tel ?? ''}</td>
                  </tr>
                  <tr>
                    <th>Email</th>
                    <td>{data.email ?? ''}</td>
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
        </>
      )}
    </>
  );
}

export default ContactDetailPage;
