import { createBrowserRouter, Navigate } from 'react-router-dom';
import { ProtectedRoute } from './auth/ProtectedRoute';
import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import NotFoundPage from './pages/NotFoundPage';

/**
 * アプリのルーティング定義。
 * - `/login` は公開
 * - 保護ルートは `ProtectedRoute` 配下（未認証は `/login` へ）
 * 画面は移行に合わせて順次追加していく（Phase 3）。
 */
export const router = createBrowserRouter([
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    element: <ProtectedRoute />,
    children: [
      { index: true, element: <Navigate to="/dashboard" replace /> },
      { path: '/dashboard', element: <DashboardPage /> },
    ],
  },
  {
    path: '*',
    element: <NotFoundPage />,
  },
]);
