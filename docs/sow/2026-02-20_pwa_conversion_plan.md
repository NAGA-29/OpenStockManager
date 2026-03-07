# PWA化 実装計画書

**日付:** 2026-02-20
**対象プロジェクト:** OpenStockManager
**ブランチ:** `claude/pwa-conversion-plan-ftGW5`

---

## 1. 概要

OpenStockManager（Laravel + Blade MPA）をPWA（Progressive Web App）化する。
主な目的は以下の通り：

- モバイル端末からのホーム画面追加（インストール体験）
- オフライン時のフォールバック表示
- 静的アセットのキャッシュによるパフォーマンス向上
- ネイティブアプリに近いUX（全画面表示、スプラッシュスクリーン）

---

## 2. 現状分析

### 技術スタック

| 項目 | 内容 |
|------|------|
| バックエンド | Laravel (PHP) |
| テンプレート | Blade (MPA / サーバーサイドレンダリング) |
| ビルドツール | Vite + `laravel-vite-plugin` |
| CSSフレームワーク | Bootstrap 5 + Tailwind CSS |
| 言語 | TypeScript / JavaScript |
| 認証 | Laravel Auth（全ページ認証必須） |

### PWA対応に関する現状

| 項目 | 現状 |
|------|------|
| `manifest.json` | **なし** |
| Service Worker | **なし** |
| アプリアイコン（192px, 512px） | **なし**（`favicon.png`のみ）|
| `<meta name="theme-color">` | **なし** |
| HTTPS | 本番環境での確認が必要 |
| オフラインページ | **なし** |

---

## 3. PWA要件定義

### 3.1 必須要件（Installable条件）

PWAとしてインストール可能にするために以下が必須：

1. **`manifest.json`の配置** (`public/manifest.json`)
2. **Service Workerの登録**
3. **HTTPSでの配信**（本番環境）
4. **192px・512pxのアイコン**

### 3.2 機能要件

| 機能 | 優先度 | 内容 |
|------|--------|------|
| ホーム画面追加 | 必須 | manifest + Service Worker |
| オフラインフォールバック | 必須 | オフライン時に専用ページを表示 |
| 静的アセットキャッシュ | 必須 | CSS/JS/画像をCache-firstでキャッシュ |
| ネットワーク優先戦略 | 必須 | HTMLページはNetwork-first |
| プッシュ通知 | 対象外 | 今回のスコープ外 |
| バックグラウンドSync | 対象外 | 今回のスコープ外 |

---

## 4. キャッシュ戦略

MPAかつ認証必須のアプリケーションのため、以下の戦略を採用する。

### 4.1 Cache-First（キャッシュ優先）

対象：静的アセット（バージョニングされたViteビルド成果物）

```
/build/assets/*.css
/build/assets/*.js
/images/**
/fonts/**（将来的な利用を想定）
```

**理由:** Viteがビルド時にコンテンツハッシュをファイル名に付与するため、キャッシュの一貫性が保証される。

### 4.2 Network-First（ネットワーク優先）

対象：HTMLページ、動的コンテンツ

```
/dashboard
/device/**
/rental/**
/sales/**
/clients/**
/personnel/**
/users/**
/profile
```

**理由:** サーバーサイドレンダリングのため、常に最新データを取得する必要がある。ネットワーク失敗時はオフラインページにフォールバック。

### 4.3 キャッシュしない対象

- POSTリクエスト（CSRF保護のため）
- `/login`, `/logout`（認証エンドポイント）
- 管理系APIエンドポイント

---

## 5. 実装内容

### 5.1 ディレクトリ構成（追加・変更ファイル）

```
OpenStockManager/
├── public/
│   ├── manifest.json                 # 【新規】Web App Manifest
│   ├── service-worker.js             # 【新規】Service Worker
│   ├── offline.html                  # 【新規】オフラインフォールバックページ
│   └── images/
│       ├── icon-72x72.png            # 【新規】PWAアイコン各サイズ
│       ├── icon-96x96.png
│       ├── icon-128x128.png
│       ├── icon-144x144.png
│       ├── icon-152x152.png
│       ├── icon-192x192.png
│       ├── icon-384x384.png
│       └── icon-512x512.png
├── resources/
│   ├── js/
│   │   └── pwa/
│   │       └── register-sw.ts        # 【新規】SW登録スクリプト
│   └── views/
│       └── layouts/
│           └── app.blade.php         # 【変更】manifest/meta tag追加
└── vite.config.js                    # 【変更】register-sw.tsをinputに追加
```

### 5.2 manifest.json

```json
{
  "name": "OpenStockManager",
  "short_name": "DeviceMgr",
  "description": "機材管理システム",
  "start_url": "/dashboard",
  "display": "standalone",
  "background_color": "#1a1a2e",
  "theme_color": "#212529",
  "orientation": "portrait-primary",
  "lang": "ja",
  "icons": [
    { "src": "/images/icon-72x72.png",   "sizes": "72x72",   "type": "image/png" },
    { "src": "/images/icon-96x96.png",   "sizes": "96x96",   "type": "image/png" },
    { "src": "/images/icon-128x128.png", "sizes": "128x128", "type": "image/png" },
    { "src": "/images/icon-144x144.png", "sizes": "144x144", "type": "image/png" },
    { "src": "/images/icon-152x152.png", "sizes": "152x152", "type": "image/png" },
    { "src": "/images/icon-192x192.png", "sizes": "192x192", "type": "image/png", "purpose": "any maskable" },
    { "src": "/images/icon-384x384.png", "sizes": "384x384", "type": "image/png" },
    { "src": "/images/icon-512x512.png", "sizes": "512x512", "type": "image/png", "purpose": "any maskable" }
  ]
}
```

### 5.3 Service Worker (`public/service-worker.js`)

```javascript
const CACHE_VERSION = 'v1';
const STATIC_CACHE  = `dm-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `dm-dynamic-${CACHE_VERSION}`;
const OFFLINE_URL   = '/offline.html';

// プリキャッシュ対象（ビルド時に生成される静的ファイル）
const PRECACHE_URLS = [
  OFFLINE_URL,
  '/images/favicon.png',
];

// インストール: オフラインページをプリキャッシュ
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
  );
});

// アクティベート: 古いキャッシュを削除
self.addEventListener('activate', (event) => {
  const keepCaches = [STATIC_CACHE, DYNAMIC_CACHE];
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(
        keys
          .filter(key => !keepCaches.includes(key))
          .map(key => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

// フェッチ戦略
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // POST / 認証系はキャッシュしない
  if (request.method !== 'GET') return;
  if (['/login', '/logout'].includes(url.pathname)) return;

  // 静的アセット（Viteビルド成果物）: Cache-First
  if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/')) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // HTMLページ: Network-First
  if (request.headers.get('Accept')?.includes('text/html')) {
    event.respondWith(networkFirst(request));
    return;
  }
});

// Cache-First戦略
async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  const response = await fetch(request);
  if (response.ok) {
    const cache = await caches.open(STATIC_CACHE);
    cache.put(request, response.clone());
  }
  return response;
}

// Network-First戦略（失敗時はオフラインページ）
async function networkFirst(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await caches.match(request);
    if (cached) return cached;
    return caches.match(OFFLINE_URL);
  }
}
```

### 5.4 Service Worker 登録スクリプト (`resources/js/pwa/register-sw.ts`)

```typescript
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker
      .register('/service-worker.js', { scope: '/' })
      .then(reg => console.log('[SW] 登録完了:', reg.scope))
      .catch(err => console.error('[SW] 登録失敗:', err));
  });
}
```

### 5.5 Bladeレイアウト変更 (`resources/views/layouts/app.blade.php`)

`<head>` セクションに以下を追加：

```html
{{-- PWA --}}
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#212529">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="DeviceMgr">
<link rel="apple-touch-icon" href="{{ asset('/images/icon-192x192.png') }}">
```

### 5.6 vite.config.js変更

`input` 配列に `'resources/js/pwa/register-sw.ts'` を追加し、
`register-sw.ts` を Vite のエントリーポイントとして管理する。

また、`app.blade.php` の `@vite(...)` 呼び出しに `register-sw.ts` を追加する。

### 5.7 オフラインページ (`public/offline.html`)

ネットワーク接続がない場合に表示するスタンドアロンのHTMLページ。
OpenStockManagerのブランドカラー（ダークテーマ）に合わせたデザインとし、
以下の情報を表示する：

- オフライン状態の説明
- 再試行ボタン（`location.reload()`）
- ネットワーク回復時の自動リロード（`online` イベントリスナー）

---

## 6. アイコン生成方針

現在 `public/images/favicon.png` が存在するため、これをベースに各サイズを生成する。

### 生成方法の選択肢

| 方法 | ツール | 備考 |
|------|--------|------|
| **コマンドライン（推奨）** | `ImageMagick` (`convert`) | サーバー上で自動化可能 |
| オンラインツール | realfavicongenerator.net | GUIで簡単 |
| Node.jsスクリプト | `sharp` パッケージ | ビルドパイプラインに統合可能 |

### ImageMagickによる生成コマンド例

```bash
for size in 72 96 128 144 152 192 384 512; do
  convert public/images/favicon.png -resize ${size}x${size} \
    public/images/icon-${size}x${size}.png
done
```

---

## 7. 実装タスク一覧

| # | タスク | ファイル | 優先度 |
|---|--------|---------|--------|
| 1 | PWAアイコン生成（8サイズ） | `public/images/icon-*.png` | 高 |
| 2 | `manifest.json` 作成 | `public/manifest.json` | 高 |
| 3 | Service Worker作成 | `public/service-worker.js` | 高 |
| 4 | オフラインページ作成 | `public/offline.html` | 高 |
| 5 | SW登録スクリプト作成 | `resources/js/pwa/register-sw.ts` | 高 |
| 6 | Bladeレイアウト変更 | `resources/views/layouts/app.blade.php` | 高 |
| 7 | Vite設定変更 | `vite.config.js` | 高 |
| 8 | 動作確認（Lighthouse監査） | - | 中 |
| 9 | 本番HTTPS確認 | - | 中 |

---

## 8. 注意事項・制約

### 8.1 CSRF保護との共存

LaravelのCSRFトークンはセッションベースのため、
**POSTリクエストはService Workerでキャッシュしない**。
フォーム送信・更新系APIは必ずネットワーク経由とする。

### 8.2 認証ページのキャッシュ

ログインページ（`/login`）はキャッシュ対象外とする。
認証済みHTMLページもdynamic cacheには保存するが、
**個人情報や機密データを含むページの長期キャッシュは行わない**。

### 8.3 Viteアセットのキャッシュバスティング

Viteはビルドごとにファイル名にハッシュを付与するため、
`/build/assets/` 配下のファイルはCache-Firstで安全にキャッシュできる。
Service Workerのバージョン（`CACHE_VERSION`）を更新する際は、
古いキャッシュが `activate` イベントで自動削除される。

### 8.4 Service Workerの更新フロー

1. `service-worker.js` を変更・デプロイ
2. ブラウザが新しいSWを検出し、バックグラウンドでインストール
3. 全タブを閉じると新SWがアクティブ化
4. 必要に応じて「更新が利用可能です」のUIを実装可能

---

## 9. Lighthouse PWAスコア目標

実装完了後に Chrome DevTools の Lighthouse で以下を達成することを目標とする：

| 項目 | 目標 |
|------|------|
| PWA Installable | ✅ |
| Has a `<meta name="viewport">` | ✅ |
| Redirects HTTP to HTTPS | ✅（本番） |
| Service Worker登録 | ✅ |
| オフライン対応 | ✅ |
| `manifest.json` 有効 | ✅ |

---

## 10. 参考

- [MDN: Progressive Web Apps](https://developer.mozilla.org/ja/docs/Web/Progressive_web_apps)
- [web.dev: PWA checklist](https://web.dev/pwa-checklist/)
- [Vite PWA Plugin (vite-plugin-pwa)](https://vite-pwa-org.netlify.app/) ※将来的な移行先候補
