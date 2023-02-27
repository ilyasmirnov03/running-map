<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RunnerController extends AbstractController
{
    #[Route('/runner', name: 'app_runner')]
    public function index(): Response
    {
        return $this->render('runner/index.html.twig', [
            'controller_name' => 'RunnerController',
        ]);
    }
}
