import { useAuth } from '@/auth/useAuth';

/**
 * ダッシュボードのプレースホルダ（保護ルート）。
 * 集計表示の本体は 3-2 で `GET /api/dashboard` を用いて実装する。
 */
function DashboardPage() {
  const { user, logout } = useAuth();
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? '(未設定)';

  return (
    <main className="app-shell">
      <h1>ダッシュボード</h1>
      <p>ようこそ、{user?.name} さん。</p>
      <p className="app-meta">API Base URL: {apiBaseUrl}</p>
      <button type="button" onClick={() => void logout()}>
        ログアウト
      </button>
    </main>
  );
}

export default DashboardPage;
