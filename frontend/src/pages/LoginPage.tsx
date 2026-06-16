import { Navigate } from 'react-router-dom';
import { useAuth } from '@/auth/useAuth';

/**
 * ログイン画面のプレースホルダ（ルーティング基盤 2-4 時点）。
 * 認証済みならダッシュボードへ。ログインフォーム本体は 3-1 で実装する。
 */
function LoginPage() {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return <div className="app-shell">読み込み中…</div>;
  }

  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />;
  }

  return (
    <main className="app-shell">
      <h1>ログイン</h1>
      <p className="app-meta">ログインフォームは 3-1（認証ドメイン）で実装します。</p>
    </main>
  );
}

export default LoginPage;
