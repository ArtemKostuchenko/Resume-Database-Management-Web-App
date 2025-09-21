<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'app_statistics')]
    public function index(ApplicationRepository $applicationRepository, ChartBuilderInterface $chartBuilder): Response
    {
        $topResumes = $applicationRepository->findTopResumesByReaction();
        $worstResumes = $applicationRepository->findTopResumesByReaction("rejected");

        $topChart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $labels = [];
        $data = [];

        foreach ($topResumes as $resume) {
            $labels[] = $resume['position_title'];
            $data[] = $resume['reactions'];
        }

        $topChart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Кількість позитивних відгуків',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'data' => $data,
                ]
            ]
        ]);

        $topChart->setOptions([
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1
                    ]
                ]
            ]
        ]);

        $labels = [];
        $data = [];

        foreach ($worstResumes as $resume) {
            $labels[] = $resume['position_title'];
            $data[] = $resume['reactions'];
        }

        $worstChart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $worstChart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Кількість негативних відгуків',
                    'backgroundColor' => ['rgba(255, 99, 132, 0.5)', 'rgba(255, 159, 64, 0.5)', 'rgba(255, 205, 86, 0.5)'],
                    'data' => $data,
                ],
            ],
        ]);

        $worstChart->setOptions([
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1
                    ]
                ]
            ]
        ]);

        return $this->render('statistics/index.html.twig', [
            'topChart' => $topChart,
            'worstChart' => $worstChart,
        ]);
    }
}
