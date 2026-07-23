import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

/** クライアント企業一覧の行（`GET /api/clients` の `data` 要素）。 */
export interface Client {
  client_id: string;
  company: string | null;
  url: string | null;
  tel: string | null;
  post_code: string | null;
  street_address: string | null;
  note: string | null;
  modified_at: string | null;
}

/** クライアント一覧を取得するクエリ（`word` で会社名検索）。 */
export function useClients(word: string) {
  return useQuery({
    queryKey: ['clients', 'list', word],
    queryFn: async (): Promise<Client[]> => {
      const res = await api.get<{ data: Client[] }>('/clients', {
        params: word ? { word } : undefined,
      });
      return res.data.data;
    },
  });
}
