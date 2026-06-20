import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';

/** 機材カテゴリの 1 行（`GET /api/device-categories` の `data` 要素）。 */
export interface DeviceCategory {
  id: number;
  code: string;
  name: string;
  icon: string;
  sort_order: number;
  is_active: boolean;
  device_count: number;
}

const KEY = ['device-categories'];

/** カテゴリ一覧（管理者のみ）。 */
export function useDeviceCategories() {
  return useQuery({
    queryKey: KEY,
    queryFn: async () => {
      const res = await api.get<{ data: DeviceCategory[] }>('/device-categories');
      return res.data.data;
    },
  });
}

export interface CreateCategoryInput {
  code: string;
  name: string;
  icon?: string;
}

export function useCreateCategory() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: CreateCategoryInput) => {
      const res = await api.post<{ data: DeviceCategory; message: string }>(
        '/device-categories',
        input,
      );
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}

export interface UpdateCategoryInput {
  id: number;
  code: string;
  name: string;
  icon?: string;
  is_active: boolean;
}

export function useUpdateCategory() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, ...body }: UpdateCategoryInput) => {
      const res = await api.put<{ data: DeviceCategory; message: string }>(
        `/device-categories/${id}`,
        body,
      );
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}

export function useDeleteCategory() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await api.delete<{ message: string }>(`/device-categories/${id}`);
      return res.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}

export function useReorderCategories() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (order: number[]) => {
      const res = await api.post<{ message: string }>('/device-categories/reorder', {
        order,
      });
      return res.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}
