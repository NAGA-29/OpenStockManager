<?php

namespace Tests\Unit\Utils;

use App\Utils\GeneralUtil;
use PHPUnit\Framework\TestCase;

class GeneralUtilTest extends TestCase
{
    // =========================================================================
    // generalID
    // =========================================================================

    public function test_generalID_returns_uuid_when_no_digit_given(): void
    {
        $id = GeneralUtil::generalID();

        // UUID v4 format: 8-4-4-4-12 hex characters
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id
        );
    }

    public function test_generalID_returns_string_with_timestamp_when_digit_given(): void
    {
        $id = GeneralUtil::generalID(10);

        // Format: {random string}-{unix timestamp}
        $this->assertMatchesRegularExpression('/^.{10}-\d+$/', $id);
    }

    public function test_generalID_with_different_digits_produces_different_length_prefix(): void
    {
        $id5 = GeneralUtil::generalID(5);
        $id20 = GeneralUtil::generalID(20);

        // The prefix before the hyphen-timestamp should differ in length
        $prefix5 = explode('-', $id5)[0];
        $prefix20 = explode('-', $id20)[0];

        $this->assertEquals(5, strlen($prefix5));
        $this->assertEquals(20, strlen($prefix20));
    }

    public function test_generalID_without_digit_returns_string(): void
    {
        $id = GeneralUtil::generalID();
        $this->assertIsString($id);
    }

    // =========================================================================
    // generalToken
    // =========================================================================

    public function test_generalToken_returns_sha256_hash_when_no_digit_given(): void
    {
        $token = GeneralUtil::generalToken();

        // SHA-256 produces a 64-character hex string
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    public function test_generalToken_returns_string_with_timestamp_when_digit_given(): void
    {
        $token = GeneralUtil::generalToken(15);

        $this->assertMatchesRegularExpression('/^.{15}-\d+$/', $token);
    }

    public function test_generalToken_uniqueness(): void
    {
        $tokens = [];
        for ($i = 0; $i < 50; $i++) {
            $tokens[] = GeneralUtil::generalToken();
        }
        // All tokens should be unique
        $this->assertCount(50, array_unique($tokens));
    }

    // =========================================================================
    // sanitizeCsvValue
    // =========================================================================

    public function test_sanitizeCsvValue_returns_null_for_null_input(): void
    {
        $this->assertNull(GeneralUtil::sanitizeCsvValue(null));
    }

    public function test_sanitizeCsvValue_does_not_modify_safe_string(): void
    {
        $this->assertEquals('hello world', GeneralUtil::sanitizeCsvValue('hello world'));
    }

    public function test_sanitizeCsvValue_prefixes_equals_sign(): void
    {
        $this->assertEquals("'=SUM(A1:A10)", GeneralUtil::sanitizeCsvValue('=SUM(A1:A10)'));
    }

    public function test_sanitizeCsvValue_prefixes_plus_sign(): void
    {
        $this->assertEquals("'+cmd|' /C calc'!A0", GeneralUtil::sanitizeCsvValue('+cmd|' . "' /C calc'!A0"));
    }

    public function test_sanitizeCsvValue_prefixes_minus_sign(): void
    {
        $this->assertEquals("'-1+1", GeneralUtil::sanitizeCsvValue('-1+1'));
    }

    public function test_sanitizeCsvValue_prefixes_at_sign(): void
    {
        $this->assertEquals("'@SUM(A1)", GeneralUtil::sanitizeCsvValue('@SUM(A1)'));
    }

    public function test_sanitizeCsvValue_prefixes_tab_character(): void
    {
        $this->assertEquals("'\tmalicious", GeneralUtil::sanitizeCsvValue("\tmalicious"));
    }

    public function test_sanitizeCsvValue_prefixes_carriage_return(): void
    {
        $this->assertEquals("'\rmalicious", GeneralUtil::sanitizeCsvValue("\rmalicious"));
    }

    public function test_sanitizeCsvValue_does_not_prefix_if_dangerous_char_is_not_first(): void
    {
        $this->assertEquals('safe=value', GeneralUtil::sanitizeCsvValue('safe=value'));
        $this->assertEquals('safe+value', GeneralUtil::sanitizeCsvValue('safe+value'));
        $this->assertEquals('safe-value', GeneralUtil::sanitizeCsvValue('safe-value'));
        $this->assertEquals('safe@value', GeneralUtil::sanitizeCsvValue('safe@value'));
    }

    public function test_sanitizeCsvValue_handles_empty_string(): void
    {
        $this->assertEquals('', GeneralUtil::sanitizeCsvValue(''));
    }

    public function test_sanitizeCsvValue_handles_multibyte_safe_string(): void
    {
        $this->assertEquals('日本語テスト', GeneralUtil::sanitizeCsvValue('日本語テスト'));
    }

    // =========================================================================
    // sanitizeCsvRecord
    // =========================================================================

    public function test_sanitizeCsvRecord_sanitizes_all_string_values(): void
    {
        $record = [
            'name' => 'safe name',
            'formula' => '=DANGEROUS',
            'note' => '+attack',
        ];

        $result = GeneralUtil::sanitizeCsvRecord($record);

        $this->assertEquals('safe name', $result['name']);
        $this->assertEquals("'=DANGEROUS", $result['formula']);
        $this->assertEquals("'+attack", $result['note']);
    }

    public function test_sanitizeCsvRecord_does_not_modify_non_string_values(): void
    {
        $record = [
            'id' => 42,
            'active' => true,
            'rate' => 3.14,
            'nothing' => null,
        ];

        $result = GeneralUtil::sanitizeCsvRecord($record);

        $this->assertSame(42, $result['id']);
        $this->assertSame(true, $result['active']);
        $this->assertSame(3.14, $result['rate']);
        $this->assertNull($result['nothing']);
    }

    public function test_sanitizeCsvRecord_handles_empty_array(): void
    {
        $this->assertEquals([], GeneralUtil::sanitizeCsvRecord([]));
    }

    public function test_sanitizeCsvRecord_preserves_array_keys(): void
    {
        $record = ['key1' => 'val1', 'key2' => '=val2'];
        $result = GeneralUtil::sanitizeCsvRecord($record);

        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayHasKey('key2', $result);
    }
}
