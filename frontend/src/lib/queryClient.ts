import { QueryClient } from '@tanstack/react-query';

/**
 * アプリ共通の TanStack Query クライアント。
 * 401 は `lib/api.ts` のインターセプタで処理するため retry は控えめにする。
 */
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 30_000,
      refetchOnWindowFocus: false,
    },
  },
});
