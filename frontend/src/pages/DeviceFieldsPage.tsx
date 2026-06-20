import { useState, type FormEvent } from 'react';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import FormModal from '@/components/ui/FormModal';
import { useToast } from '@/components/ui/toast/useToast';
import { useDeviceCategories } from '@/features/settings/useDeviceCategories';
import {
  useDeviceFields,
  useCreateField,
  useUpdateField,
  useDeleteField,
  useReorderFields,
  type DeviceField,
  type FieldType,
  type FieldOption,
} from '@/features/settings/useDeviceFields';
import './rental.css';
import './register.css';
import './clients.css';

interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

interface FieldFormState {
  label: string;
  field_type: FieldType;
  is_required: boolean;
  options: FieldOption[];
}

const EMPTY_FORM: FieldFormState = {
  label: '',
  field_type: 'text',
  is_required: false,
  options: [],
};

/** select 型の選択肢エディタ。 */
function OptionsEditor({
  options,
  onChange,
}: {
  options: FieldOption[];
  onChange: (next: FieldOption[]) => void;
}) {
  const update = (i: number, key: keyof FieldOption, value: string) => {
    const next = options.map((o, idx) => (idx === i ? { ...o, [key]: value } : o));
    onChange(next);
  };
  return (
    <div className="register-field">
      <label>選択肢</label>
      {options.map((opt, i) => (
        <div key={i} style={{ display: 'flex', gap: '0.5rem', marginBottom: '0.35rem' }}>
          <input
            type="text"
            placeholder="ラベル"
            value={opt.label}
            onChange={(e) => update(i, 'label', e.target.value)}
          />
          <input
            type="text"
            placeholder="値"
            value={opt.value}
            onChange={(e) => update(i, 'value', e.target.value)}
          />
          <button
            type="button"
            className="osm-btn osm-btn--small osm-btn--danger"
            onClick={() => onChange(options.filter((_, idx) => idx !== i))}
          >
            削除
          </button>
        </div>
      ))}
      <button
        type="button"
        className="osm-btn osm-btn--small"
        onClick={() => onChange([...options, { label: '', value: '' }])}
      >
        + 選択肢を追加
      </button>
    </div>
  );
}

/** 共通のフィールド入力（label / type / required / options）。 */
function FieldFormBody({
  state,
  setState,
  errors,
  fieldTypes,
  idPrefix,
}: {
  state: FieldFormState;
  setState: (next: FieldFormState) => void;
  errors: Record<string, string[]>;
  fieldTypes: Record<string, string>;
  idPrefix: string;
}) {
  return (
    <>
      <div className="register-field">
        <label htmlFor={`${idPrefix}-label`}>
          ラベル <span className="text-danger">*</span>
        </label>
        <input
          id={`${idPrefix}-label`}
          type="text"
          value={state.label}
          onChange={(e) => setState({ ...state, label: e.target.value })}
        />
        {errors.label?.map((m) => (
          <span key={m} className="register-field__error">{m}</span>
        ))}
      </div>

      <div className="register-field">
        <label htmlFor={`${idPrefix}-type`}>
          種別 <span className="text-danger">*</span>
        </label>
        <select
          id={`${idPrefix}-type`}
          value={state.field_type}
          onChange={(e) =>
            setState({ ...state, field_type: e.target.value as FieldType })
          }
        >
          {Object.entries(fieldTypes).map(([value, label]) => (
            <option key={value} value={value}>
              {label}
            </option>
          ))}
        </select>
        {errors.field_type?.map((m) => (
          <span key={m} className="register-field__error">{m}</span>
        ))}
      </div>

      {state.field_type === 'select' && (
        <OptionsEditor
          options={state.options}
          onChange={(options) => setState({ ...state, options })}
        />
      )}

      <div className="register-check">
        <input
          id={`${idPrefix}-required`}
          type="checkbox"
          checked={state.is_required}
          onChange={(e) => setState({ ...state, is_required: e.target.checked })}
        />
        <label htmlFor={`${idPrefix}-required`}>必須項目にする</label>
      </div>
    </>
  );
}

function DeviceFieldsPage() {
  const { data: fieldsData, isLoading, isError, refetch } = useDeviceFields();
  const { data: categories } = useDeviceCategories();
  const createMutation = useCreateField();
  const updateMutation = useUpdateField();
  const deleteMutation = useDeleteField();
  const reorderMutation = useReorderFields();
  const { show } = useToast();

  const [newCategory, setNewCategory] = useState('');
  const [createForm, setCreateForm] = useState<FieldFormState>(EMPTY_FORM);
  const [createErrors, setCreateErrors] = useState<Record<string, string[]>>({});

  const [editing, setEditing] = useState<DeviceField | null>(null);
  const [editForm, setEditForm] = useState<FieldFormState>(EMPTY_FORM);
  const [editErrors, setEditErrors] = useState<Record<string, string[]>>({});

  const fieldTypes = fieldsData?.field_types ?? {};
  const allFields = fieldsData?.data ?? [];

  const typeLabel = (t: string) => fieldTypes[t] ?? t;

  const handleCreate = async (e: FormEvent) => {
    e.preventDefault();
    setCreateErrors({});
    if (!newCategory) {
      setCreateErrors({ device_category_code: ['カテゴリを選択してください。'] });
      return;
    }
    try {
      await createMutation.mutateAsync({
        device_category_code: newCategory,
        label: createForm.label,
        field_type: createForm.field_type,
        is_required: createForm.is_required,
        options: createForm.field_type === 'select' ? createForm.options : undefined,
      });
      show(`フィールド「${createForm.label}」を追加しました。`, 'success');
      setCreateForm(EMPTY_FORM);
    } catch (err) {
      const ax = err as AxiosError<ValidationErrorResponse>;
      if (ax.response?.status === 422 && ax.response.data?.errors) {
        setCreateErrors(ax.response.data.errors);
      } else {
        show('フィールドの追加に失敗しました。', 'danger');
      }
    }
  };

  const openEdit = (field: DeviceField) => {
    setEditing(field);
    setEditForm({
      label: field.label,
      field_type: field.field_type,
      is_required: field.is_required,
      options: field.options ?? [],
    });
    setEditErrors({});
  };

  const handleEdit = async () => {
    if (!editing) return;
    setEditErrors({});
    try {
      await updateMutation.mutateAsync({
        id: editing.id,
        label: editForm.label,
        field_type: editForm.field_type,
        is_required: editForm.is_required,
        options: editForm.field_type === 'select' ? editForm.options : undefined,
      });
      show('フィールドを更新しました。', 'success');
      setEditing(null);
    } catch (err) {
      const ax = err as AxiosError<ValidationErrorResponse>;
      if (ax.response?.status === 422 && ax.response.data?.errors) {
        setEditErrors(ax.response.data.errors);
      } else {
        show('フィールドの更新に失敗しました。', 'danger');
      }
    }
  };

  const handleDelete = async (field: DeviceField) => {
    if (!window.confirm(`フィールド「${field.label}」を削除しますか？`)) return;
    try {
      const res = await deleteMutation.mutateAsync(field.id);
      show(res.message, 'success');
    } catch {
      show('フィールドの削除に失敗しました。', 'danger');
    }
  };

  /** 同一カテゴリ内で並び替え。 */
  const move = async (categoryFields: DeviceField[], index: number, dir: -1 | 1) => {
    const target = index + dir;
    if (target < 0 || target >= categoryFields.length) return;
    const ids = categoryFields.map((f) => f.id);
    [ids[index], ids[target]] = [ids[target], ids[index]];
    try {
      await reorderMutation.mutateAsync(ids);
    } catch {
      show('並び替えに失敗しました。', 'danger');
    }
  };

  // カテゴリごとにフィールドをグループ化（カテゴリの並び順を踏襲）。
  const groups = (categories ?? []).map((cat) => ({
    category: cat,
    fields: allFields.filter((f) => f.device_category_code === cat.code),
  }));

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-list-ul" aria-hidden="true" />
        カスタムフィールド管理
      </div>

      {isError && (
        <Alert variant="danger">
          フィールドの取得に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      <div className="register-card" style={{ maxWidth: '100%', marginBottom: '1.5rem' }}>
        <h3 style={{ marginTop: 0 }}>新規フィールド追加</h3>
        <form onSubmit={(e) => void handleCreate(e)} noValidate>
          <div className="register-field">
            <label htmlFor="new-category">
              カテゴリ <span className="text-danger">*</span>
            </label>
            <select
              id="new-category"
              value={newCategory}
              onChange={(e) => setNewCategory(e.target.value)}
            >
              <option value="">-- 選択してください --</option>
              {(categories ?? []).map((cat) => (
                <option key={cat.code} value={cat.code}>
                  {cat.name}（{cat.code}）
                </option>
              ))}
            </select>
            {createErrors.device_category_code?.map((m) => (
              <span key={m} className="register-field__error">{m}</span>
            ))}
          </div>

          <FieldFormBody
            state={createForm}
            setState={setCreateForm}
            errors={createErrors}
            fieldTypes={fieldTypes}
            idPrefix="new"
          />

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

      {fieldsData &&
        groups.map(({ category, fields }) => (
          <div key={category.code} className="device-card" style={{ marginBottom: '1.5rem' }}>
            <div className="device-card__header">
              <i className={`fas ${category.icon}`} aria-hidden="true" /> {category.name}（{category.code}）
            </div>
            <div className="device-card__body">
              {fields.length === 0 ? (
                <p style={{ color: '#6b7280', margin: 0 }}>フィールドがありません。</p>
              ) : (
                <table className="device-info-table">
                  <thead>
                    <tr>
                      <th>並び替え</th>
                      <th>ラベル</th>
                      <th>キー</th>
                      <th>種別</th>
                      <th>必須</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {fields.map((field, index) => (
                      <tr key={field.id}>
                        <td>
                          <span style={{ display: 'inline-flex', gap: '0.25rem' }}>
                            <button
                              type="button"
                              className="osm-btn osm-btn--small"
                              disabled={index <= 0 || reorderMutation.isPending}
                              onClick={() => void move(fields, index, -1)}
                              aria-label="上へ"
                            >
                              <i className="fas fa-arrow-up" />
                            </button>
                            <button
                              type="button"
                              className="osm-btn osm-btn--small"
                              disabled={index >= fields.length - 1 || reorderMutation.isPending}
                              onClick={() => void move(fields, index, 1)}
                              aria-label="下へ"
                            >
                              <i className="fas fa-arrow-down" />
                            </button>
                          </span>
                        </td>
                        <td>{field.label}</td>
                        <td><code>{field.field_key}</code></td>
                        <td>{typeLabel(field.field_type)}</td>
                        <td>
                          {field.is_required ? (
                            <span className="badge badge--warning">必須</span>
                          ) : (
                            '-'
                          )}
                        </td>
                        <td>
                          <span style={{ display: 'inline-flex', gap: '0.35rem' }}>
                            <button
                              type="button"
                              className="osm-btn osm-btn--small"
                              onClick={() => openEdit(field)}
                            >
                              編集
                            </button>
                            <button
                              type="button"
                              className="osm-btn osm-btn--small osm-btn--danger"
                              onClick={() => void handleDelete(field)}
                            >
                              削除
                            </button>
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              )}
            </div>
          </div>
        ))}

      <FormModal
        open={editing !== null}
        title="フィールド編集"
        onClose={() => setEditing(null)}
        onSubmit={handleEdit}
        submitting={updateMutation.isPending}
      >
        <FieldFormBody
          state={editForm}
          setState={setEditForm}
          errors={editErrors}
          fieldTypes={fieldTypes}
          idPrefix="edit"
        />
      </FormModal>
    </>
  );
}

export default DeviceFieldsPage;
