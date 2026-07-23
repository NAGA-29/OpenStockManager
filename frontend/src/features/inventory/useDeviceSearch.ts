import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import type { CategoryDevice } from './useDeviceCategory';

/** 検索結果のページネーション・メタ情報。 */
export interface DeviceSearchMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  keywords: string;
}

interface DeviceSearchResponse {
  data: CategoryDevice[];
  meta: DeviceSearchMeta;
}

/**
 * 端末検索クエリ（`GET /api/devices/search`）。
 * `word` が空のときは検索を実行しない（`enabled:false`）。
 * `hiddenType` で device_type を絞り込み、`page` でページ送り。
 */
export function useDeviceSearch(word: string, hiddenType: string, page: number) {
  return useQuery({
    queryKey: ['devices', 'search', word, hiddenType, page],
    enabled: word.trim() !== '',
    queryFn: async (): Promise<DeviceSearchResponse> => {
      const params: Record<string, string | number> = { word, page };
      if (hiddenType) {
        params.hiddenType = hiddenType;
      }
      const res = await api.get<DeviceSearchResponse>('/devices/search', {
        params,
      });
      return res.data;
    },
  });
}
