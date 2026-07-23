import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

/** 担当者一覧の行（`GET /api/contacts` の `data` 要素）。 */
export interface Contact {
  id: number | string;
  client_id: string;
  company: string | null;
  name: string | null;
  tel: string | null;
  email: string | null;
  note: string | null;
  modified_at: string | null;
}

/** 担当者一覧を取得するクエリ（`word` で担当者名検索）。 */
export function useContacts(word: string) {
  return useQuery({
    queryKey: ['contacts', 'list', word],
    queryFn: async (): Promise<Contact[]> => {
      const res = await api.get<{ data: Contact[] }>('/contacts', {
        params: word ? { word } : undefined,
      });
      return res.data.data;
    },
  });
}
