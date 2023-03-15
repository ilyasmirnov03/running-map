<?php

namespace App\Controller;

use App\Entity\Run;
use App\Entity\Runner;
use App\Entity\RunJoinRequest;
use App\Repository\RunRepository;
use App\Repository\RunnerRepository;
use App\Repository\RunJoinRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    
    #[Route('/{id}', name: 'app_runner_profile')]
    public function profile(Runner $runner): Response
    {
        return $this->render('runner/profile.html.twig', [
            'connectedUser' => $this->getUser() == $runner,
            'runner' => $runner,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_runner_edit', methods: ['GET', 'POST'])]
    /**
     * Edit runner's profile picture
     *
     * @param Request $request
     * @param Runner $runner
     * @param RunnerRepository $runnerRepository
     * @param SluggerInterface $slugger
     * @return Response
     */   
    public function edit(Request $request, Runner $runner, RunnerRepository $runnerRepository, SluggerInterface $slugger): Response
    {
        // $form = $this->createForm(RunnerType::class, $runner);
        $form = $this->createFormBuilder($runner)
            ->add('picture', FileType::class, [
                'mapped' => false
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $pictureFile->move(
                        $this->getParameter('pfp_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $runner->setPicture($newFilename);
            }
            $runnerRepository->save($runner, true);

            return $this->redirectToRoute('app_runner_profile', ["id" => $runner->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('runner/edit.html.twig', [
            'runner' => $runner,
            'form' => $form,
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
