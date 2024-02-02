<?php

namespace App\Controller;

use App\Util\SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    public function __construct(private readonly SessionManager $sessionManager)
    {
    }

    #[Route('/', name: 'home')]
    public function home(Request $request): Response
    {
        $config = $this->sessionManager->getConfig($request);

        return $this->render('game/home.html.twig', [
            'trigger' => $config->getFpsAsTrigger(),
            'width' => $config->getWidth(),
            'height' => $config->getHeight(),
            'fps' => $config->getFps(),
        ]);
    }
}
