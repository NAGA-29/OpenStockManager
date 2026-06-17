import { useMutation } from '@tanstack/react-query';
import api from '@/lib/api';
import type { Client } from './useClients';

/** クライアント登録のリクエストペイロード。 */
export interface RegisterClientPayload {
  company: string;
  url: string;
  tel: string;
  street_address: string;
  note?: string | null;
}

/** クライアント企業を登録する mutation（`POST /api/clients`）。 */
export function useRegisterClient() {
  return useMutation({
    mutationFn: async (payload: RegisterClientPayload): Promise<Client> => {
      const res = await api.post<{ data: Client }>('/clients', payload);
      return res.data.data;
    },
  });
}
