import { useState, type FormEvent } from 'react';
import { NavLink } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useContacts, type Contact } from '@/features/contacts/useContacts';
import './clients.css';

/**
 * 担当者一覧画面（旧 `contacts/lists.blade.php` を移植）。
 * 担当者名での検索と詳細リンクに対応。担当者情報の変更は CRM 側で行う前提。
 */
function ContactsPage() {
  const [input, setInput] = useState('');
  const [word, setWord] = useState('');
  const { data, isLoading, isError, refetch } = useContacts(word);

  const handleSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setWord(input.trim());
  };

  const columns: Column<Contact>[] = [
    {
      key: 'name',
      header: '名前',
      render: (row) => (
        <NavLink to={`/contacts/${encodeURIComponent(String(row.id))}`}>
          {row.name ?? '(名称未設定)'}
        </NavLink>
      ),
    },
    { key: 'company', header: '所属', render: (row) => row.company ?? '-' },
    { key: 'tel', header: 'TEL', render: (row) => row.tel ?? '-' },
    { key: 'email', header: 'Mail', render: (row) => row.email ?? '-' },
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
        <i className="fas fa-address-book" aria-hidden="true" />
        担当者一覧
      </div>

      <div className="clients-toolbar">
        <form className="clients-search" onSubmit={handleSearch}>
          <input
            type="text"
            placeholder="担当者名"
            value={input}
            onChange={(e) => setInput(e.target.value)}
          />
          <button type="submit" className="osm-btn">
            <i className="fas fa-search" aria-hidden="true" /> 検索
          </button>
        </form>
        <NavLink to="/contacts/register" className="osm-btn">
          <i className="fas fa-plus" aria-hidden="true" /> 新規登録
        </NavLink>
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          担当者一覧の取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {data && (
        <DataTable
          columns={columns}
          rows={data}
          rowKey={(row) => row.id}
          empty="該当する担当者がありません。"
        />
      )}
    </>
  );
}

export default ContactsPage;
