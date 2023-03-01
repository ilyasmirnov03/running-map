<?php

namespace App\Controller\admin;

use App\Entity\Run;
use App\Entity\Runner;
use App\Form\RunType;
use App\Repository\RunRepository;
use App\Repository\RunnerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/run')]
class AdminRunController extends AbstractController
{
    /*
    **
    Runs management
    **
    */
    #[Route('/new', name: 'app_admin_run_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RunRepository $runRepository, RunnerRepository $runnerRepository): Response
    {
        $run = new Run();
        // creating custom form
        $form = $this->createFormBuilder($run)
            ->add('name')
            ->add('map')
            ->add('run_date')
            ->add('runner', ChoiceType::class, [
                'choices' => $runnerRepository->findAll(),
                'choice_value' => 'id',
                'choice_label' => function (Runner $runner) {
                    return $runner->getLogin();
                },
                'multiple' => true,
                'mapped' => false
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            //saving as many runners as were selected
            foreach ($form->get("runner")->getData() as $runner) {
                $run->addRunner($runner);
            }

            $runRepository->save($run, true);
            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/run/new.html.twig', [
            'run' => $run,
            'form' => $form,
        ]);
    }

    #[Route('/', name: 'app_admin_runs', methods: ['GET'])]
    public function runs(RunRepository $runRepository): Response
    {
        return $this->render('admin/run/index.html.twig', [
            'runs' => $runRepository->findAll()
        ]);
    }

    #[Route('/{id}', name: 'app_admin_run_show', methods: ['GET'])]
    public function show(Run $run): Response
    {
        return $this->render('admin/run/show.html.twig', [
            'run' => $run,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_run_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Run $run, RunRepository $runRepository): Response
    {
        $form = $this->createForm(RunType::class, $run);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $runRepository->save($run, true);

            return $this->redirectToRoute('app_run_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/run/edit.html.twig', [
            'run' => $run,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_run_delete', methods: ['POST'])]
    public function delete(Request $request, Run $run, RunRepository $runRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $run->getId(), $request->request->get('_token'))) {
            $runRepository->remove($run, true);
        }

        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
