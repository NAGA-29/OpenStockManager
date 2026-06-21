import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';

export type UserRole = 'admin' | 'user';

/** ユーザー一覧の 1 行（`GET /api/users` の `data` 要素）。 */
export interface ManagedUser {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  is_admin: boolean;
  created_at: string | null;
}

export interface UserListResponse {
  data: ManagedUser[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

/** ユーザー一覧（管理者のみ）。`word` で名前/メール検索。 */
export function useUsers(page = 1, word = '') {
  return useQuery({
    queryKey: ['users', page, word],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (word) params.append('word', word);
      if (page > 1) params.append('page', String(page));

      const response = await api.get<UserListResponse>(
        `/users?${params.toString()}`,
      );
      return response.data;
    },
  });
}

export interface CreateUserInput {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: UserRole;
}

export function useCreateUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: CreateUserInput) => {
      const response = await api.post<{ data: ManagedUser; message: string }>(
        '/users',
        input,
      );
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
  });
}

export interface UpdateUserInput {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  password?: string;
  password_confirmation?: string;
}

export function useUpdateUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, ...body }: UpdateUserInput) => {
      const response = await api.put<{ data: ManagedUser; message: string }>(
        `/users/${id}`,
        body,
      );
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
  });
}
