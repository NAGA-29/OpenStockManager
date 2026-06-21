import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import SearchBox from '@/components/ui/SearchBox';
import Pagination from '@/components/ui/Pagination';
import { useSaleHistory, type SaleHist } from '@/features/sale/useSale';
import './sale.css';

function SaleHistoryPage() {
  const [page, setPage] = useState(1);
  const [word, setWord] = useState('');
  const [searchTerm, setSearchTerm] = useState('');

  const { data, isLoading, isError, refetch } = useSaleHistory(page, word);

  const handleSearch = () => {
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
            rowKey={(row) => row.sale_id}
            empty="販売履歴がありません。"
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

export default SaleHistoryPage;
