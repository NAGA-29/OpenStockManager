import { useAuth } from './auth/useAuth';

/**
 * アプリのルートコンポーネント（基盤整備中のプレースホルダ）。
 * 2-4 以降で React Router・共通レイアウトを組み込み、各画面へ置き換える。
 */
function App() {
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? '(未設定)';
  const { user, isLoading, isAuthenticated } = useAuth();

  return (
    <main className="app-shell">
      <h1>OpenStockManager</h1>
      <p>React + Vite + TypeScript への移行を準備中です。</p>
      <p className="app-meta">API Base URL: {apiBaseUrl}</p>
      <p className="app-meta">
        認証状態:{' '}
        {isLoading
          ? '確認中…'
          : isAuthenticated
            ? `ログイン中（${user?.name}）`
            : '未ログイン'}
      </p>
    </main>
  );
}

export default App;
