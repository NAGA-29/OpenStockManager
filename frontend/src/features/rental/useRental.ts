import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';

export interface RentalDevice {
  device_id: string;
  device_name: string;
  device_type: string;
  device_serial: string;
  condition: string | null;
  pivot: {
    checkout_at: string;
    return_at: string | null;
  };
}

export interface RentalHist {
  lend_id: string;
  client: string;
  contact: number;
  staff: number;
  all_returned: number;
  checkout_at: string;
  schedule_return_at: string;
  return_at: string | null;
  note: string | null;
  created_at: string;
  modified_at: string;
  soft_deleted_at: string | null;
  clients?: {
    client_id: string;
    company: string;
  };
  contacts?: {
    id: number;
    name: string;
    email: string;
  };
  user?: {
    id: number;
    name: string;
  };
  devices?: RentalDevice[];
}

export interface RentalListResponse {
  data: RentalHist[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export function useRentals(page = 1, word = '') {
  return useQuery({
    queryKey: ['rentals', page, word],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (word) params.append('word', word);
      if (page > 1) params.append('page', String(page));

      const response = await api.get<RentalListResponse>(
        `/rental?${params.toString()}`,
      );
      return response.data;
    },
  });
}

export function useRentalHistory(page = 1, word = '') {
  return useQuery({
    queryKey: ['rental-history', page, word],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (word) params.append('word', word);
      if (page > 1) params.append('page', String(page));

      const response = await api.get<RentalListResponse>(
        `/rental/history?${params.toString()}`,
      );
      return response.data;
    },
  });
}

export function useRentalDetail(lendId: string) {
  return useQuery({
    queryKey: ['rental-detail', lendId],
    queryFn: async () => {
      const response = await api.get<{ data: RentalHist }>(
        `/rental/history/${lendId}`,
      );
      return response.data.data;
    },
    enabled: !!lendId,
  });
}

export interface StoreRentalInput {
  device_ids: string[];
  client_id: string;
  contact_id: number;
  checkout_at: string;
  schedule_return_at: string;
  note?: string;
}

export function useStoreRental() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: StoreRentalInput) => {
      const response = await api.post<{ data: RentalHist; message: string }>(
        '/rental/store',
        input,
      );
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rentals'] });
      queryClient.invalidateQueries({ queryKey: ['rental-history'] });
    },
  });
}

export interface RentalPreview {
  device_id: string;
  device_name: string;
  device_type: string;
  device_serial: string;
  condition: string | null;
}

export function useUploadRentalMulti() {
  return useMutation({
    mutationFn: async (file: File) => {
      const formData = new FormData();
      formData.append('rental_file', file);

      const response = await api.post<{ data: RentalPreview[]; count: number }>(
        '/rental/multi/upload',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        },
      );
      return response.data;
    },
  });
}

export interface StoreRentalMultiInput {
  client_id: string;
  contact_id: number;
  checkout_at: string;
  schedule_return_at: string;
  rentals: Array<{ device_id: string }>;
  note?: string;
}

export function useStoreRentalMulti() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: StoreRentalMultiInput) => {
      const response = await api.post<{
        data: RentalHist;
        count: number;
        message: string;
      }>('/rental/multi/store', input);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rentals'] });
      queryClient.invalidateQueries({ queryKey: ['rental-history'] });
    },
  });
}

export function useReturnDevice() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: { lendId: string; device_id: string; return_at: string }) => {
      const response = await api.post<{ data: RentalHist; message: string }>(
        `/rental/multi/return/${input.lendId}`,
        { device_id: input.device_id, return_at: input.return_at },
      );
      return response.data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['rental-detail', variables.lendId] });
      queryClient.invalidateQueries({ queryKey: ['rentals'] });
    },
  });
}
