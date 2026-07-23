import { useState, type FormEvent } from 'react';
import { NavLink } from 'react-router-dom';
import { AxiosError } from 'axios';
import { useQueryClient } from '@tanstack/react-query';
import Alert from '@/components/ui/Alert';
import { useToast } from '@/components/ui/toast/useToast';
import {
  useRegisterClient,
  type RegisterClientPayload,
} from '@/features/clients/useRegisterClient';
import './register.css';

/** Laravel のバリデーションエラー応答（422）。 */
interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

interface ClientFields {
  company: string;
  url: string;
  tel: string;
  street_address: string;
  note: string;
}

const EMPTY_FIELDS: ClientFields = {
  company: '',
  url: '',
  tel: '',
  street_address: '',
  note: '',
};

/**
 * クライアント企業登録画面（旧 `client/register.blade.php` の企業フォームを移植）。
 * `POST /api/clients` に送信する。担当者の同時登録は CRM 連携前提のため対象外。
 */
function ClientRegisterPage() {
  const mutation = useRegisterClient();
  const queryClient = useQueryClient();
  const { show } = useToast();

  const [fields, setFields] = useState<ClientFields>(EMPTY_FIELDS);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState<string | null>(null);
  const [registeredId, setRegisteredId] = useState<string | null>(null);

  const setField = (key: keyof ClientFields, value: string) =>
    setFields((prev) => ({ ...prev, [key]: value }));

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFieldErrors({});
    setGeneralError(null);
    setRegisteredId(null);

    const payload: RegisterClientPayload = {
      company: fields.company,
      url: fields.url,
      tel: fields.tel,
      street_address: fields.street_address,
      note: fields.note || null,
    };

    try {
      const result = await mutation.mutateAsync(payload);
      setRegisteredId(result.client_id);
      show(`クライアント「${result.company ?? ''}」を登録しました。`, 'success');
      void queryClient.invalidateQueries({ queryKey: ['clients', 'list'] });
      setFields(EMPTY_FIELDS);
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
      show('クライアントの登録に失敗しました。', 'danger');
    }
  };

  const errorFor = (key: string) => fieldErrors[key];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-building" aria-hidden="true" />
        クライアント登録
      </div>

      <div className="register-card">
        {registeredId && (
          <Alert variant="success">
            クライアントを登録しました。{' '}
            <NavLink to={`/clients/${encodeURIComponent(registeredId)}`}>
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
            <label htmlFor="company">
              企業名 <span className="text-danger">*</span>
            </label>
            <input
              id="company"
              type="text"
              value={fields.company}
              onChange={(e) => setField('company', e.target.value)}
              required
            />
            {errorFor('company')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="url">
              会社URL <span className="text-danger">*</span>
            </label>
            <input
              id="url"
              type="text"
              value={fields.url}
              onChange={(e) => setField('url', e.target.value)}
              required
            />
            {errorFor('url')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="tel">
              電話番号 <span className="text-danger">*</span>
            </label>
            <input
              id="tel"
              type="tel"
              value={fields.tel}
              onChange={(e) => setField('tel', e.target.value)}
              required
            />
            {errorFor('tel')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="street_address">
              住所 <span className="text-danger">*</span>
            </label>
            <input
              id="street_address"
              type="text"
              value={fields.street_address}
              onChange={(e) => setField('street_address', e.target.value)}
              required
            />
            {errorFor('street_address')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
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

          <div className="register-actions">
            <button type="submit" className="osm-btn" disabled={mutation.isPending}>
              {mutation.isPending ? '登録中…' : '登録'}
            </button>
          </div>
        </form>
      </div>
    </>
  );
}

export default ClientRegisterPage;
