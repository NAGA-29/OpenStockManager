/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** Laravel API のベース URL（docker-compose 既定: http://localhost） */
  readonly VITE_API_BASE_URL: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
