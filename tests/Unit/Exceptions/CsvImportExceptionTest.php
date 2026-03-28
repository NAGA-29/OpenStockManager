<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Infrastructure\CsvImportException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Exceptions\Infrastructure\CsvImportException
 */
class CsvImportExceptionTest extends TestCase
{
    /**
     * forRow() が CsvImportException のインスタンスを返すこと
     */
    public function test_forRow_returns_CsvImportException_instance(): void
    {
        $e = CsvImportException::forRow(1, '不正なデータです');

        $this->assertInstanceOf(CsvImportException::class, $e);
    }

    /**
     * forRow() のメッセージが $reason 文字列と一致すること
     */
    public function test_forRow_sets_message_to_reason(): void
    {
        $reason = '必須フィールドが空です';
        $e = CsvImportException::forRow(3, $reason);

        $this->assertEquals($reason, $e->getMessage());
    }

    /**
     * forRow() の context に row_number が設定されること
     */
    public function test_forRow_sets_row_number_in_context(): void
    {
        $e = CsvImportException::forRow(5, 'エラー');

        $this->assertEquals(5, $e->getContext()['row_number']);
    }

    /**
     * forRow() に $rowData を渡した場合、context の row_data に反映されること
     */
    public function test_forRow_sets_row_data_in_context(): void
    {
        $rowData = ['device_id' => 'DEV-001', 'device_name' => 'テスト機器'];
        $e = CsvImportException::forRow(2, 'エラー', $rowData);

        $this->assertEquals($rowData, $e->getContext()['row_data']);
    }

    /**
     * $rowData を省略した場合、context の row_data が空配列になること
     */
    public function test_forRow_defaults_row_data_to_empty_array(): void
    {
        $e = CsvImportException::forRow(1, 'エラー');

        $this->assertEquals([], $e->getContext()['row_data']);
    }

    /**
     * context に row_number と row_data の両キーが含まれること
     */
    public function test_forRow_context_contains_both_keys(): void
    {
        $e = CsvImportException::forRow(10, 'エラー', ['col' => 'val']);

        $context = $e->getContext();
        $this->assertArrayHasKey('row_number', $context);
        $this->assertArrayHasKey('row_data', $context);
    }
}
