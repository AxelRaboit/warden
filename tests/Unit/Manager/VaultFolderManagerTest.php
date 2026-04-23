<?php

declare(strict_types=1);

namespace App\Tests\Unit\Manager;

use App\DTO\Vault\VaultFolderInput;
use App\Entity\User;
use App\Entity\VaultFolder;
use App\Manager\VaultFolderManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class VaultFolderManagerTest extends TestCase
{
    public function testCreatePersistsAndFlushes(): void
    {
        $user = new User();
        $input = new VaultFolderInput(name: 'Work', color: '#ff0000');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(VaultFolder::class));
        $em->expects(self::once())->method('flush');

        $folder = (new VaultFolderManager($em))->create($user, $input);

        self::assertSame($user, $folder->getUser());
        self::assertSame('Work', $folder->getName());
        self::assertSame('#ff0000', $folder->getColor());
    }

    public function testUpdateAppliesInputAndFlushes(): void
    {
        $folder = new VaultFolder(new User(), 'Old');
        $input = new VaultFolderInput(name: 'New', color: '#00ff00');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');
        $em->expects(self::never())->method('persist');

        (new VaultFolderManager($em))->update($folder, $input);

        self::assertSame('New', $folder->getName());
        self::assertSame('#00ff00', $folder->getColor());
    }

    public function testDeleteRemovesAndFlushes(): void
    {
        $folder = new VaultFolder(new User(), 'Work');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('remove')->with($folder);
        $em->expects(self::once())->method('flush');

        (new VaultFolderManager($em))->delete($folder);
    }
}
