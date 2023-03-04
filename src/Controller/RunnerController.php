<?php

namespace App\Controller;

use App\Entity\Run;
use App\Entity\RunJoinRequest;
use App\Repository\RunJoinRequestRepository;
use App\Repository\RunnerRepository;
use App\Repository\RunRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/runner')]
class RunnerController extends AbstractController
{
    #[Route('/', name: 'app_runner')]
    public function index(RunRepository $runRepository, RunnerRepository $runnerRepository): Response
    {
        $joinRequests = $runnerRepository->find($this->getUser())->getRunJoinRequests();
        $runsFromRequests = array();
        foreach ($joinRequests as $r) {
            $runsFromRequests[] = $r->getRun();
        }

        return $this->render('runner/index.html.twig', [
            'runs' => $runRepository->findAll(),
            'user' => $this->getUser(),
            'requests' => $runsFromRequests
        ]);
    }

    #[Route('/participate/{id}', name: 'app_runner_participate')]
    public function participate(RunJoinRequestRepository $runJoinRequestRepository, Run $run): RedirectResponse
    {
        $runJoinRequest = new RunJoinRequest();
        $runJoinRequest
            ->setRunner($this->getUser())
            ->setRun($run);
        $runJoinRequestRepository->save($runJoinRequest, true);
        return $this->redirectToRoute('app_runner');
    }
}
