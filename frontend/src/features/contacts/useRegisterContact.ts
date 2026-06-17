import { useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../../lib/api';

export interface RegisterContactInput {
  client_id: string;
  name: string;
  email: string;
  tel: string;
  note?: string;
}

export interface RegisterContactResponse {
  data: {
    id: number;
    client_id: string;
    company: string;
    name: string;
    tel: string;
    email: string;
    note: string | null;
    modified_at: string;
  };
}

export function useRegisterContact() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: RegisterContactInput) => {
      const response = await api.post<RegisterContactResponse>(
        '/contacts',
        input
      );
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['contacts'] });
    },
  });
}
