import axios, { AxiosError, type InternalAxiosRequestConfig } from 'axios';
import { clearToken, getToken } from './token';

/**
 * Laravel JSON API 向けの共有 Axios クライアント。
 *
 * - baseURL: `VITE_API_BASE_URL + /api`。未指定時は Vite dev proxy の `/api` を使う。
 * - リクエスト時に Bearer トークンを自動付与
 * - 401 応答時はトークンを破棄しログインへ誘導（トークン方式）
 */
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') ?? '';
const baseURL = `${apiBaseUrl}/api`;

export const api = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// リクエスト: トークンを Authorization ヘッダに注入
api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

/** ログイン画面のパス（401 リダイレクト先）。2-4 の Router 設定と一致させる。 */
const LOGIN_PATH = '/login';

/** 認証系エンドポイント（ここでの 401 はリダイレクトでなく呼び出し側で処理させる）。 */
function isAuthEndpoint(url: string | undefined): boolean {
  if (!url) {
    return false;
  }
  return url.includes('/auth/login') || url.includes('/auth/me');
}

/** サービス停止（503）時の誘導先。 */
const SERVICE_UNAVAILABLE_PATH = '/error/503';

// レスポンス: 401 はトークン破棄＋ログインへ誘導、503 はメンテ画面へ誘導
api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    const status = error.response?.status;

    if (status === 401 && !isAuthEndpoint(error.config?.url)) {
      clearToken();
      if (
        typeof window !== 'undefined' &&
        window.location.pathname !== LOGIN_PATH
      ) {
        window.location.assign(LOGIN_PATH);
      }
    }

    if (
      status === 503 &&
      typeof window !== 'undefined' &&
      window.location.pathname !== SERVICE_UNAVAILABLE_PATH
    ) {
      window.location.assign(SERVICE_UNAVAILABLE_PATH);
    }

    return Promise.reject(error);
  },
);

export default api;
