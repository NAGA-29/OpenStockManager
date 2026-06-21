import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from './useAuth';

/**
 * 管理者ガード。認証済みかつ `is_admin` のときだけ子ルートを描画する。
 * 認証は親の `ProtectedRoute` で済んでいる前提。管理者でなければダッシュボードへ送る。
 */
export function AdminRoute() {
  const { isLoading, isAuthenticated, user } = useAuth();

  if (isLoading) {
    return <div className="app-shell">読み込み中…</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (!user?.is_admin) {
    return <Navigate to="/dashboard" replace />;
  }

  return <Outlet />;
}
