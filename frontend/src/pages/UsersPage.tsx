import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import FormModal from '@/components/ui/FormModal';
import DataTable, { type Column } from '@/components/ui/DataTable';
import SearchBox from '@/components/ui/SearchBox';
import Pagination from '@/components/ui/Pagination';
import { useToast } from '@/components/ui/toast/useToast';
import {
  useUsers,
  useUpdateUser,
  type ManagedUser,
  type UserRole,
} from '@/features/users/useUsers';
import './rental.css';
import './register.css';

interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

interface EditFields {
  name: string;
  email: string;
  role: UserRole;
  password: string;
  password_confirmation: string;
}

function UsersPage() {
  const [page, setPage] = useState(1);
  const [word, setWord] = useState('');
  const [searchTerm, setSearchTerm] = useState('');

  const { data, isLoading, isError, refetch } = useUsers(page, word);
  const updateMutation = useUpdateUser();
  const { show } = useToast();

  const [editing, setEditing] = useState<ManagedUser | null>(null);
  const [fields, setFields] = useState<EditFields>({
    name: '',
    email: '',
    role: 'user',
    password: '',
    password_confirmation: '',
  });
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  const handleSearch = () => {
    setWord(searchTerm);
    setPage(1);
  };

  const openEdit = (user: ManagedUser) => {
    setEditing(user);
    setFields({
      name: user.name,
      email: user.email,
      role: user.role,
      password: '',
      password_confirmation: '',
    });
    setFieldErrors({});
  };

  const closeEdit = () => {
    setEditing(null);
    setFieldErrors({});
  };

  const setField = (key: keyof EditFields, value: string) =>
    setFields((prev) => ({ ...prev, [key]: value }));

  const handleUpdate = async () => {
    if (!editing) return;
    setFieldErrors({});

    try {
      await updateMutation.mutateAsync({
        id: editing.id,
        name: fields.name,
        email: fields.email,
        role: fields.role,
        password: fields.password || undefined,
        password_confirmation: fields.password_confirmation || undefined,
      });
      show(`ユーザー「${fields.name}」を更新しました。`, 'success');
      closeEdit();
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      if (axiosErr.response?.status === 422 && axiosErr.response.data?.errors) {
        setFieldErrors(axiosErr.response.data.errors);
      } else {
        show('ユーザーの更新に失敗しました。', 'danger');
      }
    }
  };

  const errorFor = (key: string) => fieldErrors[key];

  const columns: Column<ManagedUser>[] = [
    { key: 'name', header: '名前' },
    { key: 'email', header: 'メールアドレス' },
    {
      key: 'role',
      header: '権限',
      render: (row) =>
        row.is_admin ? (
          <span className="badge badge--warning">管理者</span>
        ) : (
          <span className="badge badge--success">一般</span>
        ),
    },
    {
      key: 'created_at',
      header: '登録日',
      render: (row) => row.created_at || '-',
    },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <button
          type="button"
          className="osm-btn osm-btn--small"
          onClick={() => openEdit(row)}
        >
          編集
        </button>
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-users-cog" aria-hidden="true" />
        ユーザー管理
      </div>

      <div className="rental-detail__actions" style={{ marginBottom: '1rem' }}>
        <NavLink to="/users/register" className="osm-btn osm-btn--primary">
          <i className="fas fa-user-plus" aria-hidden="true" /> 新規登録
        </NavLink>
      </div>

      {isError && (
        <Alert variant="danger">
          ユーザーの取得に失敗しました。{' '}
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
            placeholder="名前 or メールで検索"
          />

          {word && (
            <div className="search-summary">
              「{word}」の検索結果: {data.meta.total}件
            </div>
          )}

          <DataTable
            columns={columns}
            rows={data.data}
            rowKey={(row) => String(row.id)}
            empty="ユーザーがいません。"
          />

          <Pagination
            page={page}
            lastPage={data.meta.last_page}
            onChange={setPage}
          />
        </>
      )}

      <FormModal
        open={editing !== null}
        title="ユーザー編集"
        onClose={closeEdit}
        onSubmit={handleUpdate}
        submitting={updateMutation.isPending}
      >
          <div className="register-field">
            <label htmlFor="edit-name">名前</label>
            <input
              id="edit-name"
              type="text"
              value={fields.name}
              onChange={(e) => setField('name', e.target.value)}
            />
            {errorFor('name')?.map((msg) => (
              <span key={msg} className="register-field__error">{msg}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="edit-email">メールアドレス</label>
            <input
              id="edit-email"
              type="email"
              value={fields.email}
              onChange={(e) => setField('email', e.target.value)}
            />
            {errorFor('email')?.map((msg) => (
              <span key={msg} className="register-field__error">{msg}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="edit-role">権限</label>
            <select
              id="edit-role"
              value={fields.role}
              onChange={(e) => setField('role', e.target.value)}
            >
              <option value="user">一般</option>
              <option value="admin">管理者</option>
            </select>
            {errorFor('role')?.map((msg) => (
              <span key={msg} className="register-field__error">{msg}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="edit-password">パスワード（変更する場合のみ）</label>
            <input
              id="edit-password"
              type="password"
              autoComplete="new-password"
              value={fields.password}
              onChange={(e) => setField('password', e.target.value)}
            />
            <span className="register-field__hint">
              空欄の場合はパスワードを変更しません。
            </span>
            {errorFor('password')?.map((msg) => (
              <span key={msg} className="register-field__error">{msg}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="edit-password-confirm">パスワード（確認）</label>
            <input
              id="edit-password-confirm"
              type="password"
              autoComplete="new-password"
              value={fields.password_confirmation}
              onChange={(e) => setField('password_confirmation', e.target.value)}
            />
          </div>
      </FormModal>
    </>
  );
}

export default UsersPage;
