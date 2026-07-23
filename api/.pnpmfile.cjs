'use strict';

/**
 * pnpm セキュリティフック
 *
 * afterAllResolved フックで以下を実施:
 *   - npm レジストリに問い合わせ、パッケージの公開日時を確認
 *   - 公開から MIN_AGE_DAYS 日未満のパッケージが含まれる場合はインストールを中止
 *
 * サプライチェーン攻撃対策として、公開直後の悪意あるパッケージを防ぐ。
 *
 * スキップ方法（非推奨）:
 *   PNPM_SKIP_AGE_CHECK=1 pnpm install
 */

const https = require('https');
const fs = require('fs');
const path = require('path');

// --- 設定 ---
const MIN_AGE_DAYS = 7;
const MIN_AGE_MS = MIN_AGE_DAYS * 24 * 60 * 60 * 1000;
const CACHE_PATH = path.resolve(__dirname, '.pnpm-age-cache.json');
const REGISTRY_TIMEOUT_MS = 15000;

// ---- キャッシュ操作 ----

function loadCache() {
    try {
        return JSON.parse(fs.readFileSync(CACHE_PATH, 'utf8'));
    } catch {
        return {};
    }
}

function saveCache(cache) {
    try {
        fs.writeFileSync(CACHE_PATH, JSON.stringify(cache, null, 2), 'utf8');
    } catch {
        // キャッシュ書き込み失敗は致命的ではないので無視
    }
}

// ---- lockfile キーのパース ----

/**
 * pnpm v8 形式 ("/name@version") と v9 形式 ("name@version") の両方に対応
 * スコープパッケージ ("@scope/name@version") も処理する
 */
function parsePackageKey(key) {
    const cleanKey = key.startsWith('/') ? key.slice(1) : key;

    // 最後の "@" でパッケージ名とバージョンを分割
    const atIndex = cleanKey.lastIndexOf('@');
    if (atIndex <= 0) return null;

    const name = cleanKey.slice(0, atIndex);
    // ピア依存関係サフィックス "(react@18.0.0)" を除去
    const version = cleanKey.slice(atIndex + 1).split('(')[0].trim();

    if (!name || !version) return null;
    return { name, version };
}

// ---- npm レジストリ問い合わせ ----

/**
 * npm レジストリから指定バージョンの公開日時を取得する
 * 取得失敗時は null を返す（ネットワーク障害時はチェックをスキップ）
 */
function fetchPublishTime(name, version) {
    return new Promise((resolve) => {
        // スコープパッケージの "/" をエンコード
        const registryPath = name.startsWith('@')
            ? `/${name.replace('/', '%2F')}`
            : `/${name}`;

        const options = {
            hostname: 'registry.npmjs.org',
            path: registryPath,
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'User-Agent': 'pnpm-security-hook/1.0',
            },
        };

        const req = https.request(options, (res) => {
            if (res.statusCode !== 200) {
                resolve(null);
                return;
            }

            let data = '';
            res.on('data', (chunk) => {
                data += chunk;
            });
            res.on('end', () => {
                try {
                    const pkg = JSON.parse(data);
                    const timeStr = pkg.time?.[version];
                    resolve(timeStr ? new Date(timeStr) : null);
                } catch {
                    resolve(null);
                }
            });
        });

        req.on('error', () => resolve(null));
        req.setTimeout(REGISTRY_TIMEOUT_MS, () => {
            req.destroy();
            resolve(null);
        });
        req.end();
    });
}

// ---- pnpm フック ----

module.exports = {
    hooks: {
        /**
         * 依存関係解決後に実行されるフック
         * 公開から MIN_AGE_DAYS 日未満のパッケージがあればエラーを throw してインストールを中止する
         */
        async afterAllResolved(lockfile, context) {
            if (process.env.PNPM_SKIP_AGE_CHECK === '1') {
                context.log(
                    `⚠️  パッケージ年齢チェックをスキップします (PNPM_SKIP_AGE_CHECK=1)`
                );
                return lockfile;
            }

            const packages = lockfile.packages || {};
            const packageKeys = Object.keys(packages).filter((key) => {
                const snap = packages[key];
                const resolution = snap.resolution || {};
                // ローカルディレクトリ・git 依存関係はスキップ
                return !resolution.directory && resolution.type !== 'git';
            });

            if (packageKeys.length === 0) return lockfile;

            const cache = loadCache();
            const now = Date.now();
            const tooNew = [];
            const cacheUpdates = {};

            // 未キャッシュのパッケージを抽出
            const uncachedKeys = packageKeys.filter((key) => {
                const parsed = parsePackageKey(key);
                if (!parsed) return false;
                return !cache[`${parsed.name}@${parsed.version}`];
            });

            if (uncachedKeys.length > 0) {
                context.log(
                    `\n🔍 新規パッケージ ${uncachedKeys.length} 件の公開日時を確認しています...`
                );
            }

            await Promise.all(
                packageKeys.map(async (key) => {
                    const parsed = parsePackageKey(key);
                    if (!parsed) return;

                    const { name, version } = parsed;
                    const cacheKey = `${name}@${version}`;
                    let publishTime;

                    if (cache[cacheKey]) {
                        // キャッシュから取得
                        publishTime = cache[cacheKey].publishTime
                            ? new Date(cache[cacheKey].publishTime)
                            : null;
                    } else {
                        // レジストリから取得
                        publishTime = await fetchPublishTime(name, version);
                        cacheUpdates[cacheKey] = {
                            publishTime: publishTime?.toISOString() ?? null,
                            checkedAt: new Date().toISOString(),
                        };
                    }

                    if (publishTime && now - publishTime.getTime() < MIN_AGE_MS) {
                        const ageDays = Math.floor(
                            (now - publishTime.getTime()) / (24 * 60 * 60 * 1000)
                        );
                        tooNew.push({ name, version, publishTime, ageDays });
                    }
                })
            );

            // キャッシュを更新
            if (Object.keys(cacheUpdates).length > 0) {
                saveCache({ ...cache, ...cacheUpdates });
            }

            if (tooNew.length > 0) {
                const list = tooNew
                    .map(
                        (p) =>
                            `  - ${p.name}@${p.version}` +
                            ` (公開: ${p.publishTime.toISOString().slice(0, 10)}, 経過: ${p.ageDays}日)`
                    )
                    .join('\n');

                throw new Error(
                    `\n⛔ セキュリティチェック失敗\n\n` +
                        `以下のパッケージは公開から ${MIN_AGE_DAYS} 日未満です:\n${list}\n\n` +
                        `サプライチェーン攻撃のリスクがあるため、インストールを中止しました。\n` +
                        `${MIN_AGE_DAYS} 日後に再試行するか、該当パッケージを package.json から削除してください。\n\n` +
                        `強制的にスキップする場合（非推奨）:\n` +
                        `  PNPM_SKIP_AGE_CHECK=1 pnpm install\n`
                );
            }

            if (uncachedKeys.length > 0) {
                context.log(`✅ パッケージ年齢チェック完了 (問題なし)`);
            }

            return lockfile;
        },
    },
};
