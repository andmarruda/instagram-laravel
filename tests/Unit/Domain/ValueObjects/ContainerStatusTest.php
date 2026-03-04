<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;
use PHPUnit\Framework\TestCase;

class ContainerStatusTest extends TestCase
{
    public function test_finished_is_ready_to_publish(): void
    {
        $this->assertTrue(ContainerStatus::Finished->isReadyToPublish());
        $this->assertFalse(ContainerStatus::InProgress->isReadyToPublish());
        $this->assertFalse(ContainerStatus::Error->isReadyToPublish());
    }

    public function test_final_statuses(): void
    {
        $this->assertTrue(ContainerStatus::Expired->isFinal());
        $this->assertTrue(ContainerStatus::Error->isFinal());
        $this->assertTrue(ContainerStatus::Published->isFinal());
        $this->assertFalse(ContainerStatus::Finished->isFinal());
        $this->assertFalse(ContainerStatus::InProgress->isFinal());
    }

    public function test_from_api_string(): void
    {
        $this->assertSame(ContainerStatus::Finished, ContainerStatus::from('FINISHED'));
        $this->assertSame(ContainerStatus::InProgress, ContainerStatus::from('IN_PROGRESS'));
    }
}
