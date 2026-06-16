import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

/** 数量管理の在庫行（`GET /api/inventory/stocks` のレスポンス要素）。 */
export interface InventoryStock {
  id: number;
  location: string;
  item_name: string | null;
  quantity: number;
  min_stock: number;
  /** 最低在庫を下回っているか。 */
  below_min: boolean;
}

/** 数量管理（ロケーション×品目）の在庫一覧を取得するクエリ。 */
export function useStocks() {
  return useQuery({
    queryKey: ['inventory', 'stocks'],
    queryFn: async (): Promise<InventoryStock[]> => {
      const res = await api.get<{ data: InventoryStock[] }>('/inventory/stocks');
      return res.data.data;
    },
  });
}
