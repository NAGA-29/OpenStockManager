import { useState, type FormEvent } from 'react';
import { Navigate } from 'react-router-dom';
import { AxiosError } from 'axios';
import { useAuth } from '@/auth/useAuth';
import './login.css';

/** Laravel のバリデーションエラー応答（422）。 */
interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

/**
 * ログイン画面（旧 `auth/login.blade.php` を踏襲）。
 * email/password を `useAuth().login()` に渡し、422 のフィールドエラー
 * （`auth.failed` は `errors.email` に入る）を各入力欄に表示する。
 */
function LoginPage() {
  const { isAuthenticated, isLoading, login } = useAuth();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  if (isLoading) {
    return <div className="app-shell">読み込み中…</div>;
  }

  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />;
  }

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFieldErrors({});
    setGeneralError(null);
    setSubmitting(true);

    try {
      await login(email, password);
      // 成功時は isAuthenticated が true になり、上の Navigate で /dashboard へ。
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      const status = axiosErr.response?.status;
      const data = axiosErr.response?.data;

      if (status === 422 && data?.errors) {
        setFieldErrors(data.errors);
      } else if (axiosErr.response) {
        setGeneralError(
          data?.message ?? 'ログインに失敗しました。時間をおいて再度お試しください。',
        );
      } else {
        setGeneralError('サーバーに接続できませんでした。ネットワークをご確認ください。');
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="login-screen">
      <div className="login-card">
        <aside className="login-card__aside">
          <span className="login-card__badge">Device Manager</span>
          <h2>Welcome</h2>
          <p>管理者から発行されたアカウント情報でログインしてください。</p>
          <ul className="login-card__features">
            <li>
              <i className="fas fa-tablet-alt" aria-hidden="true" />
              <span>デバイス管理・ステータス確認を一元化</span>
            </li>
            <li>
              <i className="fas fa-history" aria-hidden="true" />
              <span>履歴情報を安全にトラッキング</span>
            </li>
          </ul>
        </aside>

        <div className="login-card__form">
          <div className="login-card__form-header">
            <h3>ログイン</h3>
            <p>メールアドレスとパスワードを入力してください</p>
          </div>

          {generalError && (
            <div className="login-alert" role="alert">
              {generalError}
            </div>
          )}

          <form onSubmit={(e) => void handleSubmit(e)} noValidate>
            <div className="login-field">
              <label htmlFor="email">メールアドレス</label>
              <input
                id="email"
                name="email"
                type="email"
                className={fieldErrors.email ? 'is-invalid' : undefined}
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                autoComplete="email"
                autoFocus
              />
              {fieldErrors.email?.map((msg) => (
                <span key={msg} className="login-field__error" role="alert">
                  {msg}
                </span>
              ))}
            </div>

            <div className="login-field">
              <label htmlFor="password">パスワード</label>
              <input
                id="password"
                name="password"
                type="password"
                className={fieldErrors.password ? 'is-invalid' : undefined}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                autoComplete="current-password"
              />
              {fieldErrors.password?.map((msg) => (
                <span key={msg} className="login-field__error" role="alert">
                  {msg}
                </span>
              ))}
            </div>

            <button type="submit" className="login-submit" disabled={submitting}>
              <i className="fas fa-sign-in-alt" aria-hidden="true" />{' '}
              {submitting ? 'ログイン中…' : 'ログイン'}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

export default LoginPage;
