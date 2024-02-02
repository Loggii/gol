<?php

namespace App\Controller;

use App\Util\Gamefield;
use App\Util\SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gamefield')]
class GamefieldController extends AbstractController
{
    public function __construct(private readonly SessionManager $sessionManager)
    {
    }

    #[Route(
        '',
        name: 'get_field',
        methods: ['GET'],
    )]
    public function gamefield(Request $request): Response
    {
        $config = $this->sessionManager->getConfig($request);
        $gamefield = $this->sessionManager->getGame($request, $config);
        $nextStep = $gamefield->calculateNextStep($config);
        $this->sessionManager->setGame($request, $nextStep);

        $table = $this->createTableString($nextStep);

        return $this->render('game/gametable.html.twig', [
            'table' => $table,
        ]);
    }


    #[Route(
        '/{id}',
        name: 'cell_set',
        methods: ['POST'],
    )]
    public function set(Request $request, int $id): Response
    {
        $config = $this->sessionManager->getConfig($request);
        $gameField = $this->sessionManager->getGame($request, $config);
        $gameField->changeCellStatus($id);
        $this->sessionManager->setGame($request, $gameField);
        $table = $this->createTableString($gameField);

        return $this->render('game/gametable.html.twig', [
            'table' => $table,
        ]);
    }


    private function createTableString(Gamefield $gamefield): string
    {
        $table = '';
        $id = 0;
        $gamefieldArray = $gamefield->asArray();
        foreach ($gamefieldArray as $row) {
            $table .= '<tr>';
            foreach ($row as $cell) {
                $table .= '<td hx-trigger="click" hx-post="/gamefield/' . $id . '" hx-swap="inner" hx-target="#game_field" class="' . ($cell ? 'alive' : 'dead') . '"></td>';
                $id++;
            }
            $table .= '</tr>';
        }
        return $table;
    }
}
