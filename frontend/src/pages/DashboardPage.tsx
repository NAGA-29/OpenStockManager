import { Link } from 'react-router-dom';
import {
  useDashboard,
  type DashboardRental,
} from '@/features/dashboard/useDashboard';
import './dashboard.css';

type RemainingKind = 'overdue' | 'near';

/** 延滞／期限間近のレンタル一覧テーブル（旧 dashboard の 2 カラムを共通化）。 */
function RentalTable({
  rentals,
  kind,
}: {
  rentals: DashboardRental[];
  kind: RemainingKind;
}) {
  if (rentals.length === 0) {
    return (
      <p className="dashboard-empty">
        {kind === 'overdue'
          ? '延滞中のレンタルはありません。'
          : '期限間近のレンタルはありません。'}
      </p>
    );
  }

  return (
    <table className="dashboard-table">
      <thead>
        <tr>
          <th>レンタルID</th>
          <th>クライアント</th>
          <th>デバイス</th>
          <th>返却予定日</th>
          <th>{kind === 'overdue' ? '超過日数' : '残日数'}</th>
        </tr>
      </thead>
      <tbody>
        {rentals.map((rental) => (
          <tr key={rental.lend_id}>
            <td>
              <Link to={`/rental/history/${rental.lend_id}`}>
                {rental.lend_id}
              </Link>
            </td>
            <td>{rental.company ?? '-'}</td>
            <td>{rental.device_count}台</td>
            <td>{rental.schedule_return_at ?? '-'}</td>
            <td>
              {kind === 'overdue' ? (
                <span className="text-danger">{rental.overdue_days}日超過</span>
              ) : rental.remaining_days === 0 ? (
                <span className="text-danger">本日</span>
              ) : (
                <span className="text-warning">
                  あと{rental.remaining_days}日
                </span>
              )}
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

/**
 * ダッシュボード（旧 `dashboard/index.blade.php` を移植）。
 * `GET /api/dashboard` の集計（貸出中台数・延滞・期限間近）を表示する。
 */
function DashboardPage() {
  const { data, isLoading, isError, refetch } = useDashboard();

  return (
    <>
      <div className="dashboard-bar">
        <i className="fas fa-home" aria-hidden="true" />
        ダッシュボード
      </div>

      {isLoading && <p className="dashboard-state">読み込み中…</p>}

      {isError && (
        <p className="dashboard-state dashboard-state--error">
          データの取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </p>
      )}

      {data && (
        <>
          <div className="summary-cards">
            <div className="summary-card summary-card--primary">
              <span className="summary-card__label">貸出中台数</span>
              <span className="summary-card__value">{data.lending_count}</span>
            </div>
            <div className="summary-card summary-card--danger">
              <span className="summary-card__label">延滞中</span>
              <span className="summary-card__value">{data.overdue.length}</span>
            </div>
            <div className="summary-card summary-card--success">
              <span className="summary-card__label">期限間近（3日以内）</span>
              <span className="summary-card__value">
                {data.near_deadline.length}
              </span>
            </div>
          </div>

          <div className="dashboard-grid">
            <div className="dashboard-card">
              <div className="dashboard-card__header dashboard-card__header--danger">
                <i className="fas fa-exclamation-triangle" aria-hidden="true" />
                返却期限超過（延滞）
              </div>
              <RentalTable rentals={data.overdue} kind="overdue" />
            </div>

            <div className="dashboard-card">
              <div className="dashboard-card__header dashboard-card__header--warning">
                <i className="fas fa-clock" aria-hidden="true" />
                貸出し期限間近（3日以内）
              </div>
              <RentalTable rentals={data.near_deadline} kind="near" />
            </div>
          </div>
        </>
      )}
    </>
  );
}

export default DashboardPage;
