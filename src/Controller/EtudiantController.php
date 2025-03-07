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

    #[Route('/etudiant', name: 'etudiant')]
    public function index(Request $request, EtudiantRepository $etudiantRepository): Response
    {
        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($etudiant);
            $this->entityManager->flush();

            return $this->redirectToRoute('etudiant');
        }

        // Récupérer tous les étudiants
        $etudiants = $etudiantRepository->findAll();

        return $this->render('etudiant/index.html.twig', [
            'form' => $form->createView(),
            'etudiants' => $etudiants, // Passer les étudiants à la vue
        ]);
    }

    #[Route('/etudiant/{id}', name: 'etudiant.edit', requirements:['id' => '\d+'])]
    public function editEtudiant(string $id, Request $request, EtudiantRepository $etudiantRepository){

        if($id) {
            $une_etudiant = $etudiantRepository->find($id);
            $form = $this->createForm(EtudiantType::class, $une_etudiant);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $this->entityManager->flush();
        
                return $this->redirectToRoute('etudiant');
            }
        }

        return $this->render('etudiant/edit.html.twig', [
            'etudiant' => $une_etudiant,'form' => $form->createView()
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
