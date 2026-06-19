import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';

export interface FileInfo {
  filename: string;
  size: number;
  updated_at: string;
}

export function useDeviceBenchmarkFile() {
  return useQuery({
    queryKey: ['device-benchmark-file'],
    queryFn: async (): Promise<FileInfo | null> => {
      const res = await api.get<{ data: FileInfo | null }>('/devices/file/benchmark');
      return res.data.data;
    },
  });
}

export function useUploadDeviceBenchmarkFile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (file: File) => {
      const formData = new FormData();
      formData.append('benchmark_file', file);

      const response = await api.post<{ data: FileInfo }>(
        '/devices/file/benchmark',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['device-benchmark-file'] });
    },
  });
}
