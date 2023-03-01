<?php

namespace App\Controller\admin;

use App\Entity\Runner;
use App\Form\RunnerType;
use App\Repository\RunnerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/runner')]
class AdminRunnerController extends AbstractController
{
    /*
    **
    Runners management
    **
    */
    #[Route('/', name: 'app_admin_runners', methods: ['GET'])]
    public function runners(RunnerRepository $runnerRepository): Response
    {
        return $this->render('admin/runner/index.html.twig', [
            'runners' => $runnerRepository->findAll()
        ]);
    }

    #[Route('/new', name: 'app_admin_runner_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RunnerRepository $runnerRepository): Response
    {
        $runner = new Runner();
        $form = $this->createForm(RunnerType::class, $runner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $runnerRepository->save($runner, true);

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/runner/new.html.twig', [
            'runner' => $runner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_runner_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Runner $runner, RunnerRepository $runnerRepository): Response
    {
        $form = $this->createForm(RunnerType::class, $runner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $runnerRepository->save($runner, true);

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/runner/edit.html.twig', [
            'runner' => $runner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_runner_delete', methods: ['POST'])]
    public function delete(Request $request, Runner $runner, RunnerRepository $runnerRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$runner->getId(), $request->request->get('_token'))) {
            $runnerRepository->remove($runner, true);
        }

        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
