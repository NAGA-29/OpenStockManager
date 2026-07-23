import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import SearchBox from '@/components/ui/SearchBox';
import Pagination from '@/components/ui/Pagination';
import { useRentalHistory, type RentalHist } from '@/features/rental/useRental';
import './rental.css';

function RentalHistoryPage() {
  const [page, setPage] = useState(1);
  const [word, setWord] = useState('');
  const [searchTerm, setSearchTerm] = useState('');

  const { data, isLoading, isError, refetch } = useRentalHistory(page, word);

  const handleSearch = () => {
    setWord(searchTerm);
    setPage(1);
  };

  const columns: Column<RentalHist>[] = [
    { key: 'lend_id', header: 'レンタルID' },
    {
      key: 'company',
      header: '貸出先',
      render: (row) => row.clients?.company || '-',
    },
    {
      key: 'contact_name',
      header: '担当者',
      render: (row) => row.contacts?.name || '-',
    },
    {
      key: 'checkout_at',
      header: '貸出日',
      render: (row) => row.checkout_at?.split(' ')[0] || '-',
    },
    {
      key: 'all_returned',
      header: '返却',
      render: (row) => (
        row.all_returned ? (
          <i className="fas fa-check-circle text-success" aria-label="返却済" />
        ) : (
          <i className="fas fa-clock text-warning" aria-label="貸出中" />
        )
      ),
    },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <NavLink to={`/rental/history/${row.lend_id}`} className="osm-btn osm-btn--small">
          詳細
        </NavLink>
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fa fa-history" aria-hidden="true" />
        レンタル履歴
      </div>

      {isError && (
        <Alert variant="danger">
          レンタル履歴の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {isLoading && <Loading />}

      {data && (
        <>
          <SearchBox
            value={searchTerm}
            onChange={setSearchTerm}
            onSubmit={handleSearch}
          />

          {word && (
            <div className="search-summary">
              「{word}」の検索結果: {data.meta.total}件
            </div>
          )}

          <DataTable
            columns={columns}
            rows={data.data}
            rowKey={(row) => row.lend_id}
            empty="レンタル履歴がありません。"
          />

          <Pagination
            page={page}
            lastPage={data.meta.last_page}
            onChange={setPage}
          />
        </>
      )}
    </>
  );
}

export default RentalHistoryPage;
