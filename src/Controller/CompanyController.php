<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    #[Route('/companies', name: 'app_companies')]
    public function index(Request $request, CompanyRepository $companyRepository): Response
    {
        $q = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'desc');
        $limit = $request->query->getInt('limit', 10);
        $page = max($request->query->getInt('page', 1), 1);
        $offset = ($page - 1) * $limit;

        $orderBy = [];

        if ($sort === 'desc') {
            $orderBy = ['created_at' => 'DESC'];
        } elseif ($sort === 'old') {
            $orderBy = ['created_at' => 'ASC'];
        }

        $companies = $companyRepository->searchCompanies($q, $orderBy, $limit, ($page - 1) * $limit);
        $total = $companyRepository->countSearchCompanies($q);

        return $this->render('company/index.html.twig', [
            'companies' => $companies,
            'q' => $q,
            'sort' => $sort,
            'limit' => $limit,
            'page' => $page,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ]);
    }

    #[Route('/companies/add', name: 'app_company_add')]
    public function addCompany(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company = $form->getData();
            $company->setCreatedAt(new \DateTime());
            $company->setUpdatedAt(new \DateTime());
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash("success", "Компанію успішно додано");

            return $this->redirectToRoute('app_companies');
        }

        return $this->render('company/add.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/company/{id<\d+>}/edit', name: 'app_company_edit')]
    public function editCompany(Request $request, EntityManagerInterface $entityManager, Company $company): Response
    {
        $form = $this->createForm(CompanyType::class, $company);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash("success", "Компанію успішно оновлено");

            return $this->redirectToRoute('app_companies');
        }

        return $this->render('company/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/company/{id<\d+>}/delete/confirm', name: 'app_company_delete_confirm')]
    public function confirmDelete(Company $company): Response
    {
        return $this->render('company/delete.html.twig', [
            'company' => $company
        ]);
    }

    #[Route('/company/{id<\d+>}/delete', name: 'app_company_delete', methods: ['POST'])]
    public function deleteCompany(Request $request, EntityManagerInterface $entityManager, Company $company): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $entityManager->remove($company);

            $entityManager->flush();

            $this->addFlash('success', 'Компанію успішно видалено');
        }

        return $this->redirectToRoute('app_companies');
    }
}
