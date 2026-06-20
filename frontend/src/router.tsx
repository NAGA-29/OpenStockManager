import { createBrowserRouter, Navigate } from 'react-router-dom';
import { ProtectedRoute } from './auth/ProtectedRoute';
import { AdminRoute } from './auth/AdminRoute';
import AppLayout from './layouts/AppLayout';
import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import InventoryStocksPage from './pages/InventoryStocksPage';
import InventoryUnitsPage from './pages/InventoryUnitsPage';
import DeviceDetailPage from './pages/DeviceDetailPage';
import DeviceBarcodePage from './pages/DeviceBarcodePage';
import DeviceSearchPage from './pages/DeviceSearchPage';
import RegisterDevicePage from './pages/RegisterDevicePage';
import DeviceRegisterMultiPage from './pages/DeviceRegisterMultiPage';
import DeviceSpecFilePage from './pages/DeviceSpecFilePage';
import DeviceBenchmarkFilePage from './pages/DeviceBenchmarkFilePage';
import ClientsPage from './pages/ClientsPage';
import ClientRegisterPage from './pages/ClientRegisterPage';
import ClientDetailPage from './pages/ClientDetailPage';
import ContactsPage from './pages/ContactsPage';
import ContactRegisterPage from './pages/ContactRegisterPage';
import ContactDetailPage from './pages/ContactDetailPage';
import RentalPage from './pages/RentalPage';
import RentalHistoryPage from './pages/RentalHistoryPage';
import RentalHistoryDetailPage from './pages/RentalHistoryDetailPage';
import SalePage from './pages/SalePage';
import SaleHistoryPage from './pages/SaleHistoryPage';
import SaleHistoryDetailPage from './pages/SaleHistoryDetailPage';
import HistoryPage from './pages/HistoryPage';
import UsersPage from './pages/UsersPage';
import UserRegisterPage from './pages/UserRegisterPage';
import DeviceCategoriesPage from './pages/DeviceCategoriesPage';
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
          { path: '/devices/search', element: <DeviceSearchPage /> },
          { path: '/devices/:id', element: <DeviceDetailPage /> },
          { path: '/devices/:id/barcode', element: <DeviceBarcodePage /> },
          { path: '/device/register', element: <RegisterDevicePage /> },
          { path: '/device/register/multi', element: <DeviceRegisterMultiPage /> },
          { path: '/device/file/spec', element: <DeviceSpecFilePage /> },
          { path: '/device/file/benchmark', element: <DeviceBenchmarkFilePage /> },
          { path: '/clients', element: <ClientsPage /> },
          { path: '/clients/register', element: <ClientRegisterPage /> },
          { path: '/clients/:id', element: <ClientDetailPage /> },
          { path: '/contacts', element: <ContactsPage /> },
          { path: '/contacts/register', element: <ContactRegisterPage /> },
          { path: '/contacts/:id', element: <ContactDetailPage /> },
          { path: '/rental', element: <RentalPage /> },
          { path: '/rental/history', element: <RentalHistoryPage /> },
          { path: '/rental/history/:lendId', element: <RentalHistoryDetailPage /> },
          { path: '/sale', element: <SalePage /> },
          { path: '/sale/history', element: <SaleHistoryPage /> },
          { path: '/sale/history/:saleId', element: <SaleHistoryDetailPage /> },
          { path: '/history', element: <HistoryPage /> },
          {
            element: <AdminRoute />,
            children: [
              { path: '/users', element: <UsersPage /> },
              { path: '/users/register', element: <UserRegisterPage /> },
              { path: '/settings/categories', element: <DeviceCategoriesPage /> },
            ],
          },
        ],
      },
    ],
  },
  {
    path: '*',
    element: <NotFoundPage />,
  },
]);
