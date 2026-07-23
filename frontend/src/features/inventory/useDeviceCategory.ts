import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

/** カテゴリタブ用の最小カテゴリ情報。 */
export interface DeviceCategoryTab {
  code: string;
  name: string;
  icon: string | null;
}

/** 個別管理の端末一覧行（`GET /api/devices/category/:code` の `data` 要素）。 */
export interface CategoryDevice {
  device_id: string;
  device_type: string;
  device_name: string | null;
  device_serial: string | null;
  /** 空文字でなければ貸出中。 */
  lending_now: string | null;
  /** 空文字でなければ販売済。 */
  sale_id: string | null;
  defective: boolean;
  not_for_sale: boolean;
  note: string | null;
  condition: string | null;
  has_images: boolean;
}

/** カテゴリ別の端末件数サマリー。 */
export interface CategoryCounts {
  all: number;
  lending: number;
  defective: number;
}

interface DeviceCategoryResponse {
  categories: DeviceCategoryTab[];
  current: DeviceCategoryTab;
  counts: CategoryCounts;
  category: string;
  data: CategoryDevice[];
}

/** カテゴリコードごとの端末一覧（個別管理）を取得するクエリ。 */
export function useDeviceCategory(code: string) {
  return useQuery({
    queryKey: ['devices', 'category', code],
    queryFn: async (): Promise<DeviceCategoryResponse> => {
      const res = await api.get<DeviceCategoryResponse>(
        `/devices/category/${encodeURIComponent(code)}`,
      );
      return res.data;
    },
  });
}
