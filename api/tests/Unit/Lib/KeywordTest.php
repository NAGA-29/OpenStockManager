<?php

namespace Tests\Unit\Lib;

use App\Traits\Keyword;
use PHPUnit\Framework\TestCase;

class KeywordTest extends TestCase
{
    use Keyword;

    public function test_extractKeywords_single_word(): void
    {
        $result = $this->extractKeywords('hello');
        $this->assertEquals(['hello'], $result);
    }

    public function test_extractKeywords_multiple_words_separated_by_space(): void
    {
        $result = $this->extractKeywords('hello world');
        $this->assertEquals(['hello', 'world'], $result);
    }

    public function test_extractKeywords_multiple_spaces_between_words(): void
    {
        $result = $this->extractKeywords('hello    world');
        $this->assertEquals(['hello', 'world'], $result);
    }

    public function test_extractKeywords_quoted_phrase_treated_as_single_keyword(): void
    {
        $result = $this->extractKeywords('"hello world"');
        $this->assertEquals(['hello world'], $result);
    }

    public function test_extractKeywords_mixed_quoted_and_unquoted(): void
    {
        $result = $this->extractKeywords('foo "hello world" bar');
        $this->assertEquals(['foo', 'hello world', 'bar'], $result);
    }

    public function test_extractKeywords_removes_duplicates(): void
    {
        $result = $this->extractKeywords('hello hello world');
        $this->assertEquals(['hello', 'world'], $result);
    }

    public function test_extractKeywords_empty_string(): void
    {
        $result = $this->extractKeywords('');
        $this->assertEquals([], $result);
    }

    public function test_extractKeywords_only_spaces(): void
    {
        $result = $this->extractKeywords('   ');
        $this->assertEquals([], $result);
    }

    public function test_extractKeywords_japanese_keywords(): void
    {
        $result = $this->extractKeywords('デバイス 管理');
        $this->assertEquals(['デバイス', '管理'], $result);
    }

    public function test_extractKeywords_quoted_japanese_phrase(): void
    {
        $result = $this->extractKeywords('"デバイス管理" テスト');
        $this->assertEquals(['デバイス管理', 'テスト'], $result);
    }

    public function test_extractKeywords_with_limit(): void
    {
        $result = $this->extractKeywords('one two three four', 2);
        $this->assertCount(2, $result);
        $this->assertEquals(['one', 'two'], $result);
    }

    public function test_extractKeywords_escaped_double_quotes(): void
    {
        // "" is treated as escaped quote and skipped
        $result = $this->extractKeywords('hello "" world');
        $this->assertEquals(['hello', 'world'], $result);
    }

    public function test_extractKeywords_fullwidth_space_separation(): void
    {
        // Full-width space (U+3000) is a Zs unicode category character
        $result = $this->extractKeywords("hello\u{3000}world");
        $this->assertEquals(['hello', 'world'], $result);
    }

    public function test_extractKeywords_tab_is_treated_as_separator(): void
    {
        // Tab is a control character (Cc category), so it should be a separator
        $result = $this->extractKeywords("hello\tworld");
        $this->assertEquals(['hello', 'world'], $result);
    }
}
