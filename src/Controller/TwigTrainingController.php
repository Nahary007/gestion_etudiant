<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TwigTrainingController extends AbstractController{
    #[Route('/training', name: 'app_twig_training')]
    public function index(): Response
    {
        return $this->render('twig_training/index.html.twig', [
            'controller_name' => 'TwigTrainingController',
        ]);
    }
}
