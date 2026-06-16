/**
 * アプリのルートコンポーネント（2-1 初期化時点のプレースホルダ）。
 * 2-2 以降で Axios クライアント・認証コンテキスト・React Router・
 * TanStack Query を順次組み込む。
 */
function App() {
  const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? '(未設定)';

  return (
    <main className="app-shell">
      <h1>OpenStockManager</h1>
      <p>React + Vite + TypeScript への移行を準備中です。</p>
      <p className="app-meta">API Base URL: {apiBaseUrl}</p>
    </main>
  );
}

export default App;
