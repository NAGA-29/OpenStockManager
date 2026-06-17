import { createBrowserRouter, Navigate } from 'react-router-dom';
import { ProtectedRoute } from './auth/ProtectedRoute';
import AppLayout from './layouts/AppLayout';
import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import InventoryStocksPage from './pages/InventoryStocksPage';
import InventoryUnitsPage from './pages/InventoryUnitsPage';
import DeviceDetailPage from './pages/DeviceDetailPage';
import RegisterDevicePage from './pages/RegisterDevicePage';
import ClientsPage from './pages/ClientsPage';
import ClientRegisterPage from './pages/ClientRegisterPage';
import ClientDetailPage from './pages/ClientDetailPage';
import ContactsPage from './pages/ContactsPage';
import ContactRegisterPage from './pages/ContactRegisterPage';
import ContactDetailPage from './pages/ContactDetailPage';
import NotFoundPage from './pages/NotFoundPage';

/**
 * アプリのルーティング定義。
 * - `/login` は公開
 * - 保護ルートは `ProtectedRoute`（認証ガード）→ `AppLayout`（共通レイアウト）配下に置き、
 *   各保護ページは `AppLayout` の `<Outlet>` に流す。
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
      {
        element: <AppLayout />,
        children: [
          { index: true, element: <Navigate to="/dashboard" replace /> },
          { path: '/dashboard', element: <DashboardPage /> },
          { path: '/inventory/stocks', element: <InventoryStocksPage /> },
          { path: '/inventory/units/:code', element: <InventoryUnitsPage /> },
          { path: '/devices/:id', element: <DeviceDetailPage /> },
          { path: '/device/register', element: <RegisterDevicePage /> },
          { path: '/clients', element: <ClientsPage /> },
          { path: '/clients/register', element: <ClientRegisterPage /> },
          { path: '/clients/:id', element: <ClientDetailPage /> },
          { path: '/contacts', element: <ContactsPage /> },
          { path: '/contacts/register', element: <ContactRegisterPage /> },
          { path: '/contacts/:id', element: <ContactDetailPage /> },
        ],
      },
    ],
  },
  {
    path: '*',
    element: <NotFoundPage />,
  },
]);
