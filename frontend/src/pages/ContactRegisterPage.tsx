import { useState, type FormEvent } from 'react';
import { NavLink } from 'react-router-dom';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useToast } from '@/components/ui/toast/useToast';
import { useClients } from '@/features/clients/useClients';
import { useRegisterContact } from '@/features/contacts/useRegisterContact';
import './register.css';

/** Laravel のバリデーションエラー応答（422）。 */
interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

interface ContactFields {
  client_id: string;
  name: string;
  email: string;
  tel: string;
  note: string;
}

const EMPTY_FIELDS: ContactFields = {
  client_id: '',
  name: '',
  email: '',
  tel: '',
  note: '',
};

/**
 * 担当者登録画面（旧 `contacts/register.blade.php` を移植）。
 * `POST /api/contacts` に送信する。
 */
function ContactRegisterPage() {
  const { data: clients = [], isLoading: clientsLoading } = useClients('');
  const mutation = useRegisterContact();
  const { show } = useToast();

  const [fields, setFields] = useState<ContactFields>(EMPTY_FIELDS);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState<string | null>(null);
  const [registeredId, setRegisteredId] = useState<number | null>(null);

  const setField = (key: keyof ContactFields, value: string) =>
    setFields((prev) => ({ ...prev, [key]: value }));

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFieldErrors({});
    setGeneralError(null);
    setRegisteredId(null);

    try {
      const result = await mutation.mutateAsync({
        client_id: fields.client_id,
        name: fields.name,
        email: fields.email,
        tel: fields.tel,
        note: fields.note || undefined,
      });
      setRegisteredId(result.id);
      show(`担当者「${result.name ?? ''}」を登録しました。`, 'success');
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
      show('担当者の登録に失敗しました。', 'danger');
    }
  };

  const errorFor = (key: string) => fieldErrors[key];

  if (clientsLoading) {
    return <Loading />;
  }

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-user-tie" aria-hidden="true" />
        担当者登録
      </div>

      <div className="register-card">
        {registeredId && (
          <Alert variant="success">
            担当者を登録しました。{' '}
            <NavLink to={`/contacts/${registeredId}`}>
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
            <label htmlFor="client_id">
              所属クライアント <span className="text-danger">*</span>
            </label>
            <select
              id="client_id"
              value={fields.client_id}
              onChange={(e) => setField('client_id', e.target.value)}
              required
            >
              <option value="">-- 選択してください --</option>
              {clients.map((client) => (
                <option key={client.client_id} value={client.client_id}>
                  {client.company}
                </option>
              ))}
            </select>
            {errorFor('client_id')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="name">
              担当者名 <span className="text-danger">*</span>
            </label>
            <input
              id="name"
              type="text"
              value={fields.name}
              onChange={(e) => setField('name', e.target.value)}
              required
            />
            {errorFor('name')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="email">
              メールアドレス <span className="text-danger">*</span>
            </label>
            <input
              id="email"
              type="email"
              value={fields.email}
              onChange={(e) => setField('email', e.target.value)}
              required
            />
            {errorFor('email')?.map((msg) => (
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

export default ContactRegisterPage;
