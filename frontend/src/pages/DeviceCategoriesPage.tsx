import { useState, type FormEvent } from 'react';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import Modal from '@/components/ui/Modal';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useToast } from '@/components/ui/toast/useToast';
import {
  useDeviceCategories,
  useCreateCategory,
  useUpdateCategory,
  useDeleteCategory,
  useReorderCategories,
  type DeviceCategory,
} from '@/features/settings/useDeviceCategories';
import './rental.css';
import './register.css';

interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

const EMPTY_CREATE = { code: '', name: '', icon: '' };

function DeviceCategoriesPage() {
  const { data, isLoading, isError, refetch } = useDeviceCategories();
  const createMutation = useCreateCategory();
  const updateMutation = useUpdateCategory();
  const deleteMutation = useDeleteCategory();
  const reorderMutation = useReorderCategories();
  const { show } = useToast();

  const [createFields, setCreateFields] = useState(EMPTY_CREATE);
  const [createErrors, setCreateErrors] = useState<Record<string, string[]>>({});

  const [editing, setEditing] = useState<DeviceCategory | null>(null);
  const [editFields, setEditFields] = useState({
    code: '',
    name: '',
    icon: '',
    is_active: true,
  });
  const [editErrors, setEditErrors] = useState<Record<string, string[]>>({});

  const handleCreate = async (e: FormEvent) => {
    e.preventDefault();
    setCreateErrors({});
    try {
      await createMutation.mutateAsync({
        code: createFields.code,
        name: createFields.name,
        icon: createFields.icon || undefined,
      });
      show(`カテゴリ「${createFields.name}」を追加しました。`, 'success');
      setCreateFields(EMPTY_CREATE);
    } catch (err) {
      const ax = err as AxiosError<ValidationErrorResponse>;
      if (ax.response?.status === 422 && ax.response.data?.errors) {
        setCreateErrors(ax.response.data.errors);
      } else {
        show('カテゴリの追加に失敗しました。', 'danger');
      }
    }
  };

  const openEdit = (cat: DeviceCategory) => {
    setEditing(cat);
    setEditFields({
      code: cat.code,
      name: cat.name,
      icon: cat.icon,
      is_active: cat.is_active,
    });
    setEditErrors({});
  };

  const handleEdit = async (e: FormEvent) => {
    e.preventDefault();
    if (!editing) return;
    setEditErrors({});
    try {
      await updateMutation.mutateAsync({
        id: editing.id,
        code: editFields.code,
        name: editFields.name,
        icon: editFields.icon || undefined,
        is_active: editFields.is_active,
      });
      show('カテゴリを更新しました。', 'success');
      setEditing(null);
    } catch (err) {
      const ax = err as AxiosError<ValidationErrorResponse>;
      if (ax.response?.status === 422 && ax.response.data?.errors) {
        setEditErrors(ax.response.data.errors);
      } else {
        show('カテゴリの更新に失敗しました。', 'danger');
      }
    }
  };

  const handleDelete = async (cat: DeviceCategory) => {
    if (!window.confirm(`カテゴリ「${cat.name}」を削除しますか？`)) return;
    try {
      const res = await deleteMutation.mutateAsync(cat.id);
      show(res.message, 'success');
    } catch (err) {
      const ax = err as AxiosError<ValidationErrorResponse>;
      show(ax.response?.data?.message ?? 'カテゴリの削除に失敗しました。', 'danger');
    }
  };

  const move = async (index: number, dir: -1 | 1) => {
    if (!data) return;
    const target = index + dir;
    if (target < 0 || target >= data.length) return;
    const ids = data.map((c) => c.id);
    [ids[index], ids[target]] = [ids[target], ids[index]];
    try {
      await reorderMutation.mutateAsync(ids);
    } catch {
      show('並び替えに失敗しました。', 'danger');
    }
  };

  const createErrorFor = (k: string) => createErrors[k];
  const editErrorFor = (k: string) => editErrors[k];

  const columns: Column<DeviceCategory>[] = [
    {
      key: 'order',
      header: '並び替え',
      render: (row) => {
        const index = data?.findIndex((c) => c.id === row.id) ?? -1;
        return (
          <span style={{ display: 'inline-flex', gap: '0.25rem' }}>
            <button
              type="button"
              className="osm-btn osm-btn--small"
              disabled={index <= 0 || reorderMutation.isPending}
              onClick={() => void move(index, -1)}
              aria-label="上へ"
            >
              <i className="fas fa-arrow-up" />
            </button>
            <button
              type="button"
              className="osm-btn osm-btn--small"
              disabled={
                index < 0 ||
                index >= (data?.length ?? 0) - 1 ||
                reorderMutation.isPending
              }
              onClick={() => void move(index, 1)}
              aria-label="下へ"
            >
              <i className="fas fa-arrow-down" />
            </button>
          </span>
        );
      },
    },
    {
      key: 'icon',
      header: 'アイコン',
      render: (row) => <i className={`fas ${row.icon}`} aria-hidden="true" />,
    },
    { key: 'code', header: 'コード' },
    { key: 'name', header: 'カテゴリ名' },
    { key: 'device_count', header: '機材数' },
    {
      key: 'is_active',
      header: '状態',
      render: (row) =>
        row.is_active ? (
          <span className="badge badge--success">有効</span>
        ) : (
          <span className="badge badge--warning">無効</span>
        ),
    },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <span style={{ display: 'inline-flex', gap: '0.35rem' }}>
          <button
            type="button"
            className="osm-btn osm-btn--small"
            onClick={() => openEdit(row)}
          >
            編集
          </button>
          <button
            type="button"
            className="osm-btn osm-btn--small osm-btn--danger"
            disabled={row.device_count > 0}
            title={row.device_count > 0 ? '機材が登録されているため削除できません' : undefined}
            onClick={() => void handleDelete(row)}
          >
            削除
          </button>
        </span>
      ),
    },
  ];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-tags" aria-hidden="true" />
        機材カテゴリ管理
      </div>

      {isError && (
        <Alert variant="danger">
          カテゴリの取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      <div className="register-card" style={{ maxWidth: '100%', marginBottom: '1.5rem' }}>
        <h3 style={{ marginTop: 0 }}>新規カテゴリ追加</h3>
        <form onSubmit={(e) => void handleCreate(e)} noValidate>
          <div className="register-field">
            <label htmlFor="new-code">
              コード <span className="text-danger">*</span>
            </label>
            <input
              id="new-code"
              type="text"
              placeholder="例: STB"
              value={createFields.code}
              onChange={(e) =>
                setCreateFields((p) => ({ ...p, code: e.target.value.toUpperCase() }))
              }
            />
            <span className="register-field__hint">半角英大文字・数字・アンダースコア。</span>
            {createErrorFor('code')?.map((m) => (
              <span key={m} className="register-field__error">{m}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="new-name">
              カテゴリ名 <span className="text-danger">*</span>
            </label>
            <input
              id="new-name"
              type="text"
              value={createFields.name}
              onChange={(e) => setCreateFields((p) => ({ ...p, name: e.target.value }))}
            />
            {createErrorFor('name')?.map((m) => (
              <span key={m} className="register-field__error">{m}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="new-icon">アイコン（Font Awesome クラス）</label>
            <input
              id="new-icon"
              type="text"
              placeholder="例: fa-tablet-alt"
              value={createFields.icon}
              onChange={(e) => setCreateFields((p) => ({ ...p, icon: e.target.value }))}
            />
            <span className="register-field__hint">未入力の場合は fa-cube を使用します。</span>
          </div>

          <div className="register-actions">
            <button
              type="submit"
              className="osm-btn osm-btn--primary"
              disabled={createMutation.isPending}
            >
              {createMutation.isPending ? '追加中…' : '追加'}
            </button>
          </div>
        </form>
      </div>

      {isLoading && <Loading />}

      {data && (
        <DataTable
          columns={columns}
          rows={data}
          rowKey={(row) => String(row.id)}
          empty="カテゴリがありません。"
        />
      )}

      <Modal open={editing !== null} title="カテゴリ編集" onClose={() => setEditing(null)}>
        <form onSubmit={(e) => void handleEdit(e)} noValidate>
          <div className="register-field">
            <label htmlFor="edit-code">コード</label>
            <input
              id="edit-code"
              type="text"
              value={editFields.code}
              onChange={(e) =>
                setEditFields((p) => ({ ...p, code: e.target.value.toUpperCase() }))
              }
            />
            <span className="register-field__hint">
              コードを変更すると、所属する機材の区分も自動で更新されます。
            </span>
            {editErrorFor('code')?.map((m) => (
              <span key={m} className="register-field__error">{m}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="edit-name">カテゴリ名</label>
            <input
              id="edit-name"
              type="text"
              value={editFields.name}
              onChange={(e) => setEditFields((p) => ({ ...p, name: e.target.value }))}
            />
            {editErrorFor('name')?.map((m) => (
              <span key={m} className="register-field__error">{m}</span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="edit-icon">アイコン</label>
            <input
              id="edit-icon"
              type="text"
              value={editFields.icon}
              onChange={(e) => setEditFields((p) => ({ ...p, icon: e.target.value }))}
            />
          </div>

          <div className="register-check">
            <input
              id="edit-active"
              type="checkbox"
              checked={editFields.is_active}
              onChange={(e) =>
                setEditFields((p) => ({ ...p, is_active: e.target.checked }))
              }
            />
            <label htmlFor="edit-active">有効</label>
          </div>

          <div className="register-actions">
            <button type="button" className="osm-btn" onClick={() => setEditing(null)}>
              キャンセル
            </button>
            <button
              type="submit"
              className="osm-btn osm-btn--primary"
              disabled={updateMutation.isPending}
            >
              {updateMutation.isPending ? '更新中…' : '更新'}
            </button>
          </div>
        </form>
      </Modal>
    </>
  );
}

export default DeviceCategoriesPage;
