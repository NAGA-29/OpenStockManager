/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** Laravel API のベース URL（docker-compose 既定: http://localhost） */
  readonly VITE_API_BASE_URL: string;
  /** Vite dev server の /api proxy 転送先 */
  readonly VITE_DEV_API_PROXY_TARGET: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
