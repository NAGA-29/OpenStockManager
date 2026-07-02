# ADR-0003: フロントエンドの pnpm 移行と minimumReleaseAge によるリリース待機期間の導入

- **ステータス**: 承認済み
- **決定日**: 2026-07-02
- **決定者**: 開発チーム
- **関連**: [ADR-0001](0001-pnpm-security-configuration.md)（本 ADR により置き換え）

---

## コンテキスト

`frontend/`（Vite + React SPA）のパッケージ管理は npm（`package-lock.json` + `npm ci`）で運用していたが、以下の理由で pnpm へ移行し、あわせてサプライチェーン対策を再整備することにした。

- **pnpm への統一**: ディスク効率（コンテンツアドレス可能なストア）と厳格な依存解決を得たい。リポジトリには既に `.pnpm-store` が存在し、pnpm 利用の意図があった。
- **公開直後パッケージのリスク**: パッケージハイジャック等の悪意あるバージョンは、公開から短期間でセキュリティ研究者・監査ツールに検出されることが多い。**公開直後のバージョンをインストールしない**ことが有効な対策となる。

[ADR-0001](0001-pnpm-security-configuration.md) では同種の対策を自作 `.pnpmfile.cjs`（`afterAllResolved` フックで npm レジストリに公開日時を問い合わせ、7日未満をブロック）で実現する設計だった。しかし、

- pnpm 10.16 で同等機能が **`minimumReleaseAge` 設定として標準搭載**された。自作フックは不要になった。
- モノレポ再構成（Phase 4）で JS 依存は `frontend/` 配下に集約され、ADR-0001 が前提とした repo ルート直下の `package.json` / `.npmrc` / `.pnpmfile.cjs` は現存しない。

このため ADR-0001 の方式は破棄し、pnpm ネイティブ設定で再構築する。

---

## 決定

### 1. npm → pnpm 移行

- パッケージマネージャを pnpm 10.17.0 に固定（`frontend/package.json` の `packageManager` フィールド、corepack 前提）。
- `package-lock.json` を `pnpm import` で `pnpm-lock.yaml` に変換し、`package-lock.json` は削除。
- CI（`.github/workflows/frontend_ci.yml`・`code_security.yml`）を `pnpm/action-setup@v4` + `pnpm install --frozen-lockfile` に更新。監査は `pnpm audit --prod`（本番依存のみ）。
- `docker-compose.yml` の frontend サービス起動コマンドを `corepack enable && pnpm install && pnpm run dev -- --host` に変更。

### 2. `minimumReleaseAge` によるリリース待機期間（2 週間）

`frontend/pnpm-workspace.yaml` に設定する。

```yaml
minimumReleaseAge: 20160 # 分単位。2週間 = 14 * 24 * 60
# minimumReleaseAgeExclude:  # 緊急ホットフィックス時のバイパス用（例）
#   - some-package
#   - some-package@1.2.3
```

- **待機期間は 2 週間（20160 分）**。ADR-0001 の 7 日から延長した。
- `minimumReleaseAge` を明示設定したことで `minimumReleaseAgeStrict` が自動的に有効化され、範囲内に条件を満たすバージョンが無い場合は**インストールを中止**する（フォールバックしない）。
- **適用範囲**: 新規追加・バージョン更新時の解決にのみ効く。既存の固定バージョンは `pnpm-lock.yaml` 経由でそのままインストールされるため、移行時点の依存は影響を受けない（＝現行の既知バージョンを固定し、以後の更新にだけ待機期間を課す方針）。
- 緊急のホットフィックスをどうしても即時導入したい場合は `minimumReleaseAgeExclude` にパッケージ（またはバージョン指定）を列挙してバイパスする。

### 3. Vite 8 対応に伴うプラグイン更新（副次対応）

移行検証中、Vite 8（Rolldown ベース）で Babel ベースの `@vitejs/plugin-react@4.7.0` を使うと非推奨警告（`vite:react-babel` による `esbuild`/`optimizeDeps.rollupOptions` の deprecation、oxc 版への移行推奨）が出ていた。Vite 8 では Oxc/Rolldown パイプライン対応の **`@vitejs/plugin-react` v6 系**が正となるためこれに更新した（`vite.config.ts` の import は変更不要）。

- `minimumReleaseAge` の実挙動確認も兼ねる。`^6` 指定に対し最新の `6.0.3`（公開 2026-06-23、2週間未満）は待機期間に抵触するため pnpm は自動的に **`6.0.2`（公開 2026-05-14）を選択**した。ルールが意図どおり機能することを確認できた。
- なお Oxc 版 `@vitejs/plugin-react-oxc` は Vite 6/7 向け（peer `vite ^6-7`）であり Vite 8 では不適。採用しない。

---

## 検討した代替案

- **ADR-0001 の自作 `.pnpmfile.cjs` フックを維持**: pnpm 標準機能で代替可能になり、自前実装・キャッシュ管理・フェイルオープン挙動の保守コストが不要になるため破棄。
- **`minimumReleaseAgeStrict: false`（フォールバック許容）**: 条件を満たすバージョンが無いとき待機期間未満のバージョンを入れてしまい、対策が骨抜きになるため採用しない（明示設定で strict=ON がデフォルトになる挙動をそのまま採用）。
- **Socket Security / Dependabot・Renovate のみ / `ignore-scripts=true`**: ADR-0001 記載の理由（外部送信懸念／公開直後の未知パッケージに無効／ネイティブビルドを壊す）と同じく不採用。

---

## 影響・トレードオフ

### メリット

- 公開直後の悪意あるバージョンによるサプライチェーン攻撃を、pnpm 標準機能で自動的にブロックできる。
- 自作フックが不要になり、保守対象が設定 1 行（`minimumReleaseAge`）に集約される。
- レジストリメタデータの `time` を pnpm が参照するため、追加の HTTP 実装や年齢キャッシュファイル（`.pnpm-age-cache.json`）が不要。

### デメリット・注意点

- **2 週間ルールの副作用**: 緊急のセキュリティパッチも公開から 2 週間はインストールできない。即時導入が必要な場合は `minimumReleaseAgeExclude` でバイパスする。
- **最新版を掴めないケース**: 上記 `@vitejs/plugin-react` のように、最新バージョンが待機期間未満だと一つ前の版に固定される。意図した挙動だが、更新 PR 等でバージョンが上がらない場合はこの設定が理由である可能性を考慮する。
- **private レジストリ / `time` 欠落**: メタデータに公開日時が無いパッケージは、pnpm のデフォルト（`minimumReleaseAgeIgnoreMissingTime: true`）でチェックがスキップされる（フェイルオープン）。現状は公開 npm レジストリのみのため影響は限定的。

---

## 関連ファイル

- [`frontend/pnpm-workspace.yaml`](../../frontend/pnpm-workspace.yaml)（`minimumReleaseAge`）
- [`frontend/package.json`](../../frontend/package.json)（`packageManager`）
- [`frontend/pnpm-lock.yaml`](../../frontend/pnpm-lock.yaml)
- [`.github/workflows/frontend_ci.yml`](../../.github/workflows/frontend_ci.yml)
- [`.github/workflows/code_security.yml`](../../.github/workflows/code_security.yml)
- [`docker-compose.yml`](../../docker-compose.yml)
