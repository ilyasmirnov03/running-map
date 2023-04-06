<?php

namespace App\Controller\admin;

use App\Entity\Run;
use App\Form\RunType;
use App\Repository\RunRepository;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/run')]
class AdminRunController extends AbstractController
{
    /*
    **
    Runs management
    **
    */
    #[Route('/new', name: 'app_admin_run_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RunRepository $runRepository,  SluggerInterface $slugger): Response
    {
        $run = new Run();

        $form = $this->createForm(RunType::class, $run);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //saving run file to public
            $mapFile = $form->get('map')->getData();
            if ($mapFile) {
                $originalFilename = pathinfo($mapFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $mapFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $mapFile->move(
                        $this->getParameter('map_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $run->setMap($newFilename);
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

    #[Route('/{id}/edit', name: 'app_admin_run_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Run $run, RunRepository $runRepository): Response
    {
        $form = $this->createForm(RunType::class, $run);
        //run is not finished but has begun
        if ($run->getRunDate()->format('U') <= time() && !$run->getFinishedAt()) {
            $form = $this->createFormBuilder($run)
                ->add('name', TextType::class)
                ->getForm();
        //run is finished
        } else if ($run->getFinishedAt()) {
            $form = $this->createFormBuilder($run)
                ->add('name', TextType::class, ['label' => 'Nom'])
                ->add('finished_at', null, ['label' => 'Date de fin de course'])
                ->getForm();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $runRepository->save($run, true);

            return $this->redirectToRoute('app_admin_runs', [], Response::HTTP_SEE_OTHER);
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
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->getParameter('map_directory') . $run->getMap());
            $runRepository->remove($run, true);
        }

        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/finish/{id}', name: 'app_admin_run_finish', methods: ['POST'])]
    public function finish(Run $run, EntityManagerInterface $manager): Response
    {
        $run->setFinishedAt(new \DateTimeImmutable());
        $manager->persist($run);
        $manager->flush();
        return $this->redirectToRoute('app_map_index', [], Response::HTTP_SEE_OTHER);
    }
}
