import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react';
import api from '@/lib/api';
import { clearToken, getToken, setToken } from '@/lib/token';
import { AuthContext } from './context';
import type { AuthContextValue, AuthUser } from './types';

/**
 * 認証状態の供給元。
 * - 起動時にトークンがあれば `GET /api/auth/me` でユーザーを復元（失敗時は破棄）
 * - `login` は `POST /api/auth/login` でトークン発行→保存→ユーザー設定
 * - `logout` は `POST /api/auth/logout`→トークン破棄
 */
export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    const token = getToken();
    if (!token) {
      setIsLoading(false);
      return;
    }

    let active = true;
    api
      .get<{ user: AuthUser }>('/auth/me')
      .then((res) => {
        if (active) {
          setUser(res.data.user);
        }
      })
      .catch(() => {
        clearToken();
        if (active) {
          setUser(null);
        }
      })
      .finally(() => {
        if (active) {
          setIsLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const res = await api.post<{ token: string; user: AuthUser }>('/auth/login', {
      email,
      password,
    });
    setToken(res.data.token);
    setUser(res.data.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout');
    } finally {
      clearToken();
      setUser(null);
    }
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isAuthenticated: user !== null,
      isLoading,
      login,
      logout,
    }),
    [user, isLoading, login, logout],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
