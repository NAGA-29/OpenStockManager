import { useEffect, useMemo, useState, type FormEvent } from 'react';
import { NavLink } from 'react-router-dom';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useToast } from '@/components/ui/toast/useToast';
import { useDeviceFormOptions } from '@/features/inventory/useDeviceFormOptions';
import {
  useRegisterDevice,
  type RegisterDevicePayload,
} from '@/features/inventory/useRegisterDevice';
import './register.css';

/** Laravel のバリデーションエラー応答（422）。 */
interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

interface SimpleFields {
  device_type: string;
  device_name: string;
  device_serial: string;
  first_work_date_at: string;
  purchase_date_at: string;
  client: string;
  condition: string;
  note: string;
}

const EMPTY_FIELDS: SimpleFields = {
  device_type: '',
  device_name: '',
  device_serial: '',
  first_work_date_at: '',
  purchase_date_at: '',
  client: '',
  condition: '',
  note: '',
};

/**
 * 端末単体登録画面（旧 `register_device` の単体タブを移植）。
 * カテゴリ選択に応じてカスタムフィールドを動的描画し、`POST /api/devices` に送信する。
 * 画像アップロード・CSV 一括登録は後続フェーズで対応。
 */
function RegisterDevicePage() {
  const { data: options, isLoading, isError, refetch } = useDeviceFormOptions();
  const mutation = useRegisterDevice();
  const { show } = useToast();

  const [fields, setFields] = useState<SimpleFields>(EMPTY_FIELDS);
  const [customValues, setCustomValues] = useState<Record<string, string | boolean>>({});
  const [defective, setDefective] = useState(false);
  const [notForSale, setNotForSale] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState<string | null>(null);
  const [registeredId, setRegisteredId] = useState<string | null>(null);

  // 選択肢ロード後に初期値（先頭カテゴリ・先頭コンディション）を設定する。
  useEffect(() => {
    if (!options) return;
    setFields((prev) => ({
      ...prev,
      device_type: prev.device_type || options.categories[0]?.code || '',
      condition: prev.condition || String(options.conditions[0]?.id ?? ''),
    }));
  }, [options]);

  const currentCategory = useMemo(
    () => options?.categories.find((cat) => cat.code === fields.device_type),
    [options, fields.device_type],
  );

  const setField = (key: keyof SimpleFields, value: string) =>
    setFields((prev) => ({ ...prev, [key]: value }));

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFieldErrors({});
    setGeneralError(null);
    setRegisteredId(null);

    const customFields: Record<string, string | boolean> = {};
    for (const def of currentCategory?.fields ?? []) {
      const value = customValues[def.field_key];
      if (def.field_type === 'boolean') {
        customFields[def.field_key] = value === true;
      } else if (value !== undefined && value !== '') {
        customFields[def.field_key] = value;
      }
    }

    const payload: RegisterDevicePayload = {
      device_type: fields.device_type,
      device_name: fields.device_name,
      device_serial: fields.device_serial,
      first_work_date_at: fields.first_work_date_at || null,
      purchase_date_at: fields.purchase_date_at || null,
      client: fields.client || null,
      condition: Number(fields.condition),
      defective,
      not_for_sale: notForSale,
      note: fields.note || null,
      ...(Object.keys(customFields).length > 0 ? { custom_fields: customFields } : {}),
    };

    try {
      const result = await mutation.mutateAsync(payload);
      setRegisteredId(result.device_id);
      show(`端末 ${result.device_id} を登録しました。`, 'success');
      // フォームをリセット（カテゴリ／コンディションは保持）。
      setFields((prev) => ({
        ...EMPTY_FIELDS,
        device_type: prev.device_type,
        condition: prev.condition,
      }));
      setCustomValues({});
      setDefective(false);
      setNotForSale(false);
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      const status = axiosErr.response?.status;
      const responseData = axiosErr.response?.data;

      if (status === 422 && responseData?.errors) {
        setFieldErrors(responseData.errors);
        setGeneralError('登録内容に誤りがあります。');
      } else if (axiosErr.response) {
        setGeneralError(responseData?.message ?? '登録に失敗しました。');
      } else {
        setGeneralError('サーバーに接続できませんでした。ネットワークをご確認ください。');
      }
      show('端末の登録に失敗しました。', 'danger');
    }
  };

  const errorFor = (key: string) => fieldErrors[key];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-plus-circle" aria-hidden="true" />
        端末登録（単体）
      </div>

      {isLoading && <Loading />}

      {isError && (
        <Alert variant="danger">
          フォームの初期化に失敗しました。{' '}
          <button type="button" onClick={() => void refetch()}>
            再読み込み
          </button>
        </Alert>
      )}

      {options && (
        <div className="register-card">
          {registeredId && (
            <Alert variant="success">
              端末 <strong>{registeredId}</strong> を登録しました。{' '}
              <NavLink to={`/devices/${encodeURIComponent(registeredId)}`}>
                詳細を見る
              </NavLink>
            </Alert>
          )}

          {generalError && <Alert variant="danger">{generalError}</Alert>}

          <p className="register-note">
            [ <span className="text-danger">*</span> ] は入力必須
          </p>

          <form onSubmit={(e) => void handleSubmit(e)} noValidate>
            <div className="register-field">
              <label htmlFor="device_type">
                端末区分 <span className="text-danger">*</span>
              </label>
              <select
                id="device_type"
                value={fields.device_type}
                onChange={(e) => setField('device_type', e.target.value)}
                required
              >
                {options.categories.map((cat) => (
                  <option key={cat.code} value={cat.code}>
                    {cat.name}
                  </option>
                ))}
              </select>
              {errorFor('device_type')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            <div className="register-field">
              <label htmlFor="device_name">
                端末名 <span className="text-danger">*</span>
              </label>
              <input
                id="device_name"
                type="text"
                value={fields.device_name}
                onChange={(e) => setField('device_name', e.target.value)}
                required
              />
              {errorFor('device_name')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            <div className="register-field">
              <label htmlFor="device_serial">
                端末シリアル <span className="text-danger">*</span>
              </label>
              <input
                id="device_serial"
                type="text"
                value={fields.device_serial}
                onChange={(e) => setField('device_serial', e.target.value)}
                required
              />
              {errorFor('device_serial')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            {/* カスタムフィールド（カテゴリに応じて動的表示） */}
            {currentCategory && currentCategory.fields.length > 0 && (
              <>
                <hr />
                <p className="register-custom-title">カスタムフィールド</p>
                {currentCategory.fields.map((def) => {
                  const errKey = `custom_fields.${def.field_key}`;
                  return (
                    <div className="register-field" key={def.field_key}>
                      <label htmlFor={`cf_${def.field_key}`}>
                        {def.label}{' '}
                        {def.is_required && <span className="text-danger">*</span>}
                      </label>
                      {def.field_type === 'boolean' ? (
                        <input
                          id={`cf_${def.field_key}`}
                          type="checkbox"
                          checked={customValues[def.field_key] === true}
                          onChange={(e) =>
                            setCustomValues((prev) => ({
                              ...prev,
                              [def.field_key]: e.target.checked,
                            }))
                          }
                        />
                      ) : def.field_type === 'select' ? (
                        <select
                          id={`cf_${def.field_key}`}
                          value={(customValues[def.field_key] as string) ?? ''}
                          onChange={(e) =>
                            setCustomValues((prev) => ({
                              ...prev,
                              [def.field_key]: e.target.value,
                            }))
                          }
                          required={def.is_required}
                        >
                          {!def.is_required && <option value="">-- 選択 --</option>}
                          {(def.options ?? []).map((opt) => (
                            <option key={opt.value} value={opt.value}>
                              {opt.label}
                            </option>
                          ))}
                        </select>
                      ) : (
                        <input
                          id={`cf_${def.field_key}`}
                          type={def.field_type === 'number' ? 'number' : 'text'}
                          value={(customValues[def.field_key] as string) ?? ''}
                          onChange={(e) =>
                            setCustomValues((prev) => ({
                              ...prev,
                              [def.field_key]: e.target.value,
                            }))
                          }
                          required={def.is_required}
                        />
                      )}
                      {errorFor(errKey)?.map((msg) => (
                        <span key={msg} className="register-field__error">
                          {msg}
                        </span>
                      ))}
                    </div>
                  );
                })}
                <hr />
              </>
            )}

            <div className="register-field">
              <label htmlFor="first_work_date_at">初稼働日</label>
              <input
                id="first_work_date_at"
                type="date"
                value={fields.first_work_date_at}
                onChange={(e) => setField('first_work_date_at', e.target.value)}
              />
              {errorFor('first_work_date_at')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            <div className="register-field">
              <label htmlFor="purchase_date_at">購入日</label>
              <input
                id="purchase_date_at"
                type="date"
                value={fields.purchase_date_at}
                onChange={(e) => setField('purchase_date_at', e.target.value)}
              />
              {errorFor('purchase_date_at')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            <div className="register-field">
              <label htmlFor="client">販売先</label>
              <input
                id="client"
                type="text"
                value={fields.client}
                onChange={(e) => setField('client', e.target.value)}
              />
              {errorFor('client')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            <div className="register-field">
              <label htmlFor="condition">コンディション</label>
              <select
                id="condition"
                value={fields.condition}
                onChange={(e) => setField('condition', e.target.value)}
              >
                {options.conditions.map((cond) => (
                  <option key={cond.id} value={cond.id}>
                    {cond.label}
                  </option>
                ))}
              </select>
              {errorFor('condition')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            <div className="register-check">
              <input
                id="defective"
                type="checkbox"
                checked={defective}
                onChange={(e) => setDefective(e.target.checked)}
              />
              <label htmlFor="defective">不具合</label>
            </div>

            <div className="register-check">
              <input
                id="not_for_sale"
                type="checkbox"
                checked={notForSale}
                onChange={(e) => setNotForSale(e.target.checked)}
              />
              <label htmlFor="not_for_sale">販売不可</label>
            </div>

            <div className="register-field">
              <label htmlFor="note">ノート</label>
              <textarea
                id="note"
                rows={5}
                value={fields.note}
                onChange={(e) => setField('note', e.target.value)}
              />
              {errorFor('note')?.map((msg) => (
                <span key={msg} className="register-field__error">
                  {msg}
                </span>
              ))}
            </div>

            <p className="register-note register-note--muted">
              ※ 端末写真のアップロードは現在準備中です。
            </p>

            <div className="register-actions">
              <button type="submit" className="osm-btn" disabled={mutation.isPending}>
                {mutation.isPending ? '登録中…' : '登録'}
              </button>
            </div>
          </form>
        </div>
      )}
    </>
  );
}

export default RegisterDevicePage;
