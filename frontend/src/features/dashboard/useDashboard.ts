import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

/** 延滞／期限間近のレンタル行（`GET /api/dashboard` のレスポンス要素）。 */
export interface DashboardRental {
  lend_id: number;
  company: string | null;
  staff: string | null;
  schedule_return_at: string | null;
  device_count: number;
  /** 期限間近のみ（本日=0）。 */
  remaining_days?: number;
  /** 延滞のみ。 */
  overdue_days?: number;
}

/** `GET /api/dashboard` のレスポンス（フラットなキー構成）。 */
export interface DashboardData {
  lending_count: number;
  near_deadline: DashboardRental[];
  overdue: DashboardRental[];
}

/** ダッシュボードの集計データを取得するクエリ。 */
export function useDashboard() {
  return useQuery({
    queryKey: ['dashboard'],
    queryFn: async (): Promise<DashboardData> => {
      const res = await api.get<DashboardData>('/dashboard');
      return res.data;
    },
  });
}
