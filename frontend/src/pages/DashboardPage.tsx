import { Link } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import {
  useDashboard,
  type DashboardRental,
} from '@/features/dashboard/useDashboard';
import './dashboard.css';

type RemainingKind = 'overdue' | 'near';

function rentalColumns(kind: RemainingKind): Column<DashboardRental>[] {
  return [
    {
      key: 'lend_id',
      header: 'レンタルID',
      render: (row) => (
        <Link to={`/rental/history/${row.lend_id}`}>{row.lend_id}</Link>
      ),
    },
    { key: 'company', header: 'クライアント', render: (row) => row.company ?? '-' },
    {
      key: 'device_count',
      header: 'デバイス',
      render: (row) => `${row.device_count}台`,
    },
    {
      key: 'schedule_return_at',
      header: '返却予定日',
      render: (row) => row.schedule_return_at ?? '-',
    },
    {
      key: 'remaining',
      header: kind === 'overdue' ? '超過日数' : '残日数',
      render: (row) =>
        kind === 'overdue' ? (
          <span className="text-danger">{row.overdue_days}日超過</span>
        ) : row.remaining_days === 0 ? (
          <span className="text-danger">本日</span>
        ) : (
          <span className="text-warning">あと{row.remaining_days}日</span>
        ),
    },
  ];
}

/**
 * ダッシュボード（旧 `dashboard/index.blade.php` を移植）。
 * `GET /api/dashboard` の集計（貸出中台数・延滞・期限間近）を表示する。
 */
function DashboardPage() {
  const { data, isLoading, isError, refetch } = useDashboard();

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-home" aria-hidden="true" />
        ダッシュボード
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          データの取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
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
              <DataTable
                columns={rentalColumns('overdue')}
                rows={data.overdue}
                rowKey={(row) => row.lend_id}
                empty="延滞中のレンタルはありません。"
              />
            </div>

            <div className="dashboard-card">
              <div className="dashboard-card__header dashboard-card__header--warning">
                <i className="fas fa-clock" aria-hidden="true" />
                貸出し期限間近（3日以内）
              </div>
              <DataTable
                columns={rentalColumns('near')}
                rows={data.near_deadline}
                rowKey={(row) => row.lend_id}
                empty="期限間近のレンタルはありません。"
              />
            </div>
          </div>
        </>
      )}
    </>
  );
}

export default DashboardPage;
