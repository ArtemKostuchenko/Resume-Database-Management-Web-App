<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, ApplicationRepository $applicationRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit= $request->query->getInt('limit', 10);
        $offset = ($page - 1) * $limit;

        $applications = $applicationRepository->findBy([], ['sent_at' => 'DESC'], $limit, $offset);
        $total = $applicationRepository->count([]);

        return $this->render('home/index.html.twig', [
            'applications' => $applications,
            'limit' => $limit,
            'page' => $page,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ]);
    }
}
