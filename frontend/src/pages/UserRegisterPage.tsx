import { useState, type FormEvent } from 'react';
import { NavLink } from 'react-router-dom';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import { useToast } from '@/components/ui/toast/useToast';
import { useCreateUser, type UserRole } from '@/features/users/useUsers';
import './register.css';

/** Laravel のバリデーションエラー応答（422）。 */
interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

interface UserFields {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: UserRole;
}

const EMPTY_FIELDS: UserFields = {
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'user',
};

/**
 * ユーザー登録画面（管理者のみ。旧 `user/register.blade.php` を移植）。
 * `POST /api/users` に送信する。
 */
function UserRegisterPage() {
  const mutation = useCreateUser();
  const { show } = useToast();

  const [fields, setFields] = useState<UserFields>(EMPTY_FIELDS);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState<string | null>(null);
  const [registered, setRegistered] = useState(false);

  const setField = (key: keyof UserFields, value: string) =>
    setFields((prev) => ({ ...prev, [key]: value }));

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFieldErrors({});
    setGeneralError(null);
    setRegistered(false);

    try {
      const result = await mutation.mutateAsync({
        name: fields.name,
        email: fields.email,
        password: fields.password,
        password_confirmation: fields.password_confirmation,
        role: fields.role,
      });
      setRegistered(true);
      show(`ユーザー「${result.name}」を登録しました。`, 'success');
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
      show('ユーザーの登録に失敗しました。', 'danger');
    }
  };

  const errorFor = (key: string) => fieldErrors[key];

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-user-plus" aria-hidden="true" />
        ユーザー登録
      </div>

      <div className="register-card">
        {registered && (
          <Alert variant="success">
            ユーザーを登録しました。{' '}
            <NavLink to="/users">一覧へ戻る</NavLink>
          </Alert>
        )}

        {generalError && <Alert variant="danger">{generalError}</Alert>}

        <p className="register-note">
          [ <span className="text-danger">*</span> ] は入力必須
        </p>

        <form onSubmit={(e) => void handleSubmit(e)} noValidate>
          <div className="register-field">
            <label htmlFor="name">
              名前 <span className="text-danger">*</span>
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
            <label htmlFor="role">
              権限 <span className="text-danger">*</span>
            </label>
            <select
              id="role"
              value={fields.role}
              onChange={(e) => setField('role', e.target.value)}
              required
            >
              <option value="user">一般</option>
              <option value="admin">管理者</option>
            </select>
            {errorFor('role')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="password">
              パスワード <span className="text-danger">*</span>
            </label>
            <input
              id="password"
              type="password"
              autoComplete="new-password"
              value={fields.password}
              onChange={(e) => setField('password', e.target.value)}
              required
            />
            <span className="register-field__hint">
              8文字以上・大文字小文字・数字を含めてください。
            </span>
            {errorFor('password')?.map((msg) => (
              <span key={msg} className="register-field__error">
                {msg}
              </span>
            ))}
          </div>

          <div className="register-field">
            <label htmlFor="password_confirmation">
              パスワード（確認） <span className="text-danger">*</span>
            </label>
            <input
              id="password_confirmation"
              type="password"
              autoComplete="new-password"
              value={fields.password_confirmation}
              onChange={(e) => setField('password_confirmation', e.target.value)}
              required
            />
          </div>

          <div className="register-actions">
            <NavLink to="/users" className="osm-btn">
              一覧へ戻る
            </NavLink>
            <button type="submit" className="osm-btn osm-btn--primary" disabled={mutation.isPending}>
              {mutation.isPending ? '登録中…' : '登録'}
            </button>
          </div>
        </form>
      </div>
    </>
  );
}

export default UserRegisterPage;
