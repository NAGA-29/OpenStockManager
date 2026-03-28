<?php

namespace Tests\Unit\Exceptions\Device;

use App\Exceptions\Domain\Device\DeviceAlreadySoldException;
use App\Exceptions\Domain\Device\DeviceCurrentlyRentedException;
use App\Exceptions\Domain\Device\DeviceDefectiveException;
use App\Exceptions\Domain\Device\DeviceDuplicateException;
use App\Exceptions\Domain\Device\DeviceNotFoundException;
use App\Exceptions\Domain\DeviceException;
use Tests\TestCase;

/**
 * デバイス系ドメイン例外の forDevice() ファクトリメソッドのテスト
 *
 * @covers \App\Exceptions\Domain\Device\DeviceAlreadySoldException
 * @covers \App\Exceptions\Domain\Device\DeviceCurrentlyRentedException
 * @covers \App\Exceptions\Domain\Device\DeviceDefectiveException
 * @covers \App\Exceptions\Domain\Device\DeviceDuplicateException
 * @covers \App\Exceptions\Domain\Device\DeviceNotFoundException
 */
class DeviceExceptionsTest extends TestCase
{
    private const DEVICE_ID = 'STB_TestDevice_000001';

    // =========================================================================
    // DeviceAlreadySoldException
    // =========================================================================

    /**
     * DeviceAlreadySoldException::forDevice() が正しいクラスのインスタンスを返すこと
     */
    public function test_DeviceAlreadySoldException_forDevice_returns_correct_instance(): void
    {
        $e = DeviceAlreadySoldException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceAlreadySoldException::class, $e);
    }

    /**
     * DeviceAlreadySoldException::forDevice() の context に device_id が設定されること
     */
    public function test_DeviceAlreadySoldException_forDevice_sets_device_id_in_context(): void
    {
        $e = DeviceAlreadySoldException::forDevice(self::DEVICE_ID);

        $this->assertEquals(self::DEVICE_ID, $e->getContext()['device_id']);
    }

    /**
     * DeviceAlreadySoldException が DeviceException のサブクラスであること
     */
    public function test_DeviceAlreadySoldException_is_subclass_of_DeviceException(): void
    {
        $e = DeviceAlreadySoldException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceException::class, $e);
    }

    // =========================================================================
    // DeviceCurrentlyRentedException
    // =========================================================================

    /**
     * DeviceCurrentlyRentedException::forDevice() が正しいクラスのインスタンスを返すこと
     */
    public function test_DeviceCurrentlyRentedException_forDevice_returns_correct_instance(): void
    {
        $e = DeviceCurrentlyRentedException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceCurrentlyRentedException::class, $e);
    }

    /**
     * DeviceCurrentlyRentedException::forDevice() の context に device_id が設定されること
     */
    public function test_DeviceCurrentlyRentedException_forDevice_sets_device_id_in_context(): void
    {
        $e = DeviceCurrentlyRentedException::forDevice(self::DEVICE_ID);

        $this->assertEquals(self::DEVICE_ID, $e->getContext()['device_id']);
    }

    /**
     * DeviceCurrentlyRentedException が DeviceException のサブクラスであること
     */
    public function test_DeviceCurrentlyRentedException_is_subclass_of_DeviceException(): void
    {
        $e = DeviceCurrentlyRentedException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceException::class, $e);
    }

    // =========================================================================
    // DeviceDefectiveException
    // =========================================================================

    /**
     * DeviceDefectiveException::forDevice() が正しいクラスのインスタンスを返すこと
     */
    public function test_DeviceDefectiveException_forDevice_returns_correct_instance(): void
    {
        $e = DeviceDefectiveException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceDefectiveException::class, $e);
    }

    /**
     * DeviceDefectiveException::forDevice() の context に device_id が設定されること
     */
    public function test_DeviceDefectiveException_forDevice_sets_device_id_in_context(): void
    {
        $e = DeviceDefectiveException::forDevice(self::DEVICE_ID);

        $this->assertEquals(self::DEVICE_ID, $e->getContext()['device_id']);
    }

    /**
     * DeviceDefectiveException が DeviceException のサブクラスであること
     */
    public function test_DeviceDefectiveException_is_subclass_of_DeviceException(): void
    {
        $e = DeviceDefectiveException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceException::class, $e);
    }

    // =========================================================================
    // DeviceDuplicateException
    // =========================================================================

    /**
     * DeviceDuplicateException::forDevice() が正しいクラスのインスタンスを返すこと
     */
    public function test_DeviceDuplicateException_forDevice_returns_correct_instance(): void
    {
        $e = DeviceDuplicateException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceDuplicateException::class, $e);
    }

    /**
     * DeviceDuplicateException::forDevice() の context に device_id が設定されること
     */
    public function test_DeviceDuplicateException_forDevice_sets_device_id_in_context(): void
    {
        $e = DeviceDuplicateException::forDevice(self::DEVICE_ID);

        $this->assertEquals(self::DEVICE_ID, $e->getContext()['device_id']);
    }

    /**
     * DeviceDuplicateException が DeviceException のサブクラスであること
     */
    public function test_DeviceDuplicateException_is_subclass_of_DeviceException(): void
    {
        $e = DeviceDuplicateException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceException::class, $e);
    }

    // =========================================================================
    // DeviceNotFoundException
    // =========================================================================

    /**
     * DeviceNotFoundException::forDevice() が正しいクラスのインスタンスを返すこと
     */
    public function test_DeviceNotFoundException_forDevice_returns_correct_instance(): void
    {
        $e = DeviceNotFoundException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceNotFoundException::class, $e);
    }

    /**
     * DeviceNotFoundException::forDevice() の context に device_id が設定されること
     */
    public function test_DeviceNotFoundException_forDevice_sets_device_id_in_context(): void
    {
        $e = DeviceNotFoundException::forDevice(self::DEVICE_ID);

        $this->assertEquals(self::DEVICE_ID, $e->getContext()['device_id']);
    }

    /**
     * DeviceNotFoundException が DeviceException のサブクラスであること
     */
    public function test_DeviceNotFoundException_is_subclass_of_DeviceException(): void
    {
        $e = DeviceNotFoundException::forDevice(self::DEVICE_ID);

        $this->assertInstanceOf(DeviceException::class, $e);
    }
}
