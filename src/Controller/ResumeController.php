<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Resume;
use App\Form\ResumeType;
use App\Form\SendResumeType;
use App\Repository\ResumeRepository;
use App\Service\PaginationService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResumeController extends AbstractController
{
    #[Route('/resumes', name: 'app_resumes')]
    public function index(Request $request, ResumeRepository $resumeRepository, PaginationService $paginationService): Response
    {
        $params = $paginationService->getPaginationParams($request);

        $resumes = $resumeRepository->searchResumes(
            $params['q'],
            $params['orderBy'],
            $params['limit'],
            $params['offset']
        );

        $total = $resumeRepository->countResumes($params['q']);

        return $this->render('resume/index.html.twig', [
            'resumes' => $resumes,
            'q' => $params['q'],
            'sort' => $params['sort'],
            'limit' => $params['limit'],
            'page' => $params['page'],
            'total' => $total,
            'pages' => ceil($total / $params['limit']),
        ]);
    }


    #[Route('/resume/{id}', name: 'app_resume')]
    public function show(Resume $resume, Request $request, EntityManagerInterface $entityManager): Response
    {
        $application = new Application();
        $application->setResume($resume);

        $form = $this->createForm(SendResumeType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application->setSentAt(new \DateTime());

            $entityManager->persist($application);
            $entityManager->flush();

            $this->addFlash('success', 'Резюме успішно надіслано');

            return $this->redirectToRoute('app_resume', ['id' => $resume->getId()]);
        }

        $applications = $resume->getApplications();

        return $this->render('resume/show.html.twig', [
            'resume' => $resume,
            'form' => $form->createView(),
            'applications' => $applications,
        ]);
    }

    #[Route('/resume/add', name: 'app_resume_add', priority: 2)]
    public function addResume(Request $request, UploadService $uploadService, EntityManagerInterface $entityManager): Response
    {
        $resume = new Resume();
        $form = $this->createForm(ResumeType::class, $resume);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resume = $form->getData();
            $resume->setCreatedAt(new \DateTime());
            $resume->setUpdatedAt(new \DateTime());

            $resumeFile = $form->get('resumeFile')->getData();

            if ($resumeFile) {
                try {
                    $newFilename = $uploadService->uploadFile($resumeFile, $this->getParameter('resumes_directory'));

                    $resume->setFilePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Не вдалося завантажити файл');

                    return $this->render('resume/add.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            if (!$resume->getFilePath() && !$resume->getContent()) {
                $this->addFlash('error', 'Напишіть текст резюме або додайте файл');

                return $this->render('resume/add.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->persist($resume);
            $entityManager->flush();

            $this->addFlash('success', 'Резюме успішно додано');

            return $this->redirectToRoute('app_resumes');
        }


        return $this->render('resume/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/resume/{id<\d+>}/edit', name: 'app_resume_edit')]
    public function editResume(Request $request, Resume $resume, UploadService $uploadService, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResumeType::class, $resume);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resume->setUpdatedAt(new \DateTime());

            $resumeFile = $form->get('resumeFile')->getData();

            if ($resumeFile) {
                $uploadService->removeFile($resume->getFilePath(), $this->getParameter('resumes_directory'));

                try {
                    $newFilename = $uploadService->uploadFile($resumeFile, $this->getParameter('resumes_directory'));

                    $resume->setFilePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Не вдалося завантажити файл');

                    return $this->render('resume/edit.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

            }

            if (!$resume->getFilePath() && !$resume->getContent()) {
                $this->addFlash('error', 'Напишіть текст резюме або додайте файл');

                return $this->render('resume/add.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Резюме успішно оновлено');

            return $this->redirectToRoute('app_resumes');
        }


        return $this->render('resume/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/resume/{id}/download', name: 'app_resume_download')]
    public function download(Resume $resume): Response
    {
        if (!$resume->getFilePath()) {
            $this->addFlash('error', 'Файл для цього резюме відсутній');
            return $this->redirectToRoute('app_resumes');
        }

        $filePath = $this->getParameter('resumes_directory') . '/' . $resume->getFilePath();

        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Файл не знайдено');
            return $this->redirectToRoute('app_resumes');
        }

        return $this->file($filePath);
    }

    #[Route('/resume/{id<\d+>}/delete/confirm', name: 'app_resume_delete_confirm')]
    public function confirmDelete(Resume $resume): Response
    {
        return $this->render('resume/delete.html.twig', [
            'resume' => $resume
        ]);
    }

    #[Route('/resume/{id<\d+>}/delete', name: 'app_resume_delete', methods: ['POST'])]
    public function deleteResume(Request $request, EntityManagerInterface $entityManager, Resume $resume, UploadService $uploadService): Response
    {
        if ($this->isCsrfTokenValid('delete' . $resume->getId(), $request->request->get('_token'))) {
            $uploadService->removeFile($resume->getFilePath(), $this->getParameter('resumes_directory'));

            $entityManager->remove($resume);

            $entityManager->flush();

            $this->addFlash('success', 'Резюме успішно видалено');
        }

        return $this->redirectToRoute('app_resumes');
    }
}
