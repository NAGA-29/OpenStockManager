<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\AppException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

// AppException is abstract, so use a concrete subclass for testing
class ConcreteAppException extends AppException {}

/**
 * @covers \App\Exceptions\AppException
 */
class AppExceptionTest extends TestCase
{
    /**
     * コンストラクタで渡したメッセージが getMessage() で取得できること
     */
    public function test_getMessage_returns_constructor_message(): void
    {
        $e = new ConcreteAppException('テストエラーメッセージ');

        $this->assertEquals('テストエラーメッセージ', $e->getMessage());
    }

    /**
     * コンストラクタで渡した context 配列が getContext() でそのまま返ること
     */
    public function test_getContext_returns_context_array(): void
    {
        $context = ['key' => 'value', 'device_id' => 'DEV-001'];
        $e = new ConcreteAppException('error', $context);

        $this->assertEquals($context, $e->getContext());
    }

    /**
     * context を省略した場合、getContext() が空配列を返すこと
     */
    public function test_getContext_returns_empty_array_when_omitted(): void
    {
        $e = new ConcreteAppException('error');

        $this->assertEquals([], $e->getContext());
    }

    /**
     * $previous 例外を渡した場合、getPrevious() で取得できること
     */
    public function test_getPrevious_returns_previous_exception(): void
    {
        $previous = new RuntimeException('original error');
        $e = new ConcreteAppException('wrapped error', [], $previous);

        $this->assertSame($previous, $e->getPrevious());
    }

    /**
     * $previous を省略した場合、getPrevious() が null を返すこと
     */
    public function test_getPrevious_returns_null_when_omitted(): void
    {
        $e = new ConcreteAppException('error');

        $this->assertNull($e->getPrevious());
    }

    /**
     * AppException が RuntimeException のサブクラスであること
     */
    public function test_is_instance_of_RuntimeException(): void
    {
        $e = new ConcreteAppException('error');

        $this->assertInstanceOf(RuntimeException::class, $e);
    }
}
