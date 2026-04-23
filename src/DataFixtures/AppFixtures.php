<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Locale;
use App\Entity\Setting;
use App\Entity\User;
use App\Enum\LocaleEnum;
use App\Enum\UserRoleEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Locales
        $frenchLocale = (new Locale())->setCode(LocaleEnum::French->value)->setName('Français')->setIsDefault(true)->setPosition(0);
        $englishLocale = (new Locale())->setCode(LocaleEnum::English->value)->setName('English')->setPosition(1);
        $manager->persist($frenchLocale);
        $manager->persist($englishLocale);

        // Settings
        $settings = [
            ['site_name', 'Warden', 'string', 'general'],
            ['site_description', 'Self-hosted password manager', 'string', 'general'],
            ['default_locale', LocaleEnum::French->value, 'string', 'general'],
        ];

        foreach ($settings as [$key, $value, $type, $group]) {
            $setting = (new Setting())->setKey($key)->setValue($value)->setType($type)->setGroup($group);
            $manager->persist($setting);
        }

        // Admin user
        $user = new User();
        $user->setEmail('admin@warden.app')
             ->setName('Admin User')
             ->setRoles([UserRoleEnum::Admin->value])
             ->setArgon2Salt(bin2hex(random_bytes(32)))
             ->setPassword($this->hasher->hashPassword($user, 'password'));
        $manager->persist($user);

        // Demo user
        $demoUser = new User();
        $demoUser->setEmail('demo@warden.app')
                 ->setName('Demo User')
                 ->setRoles([UserRoleEnum::User->value])
                 ->setIsDemo(true)
                 ->setArgon2Salt(bin2hex(random_bytes(32)))
                 ->setPassword($this->hasher->hashPassword($demoUser, 'demo'));
        $manager->persist($demoUser);

        $manager->flush();
    }
}
