<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Run;
use App\Entity\Admin;
use App\Entity\Runner;
use App\Entity\Coordinates;
use App\Entity\RunJoinRequest;
use App\Service\ToolboxService;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $toolboxService;
    private $passwordHasher;
    private $faker;

    const N_RUNNERS = 10;

    public function __construct(UserPasswordHasherInterface $passwordHasher, ToolboxService $toolboxService)
    {
        $this->passwordHasher = $passwordHasher;
        $this->toolboxService = $toolboxService;
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadRun($manager, $this->toolboxService);
        $this->loadRequests($manager);
        $this->loadLiveRuns($manager);
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
                ->setPicture("default.png");
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
        $manager->persist($this->getReference("run-0"));

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

    /**
     * Loading demo live runs with runners
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function loadLiveRuns(ObjectManager $manager)
    {
        $date = new \DateTimeImmutable();
        $date = $date->modify("+1 minute");

        //generating runs
        for ($i = 1; $i < 3; $i++) {
            $run = new Run();
            $run->setMap("map-" . $i . ".kml")
                ->setName("run-" . $i)
                ->setRunDate($date);
            //associating runners to a run
            for ($j = 1; $j < self::N_RUNNERS; $j++) {
                $run->addRunner($this->getReference('runner-' . $j));
            }
            $this->addReference('run-live-' . $i, $run);
            $manager->persist($run);
            $date = $date->modify("+1 day");
        }

        //generating coordinates
        /* 
        $i = run
        $j = runner
        $k = coords
        */
        for ($i = 1; $i < 3; $i++) {
            $mapCoordinates = $this->toolboxService->getCoordinates("map-" . $i . ".kml");
            $coordsDate = new \DateTimeImmutable();
            $coordsDate = $coordsDate->modify("+1 minute");
            for ($j = 1; $j < self::N_RUNNERS; $j++) {
                $speed = random_int(5, 30);
                $coordsDateRunner = $coordsDate;
                for ($k = 1; $k < count($mapCoordinates); $k++) {
                    $coords = new Coordinates();
                    $coords
                        ->setRun($this->getReference('run-live-' . $i))
                        ->setCoordsDate($coordsDateRunner->modify('+' . $speed . ' seconds'))
                        ->setLatitude($mapCoordinates[$k]['latitude'])
                        ->setLongitude($mapCoordinates[$k]['longitude'])
                        ->setRunner($this->getReference("runner-" . $j));
                    $manager->persist($coords);
                    $coordsDateRunner = $coordsDateRunner->modify('+' . $speed . ' seconds');
                }
                $coordsDate->modify("+1 day");
            }
        }
        $manager->flush();
    }
}
