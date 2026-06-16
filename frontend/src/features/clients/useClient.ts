import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import type { Client } from './useClients';

/** クライアントに紐づく担当者。 */
export interface ClientContact {
  id: number | string;
  name: string | null;
  tel: string | null;
  email: string | null;
  note: string | null;
  modified_at: string | null;
}

/** クライアント詳細（担当者一覧込み）。 */
export interface ClientDetail extends Client {
  contacts: ClientContact[];
}

/** クライアント詳細を取得するクエリ。 */
export function useClient(clientId: string) {
  return useQuery({
    queryKey: ['clients', 'detail', clientId],
    queryFn: async (): Promise<ClientDetail> => {
      const res = await api.get<{ data: ClientDetail }>(
        `/clients/${encodeURIComponent(clientId)}`,
      );
      return res.data.data;
    },
  });
}
