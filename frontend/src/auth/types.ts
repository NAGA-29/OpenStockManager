/** 認証済みユーザー（API `auth.userResource` のレスポンスに対応）。 */
export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: number | string;
  is_admin: boolean;
}

export interface AuthContextValue {
  user: AuthUser | null;
  isAuthenticated: boolean;
  /** 起動時のトークン復元中など、認証状態が未確定の間 true。 */
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}
