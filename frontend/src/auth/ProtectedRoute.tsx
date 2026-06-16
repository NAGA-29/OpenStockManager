import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from './useAuth';

/**
 * 認証ガード。`isLoading` 中はローディング表示、未認証なら `/login` へ送る。
 * 認証済みなら子ルート（`<Outlet>`）を描画する。
 */
export function ProtectedRoute() {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return <div className="app-shell">読み込み中…</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
}
