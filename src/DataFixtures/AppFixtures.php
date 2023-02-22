<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Run;
use App\Entity\Admin;
use App\Entity\Runner;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadRun($manager);
    }

    public function loadUsers(ObjectManager $manager)
    {
        $admin = new Admin();
        $runner = new Runner();

        $runner
            ->setLogin($this->faker->userName())
            ->setPassword($this->passwordHasher->hashPassword($runner, '1234'))
            ->setRoles(['ROLE_RUNNER'])
            ->setPicture("image.png");
        $manager->persist($runner);

        $admin
            ->setLogin("superadmin")
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($admin, 'root'));
        $manager->persist($admin);

        $manager->flush();
    }

    public function loadRun(ObjectManager $manager)
    {
        $run = new Run();
        $run
            ->setName("Epic Run")
            ->setCreatedAt(new \DateTimeImmutable())
            ->setMap("map.png")
            ->setRunDate(new \DateTimeImmutable());

        $manager->persist($run);
        $manager->flush();
    }
}
