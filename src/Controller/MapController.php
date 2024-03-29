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
            //if there is no latest, return null
            'run' =>  (isset($runRepository->findLatest()[0])) ? $runRepository->findLatest()[0] : null,
            'upcomingRuns' => $runRepository->findUpcomingRuns(),
            'pastRuns' => $runRepository->findPastRuns()
        ]);
    }
    
    #[Route('/map/{id}', name: 'app_map_id')]
    public function run(RunRepository $runRepository, $id = null): Response
    {   
        //if there is no latest run -> redirect to index
        if (!isset($runRepository->findLatest()[0]) && $id == null) {
            return $this->redirectToRoute('app_map_index');
        }

        return $this->render('map/show.html.twig', [
            'run' => ($id) ? $runRepository->find($id) : $runRepository->findLatest()[0]
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
