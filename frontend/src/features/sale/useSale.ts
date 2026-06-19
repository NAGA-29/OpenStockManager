import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';

export interface SaleDevice {
  device_id: string;
  device_name: string;
  device_type: string;
  device_serial: string;
  condition: string | null;
  pivot: {
    sale_date_at: string;
  };
}

export interface SaleHist {
  sale_id: string;
  client: string;
  contact: number;
  staff: number;
  sale_date_at: string;
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
  devices?: SaleDevice[];
}

export interface SaleListResponse {
  data: SaleHist[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export function useSales(page = 1, word = '') {
  return useQuery({
    queryKey: ['sales', page, word],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (word) params.append('word', word);
      if (page > 1) params.append('page', String(page));

      const response = await api.get<SaleListResponse>(
        `/sale?${params.toString()}`,
      );
      return response.data;
    },
  });
}

export function useSaleHistory(page = 1, word = '') {
  return useQuery({
    queryKey: ['sale-history', page, word],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (word) params.append('word', word);
      if (page > 1) params.append('page', String(page));

      const response = await api.get<SaleListResponse>(
        `/sale/history?${params.toString()}`,
      );
      return response.data;
    },
  });
}

export function useSaleDetail(saleId: string) {
  return useQuery({
    queryKey: ['sale-detail', saleId],
    queryFn: async () => {
      const response = await api.get<{ data: SaleHist }>(
        `/sale/history/${saleId}`,
      );
      return response.data.data;
    },
    enabled: !!saleId,
  });
}

export interface StoreSaleInput {
  device_ids: string[];
  client_id: string;
  contact_id: number;
  sale_date_at: string;
  note?: string;
}

export function useStoreSale() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: StoreSaleInput) => {
      const response = await api.post<{ data: SaleHist; message: string }>(
        '/sale/store',
        input,
      );
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sales'] });
      queryClient.invalidateQueries({ queryKey: ['sale-history'] });
    },
  });
}

export interface SalePreview {
  device_id: string;
  device_name: string;
  device_type: string;
  device_serial: string;
  condition: string | null;
}

export function useUploadSaleMulti() {
  return useMutation({
    mutationFn: async (file: File) => {
      const formData = new FormData();
      formData.append('sale_file', file);

      const response = await api.post<{ data: SalePreview[]; count: number }>(
        '/sale/multi/upload',
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

export interface StoreSaleMultiInput {
  client_id: string;
  contact_id: number;
  sale_date_at: string;
  sales: Array<{ device_id: string }>;
  note?: string;
}

export function useStoreSaleMulti() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: StoreSaleMultiInput) => {
      const response = await api.post<{
        data: SaleHist;
        count: number;
        message: string;
      }>('/sale/multi/store', input);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sales'] });
      queryClient.invalidateQueries({ queryKey: ['sale-history'] });
    },
  });
}
