<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Run;
use App\Entity\Admin;
use App\Entity\Runner;
use App\Entity\Coordinates;
use App\Entity\RunJoinRequest;
use App\Service\ToolboxService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $toolboxService;
    private $passwordHasher;
    private $faker;

    const N_RUNNERS = 10;

    public function __construct(UserPasswordHasherInterface $passwordHasher, ParameterBagInterface $params, ToolboxService $toolboxService)
    {
        $this->params = $params;
        $this->passwordHasher = $passwordHasher;
        $this->toolboxService = $toolboxService;
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadRun($manager, $this->toolboxService);
        $this->loadRequests($manager);
    }

    /**
     * Loading admin and runners
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function loadUsers(ObjectManager $manager)
    {
        $admin = new Admin();

        for ($i = 1; $i < self::N_RUNNERS; $i++) {
            $runner = new Runner();
            $runner
                ->setLogin($this->faker->userName())
                ->setPassword($this->passwordHasher->hashPassword($runner, '1234'))
                ->setRoles(['ROLE_RUNNER'])
                ->setPicture("image.png");
            $this->addReference("runner-" . $i, $runner);
            $manager->persist($runner);
        }

        $admin
            ->setLogin("superadmin")
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($admin, 'root'));
        $manager->persist($admin);

        $manager->flush();
    }

    /**
     * Loading run with coords
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function loadRun(ObjectManager $manager, ToolboxService $toolboxService)
    {
        //init date
        $date = new \DateTimeImmutable();

        //creating runs
        for ($i = 0; $i < 2; $i++) {
            $run = new Run();
            $run
                ->setName("Epic run " . $i)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setMap("default.kml")
                ->setRunDate($date)
                ->addRunner($this->getReference('runner-1'));
            $this->addReference("run-" . $i, $run);

            $manager->persist($run);
        }
        $this->getReference("run-0")->setRunDate($date->modify("+2 days"));
        $manager->persist( $this->getReference("run-0"));

        //loading coordinates from map
        $coords = $toolboxService->getCoordinates("default.kml");

        //creating coordinates in database
        for ($i = 1; $i < count($coords) - 1; $i++) {
            $c = new Coordinates();
            $c
                ->setRun($this->getReference('run-1'))
                ->setCoordsDate($date->modify('+20 seconds'))
                ->setLatitude($coords[$i]['latitude'])
                ->setLongitude($coords[$i]['longitude'])
                ->setRunner($this->getReference("runner-1"));

            $manager->persist($c);
            $date = $date->modify('+20 seconds');
        }
        $this->getReference("run-1")->setFinishedAt($date);
        $manager->persist($this->getReference("run-1"));
        $manager->flush();
    }

    /**
     * Loading run join requests
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function loadRequests(ObjectManager $manager)
    {
        for ($i = 0; $i < 2; $i++) {
            for ($j = 2; $j < self::N_RUNNERS; $j++) {
                $runRequest = new RunJoinRequest();
                $runRequest
                    ->setRun($this->getReference("run-" . $i))
                    ->setRunner($this->getReference("runner-" . $j));
                $manager->persist($runRequest);
            }
        }

        $manager->flush();
    }
}
