/**
 * 認証トークンの永続化ヘルパ（localStorage）。
 * 認証コンテキスト（2-3）と Axios クライアント（2-2）から共有する。
 */
const TOKEN_KEY = 'osm_token';

export function getToken(): string | null {
  try {
    return localStorage.getItem(TOKEN_KEY);
  } catch {
    // localStorage 非対応／プライベートモード等
    return null;
  }
}

export function setToken(token: string): void {
  try {
    localStorage.setItem(TOKEN_KEY, token);
  } catch {
    // 保存できない環境では握りつぶす（メモリ上のみで動作）
  }
}

export function clearToken(): void {
  try {
    localStorage.removeItem(TOKEN_KEY);
  } catch {
    // no-op
  }
}
