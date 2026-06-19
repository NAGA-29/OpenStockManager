import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

export type HistoryType = 'rental' | 'sale';
export type HistoryFilter = 'all' | HistoryType;

/** レンタル/販売を統合した履歴の 1 行。 */
export interface HistoryItem {
  id: string;
  type: HistoryType;
  company: string | null;
  contact: string | null;
  date: string | null;
  /** rental: lending|returned / sale: sold */
  status: 'lending' | 'returned' | 'sold';
  note: string | null;
}

export interface HistoryListResponse {
  data: HistoryItem[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

/**
 * 統合履歴クエリ（`GET /api/history`）。
 * `type` で種別（all/rental/sale）、`word` でキーワード、`page` でページ送り。
 */
export function useHistory(page = 1, word = '', type: HistoryFilter = 'all') {
  return useQuery({
    queryKey: ['history', page, word, type],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (type !== 'all') params.append('type', type);
      if (word) params.append('word', word);
      if (page > 1) params.append('page', String(page));

      const response = await api.get<HistoryListResponse>(
        `/history?${params.toString()}`,
      );
      return response.data;
    },
  });
}
