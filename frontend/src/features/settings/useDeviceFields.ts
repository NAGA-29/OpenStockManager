import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';

export type FieldType = 'text' | 'number' | 'select' | 'boolean';

export interface FieldOption {
  label: string;
  value: string;
}

/** カスタムフィールドの 1 行（`GET /api/device-fields` の `data` 要素）。 */
export interface DeviceField {
  id: number;
  device_category_code: string;
  field_key: string;
  label: string;
  field_type: FieldType;
  options: FieldOption[] | null;
  is_required: boolean;
  sort_order: number;
}

export interface DeviceFieldsResponse {
  data: DeviceField[];
  field_types: Record<string, string>;
}

const KEY = ['device-fields'];

/** カスタムフィールド一覧（管理者のみ）。 */
export function useDeviceFields() {
  return useQuery({
    queryKey: KEY,
    queryFn: async () => {
      const res = await api.get<DeviceFieldsResponse>('/device-fields');
      return res.data;
    },
  });
}

export interface CreateFieldInput {
  device_category_code: string;
  label: string;
  field_type: FieldType;
  options?: FieldOption[];
  is_required?: boolean;
}

export function useCreateField() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: CreateFieldInput) => {
      const res = await api.post<{ data: DeviceField; message: string }>(
        '/device-fields',
        input,
      );
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}

export interface UpdateFieldInput {
  id: number;
  label: string;
  field_type: FieldType;
  options?: FieldOption[];
  is_required?: boolean;
}

export function useUpdateField() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async ({ id, ...body }: UpdateFieldInput) => {
      const res = await api.put<{ data: DeviceField; message: string }>(
        `/device-fields/${id}`,
        body,
      );
      return res.data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}

export function useDeleteField() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await api.delete<{ message: string }>(`/device-fields/${id}`);
      return res.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}

export function useReorderFields() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (order: number[]) => {
      const res = await api.post<{ message: string }>('/device-fields/reorder', {
        order,
      });
      return res.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  });
}
