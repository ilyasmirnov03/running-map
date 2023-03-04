<?php

namespace App\Controller\admin;

use App\Entity\Run;
use App\Repository\RunJoinRequestRepository;
use App\Repository\RunnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_index', methods: ['GET'])]
    public function index(RunJoinRequestRepository $runJoinRequestRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'requests' => $runJoinRequestRepository->findAll()
        ]);
    }
    #[Route('/accept/{id}', name: 'app_admin_accept', methods: ['POST'])]
    public function acceptRequest(EntityManagerInterface $manager, Run $run, RunnerRepository $runnerRepository, RunJoinRequestRepository $runJoinRequestRepository): RedirectResponse
    {
        $req = Request::createFromGlobals();

        if ($req->request->get('accept')) {
            $run->addRunner($runnerRepository->find($req->request->get('runnerId')));
            $manager->persist($run);
            $manager->flush();
        }

        $runJoinRequestRepository->remove($runJoinRequestRepository->find($req->request->get('request')), true);
        return $this->redirectToRoute('app_admin_index');
    }
}
