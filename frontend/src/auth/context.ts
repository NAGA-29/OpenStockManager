import { createContext } from 'react';
import type { AuthContextValue } from './types';

/**
 * 認証コンテキスト本体。Provider は `AuthProvider`、参照は `useAuth` を使う。
 * （react-refresh 対策でコンポーネント／フックとはファイルを分離）
 */
export const AuthContext = createContext<AuthContextValue | null>(null);
