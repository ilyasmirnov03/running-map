<?php

namespace App\Controller;

use App\Entity\Run;
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
        return $this->render('map/index.html.twig', [
            'controller_name' => 'MapController',
            'run' => $run
        ]);
    }
}
?>