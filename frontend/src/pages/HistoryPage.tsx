import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import {
  useHistory,
  type HistoryFilter,
  type HistoryItem,
} from '@/features/history/useHistory';
import './rental.css';

const TYPE_TABS: Array<{ key: HistoryFilter; label: string }> = [
  { key: 'all', label: 'すべて' },
  { key: 'rental', label: 'レンタル' },
  { key: 'sale', label: '販売' },
];

function typeBadge(type: HistoryItem['type']) {
  return type === 'rental' ? (
    <span className="badge badge--warning">レンタル</span>
  ) : (
    <span className="badge badge--success">販売</span>
  );
}

function statusBadge(item: HistoryItem) {
  switch (item.status) {
    case 'returned':
      return <span className="badge badge--success">返却済</span>;
    case 'lending':
      return <span className="badge badge--warning">貸出中</span>;
    case 'sold':
    default:
      return <span className="badge badge--success">販売完了</span>;
  }
}

function detailPath(item: HistoryItem) {
  return item.type === 'rental'
    ? `/rental/history/${item.id}`
    : `/sale/history/${item.id}`;
}

function HistoryPage() {
  const [page, setPage] = useState(1);
  const [word, setWord] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [type, setType] = useState<HistoryFilter>('all');

  const { data, isLoading, isError, refetch } = useHistory(page, word, type);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setWord(searchTerm);
    setPage(1);
  };

  const handleTypeChange = (next: HistoryFilter) => {
    setType(next);
    setPage(1);
  };

  const columns: Column<HistoryItem>[] = [
    {
      key: 'type',
      header: '種別',
      render: (row) => typeBadge(row.type),
    },
    { key: 'id', header: 'ID' },
    {
      key: 'company',
      header: '取引先',
      render: (row) => row.company || '-',
    },
    {
      key: 'contact',
      header: '担当者',
      render: (row) => row.contact || '-',
    },
    {
      key: 'date',
      header: '日付',
      render: (row) => row.date || '-',
    },
    {
      key: 'status',
      header: '状態',
      render: (row) => statusBadge(row),
    },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <NavLink to={detailPath(row)} className="osm-btn osm-btn--small">
          詳細
        </NavLink>
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fa fa-history" aria-hidden="true" />
        全体履歴
      </div>

      {isError && (
        <Alert variant="danger">
          履歴の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      <div className="rental-container">
        <div className="rental-tabs">
          {TYPE_TABS.map((tab) => (
            <button
              key={tab.key}
              className={`rental-tab ${type === tab.key ? 'active' : ''}`}
              onClick={() => handleTypeChange(tab.key)}
            >
              {tab.label}
            </button>
          ))}
        </div>

        <div className="rental-content">
          <div className="search-section">
            <form onSubmit={handleSearch} className="search-form">
              <input
                type="text"
                placeholder="取引先 or ノートで検索"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="form-control"
              />
              <button type="submit" className="osm-btn osm-btn--primary">
                <i className="fas fa-search" /> 検索
              </button>
            </form>
          </div>

          {isLoading && <Loading />}

          {data && (
            <>
              {word && (
                <div className="search-summary">
                  「{word}」の検索結果: {data.meta.total}件
                </div>
              )}

              <DataTable
                columns={columns}
                rows={data.data}
                rowKey={(row) => `${row.type}-${row.id}`}
                empty="履歴がありません。"
              />

              {data.meta.last_page > 1 && (
                <div className="search-pagination">
                  <button
                    onClick={() => setPage(Math.max(1, page - 1))}
                    disabled={page === 1}
                    className="osm-btn osm-btn--small"
                  >
                    &lt; 前へ
                  </button>
                  <span className="search-pagination__info">
                    {page} / {data.meta.last_page}
                  </span>
                  <button
                    onClick={() => setPage(Math.min(data.meta.last_page, page + 1))}
                    disabled={page === data.meta.last_page}
                    className="osm-btn osm-btn--small"
                  >
                    次へ &gt;
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </>
  );
}

export default HistoryPage;
