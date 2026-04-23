<?php

declare(strict_types=1);

namespace App\Tests\Unit\Serializer;

use App\Entity\User;
use App\Entity\VaultFolder;
use App\Serializer\VaultFolderSerializer;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class VaultFolderSerializerTest extends TestCase
{
    public function testSerializeReturnsExpectedShape(): void
    {
        $folder = $this->makeFolder();

        $result = (new VaultFolderSerializer())->serialize($folder);

        self::assertSame([
            'id' => 1,
            'name' => 'Work',
            'color' => '#ff0000',
            'position' => 2,
        ], $result);
    }

    public function testSerializeNullColor(): void
    {
        $folder = $this->makeFolder(color: null);

        $result = (new VaultFolderSerializer())->serialize($folder);

        self::assertNull($result['color']);
    }

    private function makeFolder(?string $color = '#ff0000'): VaultFolder
    {
        $folder = new VaultFolder(new User(), 'Work');
        $folder->setColor($color)->setPosition(2);

        $idProp = new ReflectionProperty(VaultFolder::class, 'id');
        $idProp->setValue($folder, 1);

        return $folder;
    }
}
