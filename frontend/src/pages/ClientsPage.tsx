import { useState, type FormEvent } from 'react';
import { NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useClients, type Client } from '@/features/clients/useClients';
import './clients.css';

/**
 * 登録クライアント一覧画面（旧 `client/index.blade.php` を移植）。
 * 会社名での検索と詳細リンクに対応。CRM 同期ボタンは 3-9（外部連携）で対応。
 */
function ClientsPage() {
  const [input, setInput] = useState('');
  const [word, setWord] = useState('');
  const { data, isLoading, isError, refetch } = useClients(word);

  const handleSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setWord(input.trim());
  };

  const columns: Column<Client>[] = [
    {
      key: 'company',
      header: 'クライアント名',
      render: (row) => (
        <NavLink to={`/clients/${encodeURIComponent(row.client_id)}`}>
          {row.company ?? '(名称未設定)'}
        </NavLink>
      ),
    },
    { key: 'url', header: 'URL', render: (row) => row.url ?? '-' },
    {
      key: 'street_address',
      header: '住所',
      render: (row) => row.street_address ?? '-',
    },
    {
      key: 'note',
      header: 'ノート',
      className: 'device-note',
      render: (row) => row.note ?? '',
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-building" aria-hidden="true" />
        登録クライアント一覧
      </div>

      <div className="clients-toolbar">
        <form className="clients-search" onSubmit={handleSearch}>
          <input
            type="text"
            placeholder="クライアント名"
            value={input}
            onChange={(e) => setInput(e.target.value)}
          />
          <button type="submit" className="osm-btn">
            <i className="fas fa-search" aria-hidden="true" /> 検索
          </button>
        </form>
        <NavLink to="/clients/register" className="osm-btn">
          <i className="fas fa-plus" aria-hidden="true" /> 新規登録
        </NavLink>
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          クライアント一覧の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {data && (
        <DataTable
          columns={columns}
          rows={data}
          rowKey={(row) => row.client_id}
          empty="該当するクライアントがありません。"
        />
      )}
    </>
  );
}

export default ClientsPage;
