<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\VaultFolder;
use PHPUnit\Framework\TestCase;

final class VaultFolderTest extends TestCase
{
    public function testConstructorSetsUserAndName(): void
    {
        $user = new User();
        $folder = new VaultFolder($user, 'Work');

        self::assertSame($user, $folder->getUser());
        self::assertSame('Work', $folder->getName());
    }

    public function testIdIsNullBeforePersist(): void
    {
        $folder = new VaultFolder(new User(), 'Work');

        self::assertNull($folder->getId());
    }

    public function testDefaultPositionIsZero(): void
    {
        $folder = new VaultFolder(new User(), 'Work');

        self::assertSame(0, $folder->getPosition());
    }

    public function testDefaultColorIsNull(): void
    {
        $folder = new VaultFolder(new User(), 'Work');

        self::assertNull($folder->getColor());
    }

    public function testSettersAreFluent(): void
    {
        $folder = new VaultFolder(new User(), 'Work');

        self::assertSame($folder, $folder->setName('Personal'));
        self::assertSame($folder, $folder->setColor('#ff0000'));
        self::assertSame($folder, $folder->setPosition(3));
    }

    public function testGettersReturnSetValues(): void
    {
        $folder = new VaultFolder(new User(), 'Work');
        $folder->setName('Personal')->setColor('#ff0000')->setPosition(3);

        self::assertSame('Personal', $folder->getName());
        self::assertSame('#ff0000', $folder->getColor());
        self::assertSame(3, $folder->getPosition());
    }
}
