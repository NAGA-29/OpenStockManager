import { useContext } from 'react';
import { AuthContext } from './context';
import type { AuthContextValue } from './types';

/** 認証コンテキストへアクセスするフック。`AuthProvider` 配下で使用する。 */
export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth は AuthProvider の内側で使用してください');
  }
  return ctx;
}
