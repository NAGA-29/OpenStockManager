<?php

namespace App\Traits;

trait Keyword
{
    /**
     * 検索キーワードからスペースを排除
     * 参考: https://qiita.com/mpyw/items/a704cb900dfda0fc0331
     * @access public
     * @param string $input
     * @param int $limit
     * @return array
     */
    public function extractKeywords(string $input, int $limit = -1): array
    {
        $matches = [];
        preg_replace_callback(
            '/""(*SKIP)(*FAIL)|"([^"]++)"|([^"\p{Z}\p{Cc}]++)/u',
            function (array $match) use (&$matches) {
                $matches[] = $match[2] ?? $match[1];
            },
            $input,
            $limit,
            $_,
            PREG_SET_ORDER
        );
        return array_values(array_unique($matches));
    }
}
