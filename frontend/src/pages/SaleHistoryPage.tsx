import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useSaleHistory, type SaleHist } from '@/features/sale/useSale';
import './sale.css';

function SaleHistoryPage() {
  const [page, setPage] = useState(1);
  const [word, setWord] = useState('');
  const [searchTerm, setSearchTerm] = useState('');

  const { data, isLoading, isError, refetch } = useSaleHistory(page, word);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setWord(searchTerm);
    setPage(1);
  };

  const columns: Column<SaleHist>[] = [
    { key: 'sale_id', header: '販売ID' },
    {
      key: 'company',
      header: '販売先',
      render: (row) => row.clients?.company || '-',
    },
    {
      key: 'contact_name',
      header: '担当者',
      render: (row) => row.contacts?.name || '-',
    },
    {
      key: 'sale_date_at',
      header: '販売日',
      render: (row) => row.sale_date_at?.split(' ')[0] || '-',
    },
    {
      key: 'status',
      header: '状態',
      render: () => (
        <span className="badge badge--success">販売完了</span>
      ),
    },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <NavLink to={`/sale/history/${row.sale_id}`} className="osm-btn osm-btn--small">
          詳細
        </NavLink>
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fa fa-history" aria-hidden="true" />
        販売履歴
      </div>

      {isError && (
        <Alert variant="danger">
          販売履歴の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {isLoading && <Loading />}

      {data && (
        <>
          <div className="search-section">
            <form onSubmit={handleSearch} className="search-form">
              <input
                type="text"
                placeholder="検索キーワード"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="form-control"
              />
              <button type="submit" className="osm-btn osm-btn--primary">
                <i className="fas fa-search" /> 検索
              </button>
            </form>
          </div>

          {word && (
            <div className="search-summary">
              「{word}」の検索結果: {data.meta.total}件
            </div>
          )}

          <DataTable
            columns={columns}
            rows={data.data}
            rowKey={(row) => row.sale_id}
            empty="販売履歴がありません。"
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
    </>
  );
}

export default SaleHistoryPage;
