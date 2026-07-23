import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import type { Contact } from './useContacts';

/** 担当者詳細を取得するクエリ。 */
export function useContact(contactId: string) {
  return useQuery({
    queryKey: ['contacts', 'detail', contactId],
    queryFn: async (): Promise<Contact> => {
      const res = await api.get<{ data: Contact }>(
        `/contacts/${encodeURIComponent(contactId)}`,
      );
      return res.data.data;
    },
  });
}
