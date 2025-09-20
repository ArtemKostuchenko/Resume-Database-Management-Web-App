<?php

namespace App\Controller;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends AbstractController
{
    #[Route('/applications', name: 'app_applications')]
    public function index(Request $request, ApplicationRepository $applicationRepository, PaginationService $paginationService): Response
    {
        $params = $paginationService->getPaginationParams($request, ['sent_at' => 'desc'], 'sent_at');

        $applications = $applicationRepository->searchApplications(
            $params['q'],
            $params['orderBy'],
            $params['limit'],
            $params['offset']
        );

        $total = $applicationRepository->countApplications($params['q']);

        return $this->render('application/index.html.twig', [
            'applications' => $applications,
            'q' => $params['q'],
            'sort' => $params['sort'],
            'limit' => $params['limit'],
            'page' => $params['page'],
            'total' => $total,
            'pages' => ceil($total / $params['limit']),
        ]);
    }

    #[Route('/applications/{id<\d+>}/view', name: 'app_application_view')]
    public function show(Application $application): Response
    {
        $resume = $application->getResume();
        $company = $application->getCompany();


        return $this->render('application/show.html.twig', [
            'application' => $application,
            'resume' => $resume,
            'company' => $company,
        ]);
    }

    #[Route('/applications/{id<\d+>}/accept', name: 'app_application_accept', methods: ['POST'])]
    public function acceptApplication(Application $application, EntityManagerInterface $entityManager, Request $request): Response
    {
        $application->setReaction("approved");
        $application->setReactionAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Заявку прийнято');

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_application_view', ['id' => $application->getId()]);
    }

    #[Route('/applications/{id<\d+>}/reject', name: 'app_application_reject', methods: ['POST'])]
    public function rejectApplication(Application $application, EntityManagerInterface $entityManager, Request $request): Response
    {
        $application->setReaction("rejected");
        $application->setReactionAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('error', 'Заявку відхилено');

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_application_view', ['id' => $application->getId()]);
    }
}
