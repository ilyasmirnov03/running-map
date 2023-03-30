<?php

namespace App\Controller;

use App\Entity\Run;
use App\Repository\CoordinatesRepository;
use App\Repository\RunRepository;
use App\Service\ToolboxService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    #[Route('/', name: 'app_map_index')]
    public function index(RunRepository $runRepository): Response
    {
        return $this->render('map/home.html.twig', [
            'run' =>  $runRepository->findLatest()[0],
            'upcomingRuns' => $runRepository->findUpcomingRuns(),
            'pastRuns' => $runRepository->findPastRuns()
        ]);
    }
    
    #[Route('/map/{id}', name: 'app_map_id')]
    public function run(Run $run, RunRepository $runRepository): Response
    {
        return $this->render('map/index.html.twig', [
            'run' => $run,
            'upcomingRuns' => $runRepository->findUpcomingRuns(),
            'pastRuns' => $runRepository->findPastRuns()
        ]);
    }

    #[Route('/coords/{id}/{timestamp}', name: 'app_map_coords')]
    public function coords(Run $run, CoordinatesRepository $coordinatesRepository, ToolboxService $toolboxService, $timestamp): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $runners = array();
        foreach ($run->getRunners() as $runner) {
            $coords = array();
            foreach ($coordinatesRepository->findBy(["run" => $run, "runner" => $runner]) as $coord) {
                array_push($coords, ["latitude" => $coord->getLatitude(), "longitude" => $coord->getLongitude(), "date" => $coord->getCoordsDate()->format('U')]);
            }
            array_push($runners, ["runner" => ["login" => $runner->getLogin(), "picture" => $runner->getPicture(), "id" => $runner->getId()],  "coords" => $toolboxService->find_closest($coords, $timestamp)]);
        }
        $response->setContent(json_encode($runners));
        return $response;
    }
}
