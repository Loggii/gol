<?php

namespace App\Controller;

use App\Util\SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/config')]
class ConfigController extends AbstractController
{
    public function __construct(private readonly SessionManager $sessionManager)
    {
    }

    #[Route(
        '',
        name: 'set_config',
        methods: ['POST'],
    )]
    public function config(Request $request): Response
    {
        $config = $this->sessionManager->getConfig($request);
        if (!empty($request->get('width'))) {
            $config->setWidth((int)$request->get('width'));
        }
        if (!empty($request->get('height'))) {
            $config->setHeight((int)$request->get('height'));
        }
        if (!empty($request->get('fps'))) {
            $config->setFps((int)$request->get('fps'));
        }
        if ((int)($request->get('fps') == 0)) {
            $config->setFps((int)$request->get('fps'));
        }

        $this->sessionManager->setConfig($request, $config);

        return $this->render('game/home.html.twig', [
            'trigger' => $config->getFpsAsTrigger(),
            'width' => $config->getWidth(),
            'height' => $config->getHeight(),
            'fps' => $config->getFps(),
        ]);
    }
}
