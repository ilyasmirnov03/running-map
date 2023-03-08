<?php

namespace App\Controller;

use App\Entity\Run;
use App\Repository\CoordinatesRepository;
use App\Service\ToolboxService;
use LDAP\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    // TODO: Redirect to most recent run
    #[Route('/', name: 'app_map_index')]
    public function index(): Response
    {
        return $this->render('map/index.html.twig', [
            'controller_name' => 'MapController',
        ]);
    }

    #[Route('/{id}', name: 'app_map_id')]
    public function run(Run $run): Response
    {
        return $this->render('map/show.html.twig', [
            'run' => $run
        ]);
    }

    #[Route('/coords/{id}/{timestamp}', name: 'app_map_coords')]
    public function coords(Run $run, CoordinatesRepository $coordinatesRepository, ToolboxService $toolboxService, $timestamp): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $runners = array();
        $coords = array();
        foreach ($run->getRunners() as $runner) {
            foreach ($coordinatesRepository->findBy(["run" => $run, "runner" => $runner]) as $coord) {
                array_push($coords, ["latitude" => $coord->getLatitude(), "longitude" => $coord->getLongitude(), "date" => $coord->getCoordsDate()->format('U')]);
            }
            array_push($runners, ["runner" => $runner->getId(), "coords" => $toolboxService->find_closest($coords, $timestamp)]);
        }
        $response->setContent(json_encode($runners));
        return $response;
    }
}
