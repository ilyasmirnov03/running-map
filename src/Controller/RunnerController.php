<?php

namespace App\Controller;

use App\Repository\RunRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/runner')]
class RunnerController extends AbstractController
{
    #[Route('/', name: 'app_runner')]
    public function index(RunRepository $runRepository): Response
    {
        return $this->render('runner/index.html.twig', [
            'runs' => $runRepository->findAll(),
            'user' => $this->getUser()
        ]);
    }
}
