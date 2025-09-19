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
    public function index(CompanyRepository $companyRepository): Response
    {
        $companies = $companyRepository->findAll();

        return $this->render('company/index.html.twig', [
            'companies' => $companies,
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

            $this->addFlash("success", "Company added successfully");

            return $this->redirectToRoute('app_companies');
        }

        return $this->render('company/add.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
