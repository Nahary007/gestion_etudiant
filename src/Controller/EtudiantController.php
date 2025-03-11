<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Form\EtudiantType;
use App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EtudiantController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/etudiant/{page}', name: 'etudiant', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function index(Request $request, EtudiantRepository $etudiantRepository, int $page): Response
    {
        $limit = 20;
        $totalEtudiants = $etudiantRepository->countTotalEtudiants();
        $totalPages = ceil($totalEtudiants / $limit);
        
        $etudiants = $etudiantRepository->findPaginatedPage($page, $limit);

        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($etudiant);
            $this->entityManager->flush();
            return $this->redirectToRoute('etudiant');
        }

        return $this->render('etudiant/index.html.twig', [
            'form' => $form->createView(),
            'etudiants' => $etudiants,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/etudiant/search', name: 'etudiant.search', methods: ['GET'])]
    public function search(Request $request, EtudiantRepository $etudiantRepository): Response
    {
        $query = $request->query->get('q', '');
        $etudiants = $etudiantRepository->createQueryBuilder('e')
            ->where('e.nom LIKE :query')
            ->orWhere('e.prenom LIKE :query')
            ->orWhere('e.email LIKE :query')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult();

        return $this->render('etudiant/_list.html.twig', [
            'etudiants' => $etudiants,
        ]);
    }

    #[Route('/etudiant/{id}/edit', name: 'etudiant.edit', requirements: ['id' => '\d+'])]
    public function editEtudiant(int $id, Request $request, EtudiantRepository $etudiantRepository): Response
    {
        $etudiant = $etudiantRepository->find($id);
        if (!$etudiant) {
            throw $this->createNotFoundException('Étudiant non trouvé');
        }

        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('etudiant');
        }

        return $this->render('etudiant/edit.html.twig', [
            'form' => $form->createView(),
            'etudiant' => $etudiant,
        ]);
    }

    #[Route('/etudiant/supprimer/{id}', name:'etudiant.supprimer', requirements:['id' => '\d+'])]
    public function supprimerEtudiant(string $id, EtudiantRepository $etudiantRepository) {
        if($id) {
            $une_etudiant = $etudiantRepository->find($id);
            if($une_etudiant) {
                $this->entityManager->remove($une_etudiant);
                $this->entityManager->flush();   
                return $this->redirectToRoute('etudiant');
            }
        }
    }
}
