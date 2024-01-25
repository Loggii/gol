<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route(
        '/',
        name: 'home',
        defaults: ['fps' => 0],
        condition: 'params["fps"] >= 0',
    )]
    public function home(): Response
    {
        $fps = $this->getConfig()['fps'];
        if ($fps < 1) {
            $trigger = 'load';
        } else {
            $seconds = 1 / $fps;
            $trigger = 'every ' . $seconds . 's';
        }

        return $this->render('game/home.html.twig', [
            'trigger' => $trigger
        ]);
    }


    #[Route(
        '/gamefield',
        name: 'gamefield',
        methods: ['GET'],
    )]
    public function gamefield(): Response
    {
        $config = $this->getConfig();
        $width = $config['width'];
        $height = $config['height'];


        if (!file_exists(__DIR__ . '/game.json')) {
            $gameField = $this->initializeGame($width, $height);
            file_put_contents(__DIR__ . '/game.json', json_encode($gameField, JSON_THROW_ON_ERROR));
        } else {
            $gameFieldString = file_get_contents(__DIR__ . '/game.json');
            $gameField = json_decode($gameFieldString, true, 512, JSON_THROW_ON_ERROR);
        }

        $nextStep = $this->calculateNextStep($gameField, $height, $width);
        file_put_contents(__DIR__ . '/game.json', json_encode($nextStep, JSON_THROW_ON_ERROR));



        $table = $this->createTableString($nextStep);

        return $this->render('game/gametable.html.twig', [
            'table' => $table,
        ]);
    }


    #[Route(
        '/config',
        name: 'config',
        methods: ['POST'],
    )]
    public function config(Request $request): Response
    {
        $config = $this->getConfig();
        if (!empty($request->get('width'))) {
            $this->setConfig((int) $request->get('width'), $config['height'], $config['fps']);
        }
        $config = $this->getConfig();
        if (!empty($request->get('height'))) {
            $this->setConfig($config['width'], (int) $request->get('height'), $config['fps']);
        }
        $config = $this->getConfig();
        if ((int) $request->get('fps') === 0) {
            $this->setConfig($config['width'], $config['height'],(int) $request->get('fps'));
        }
        $config = $this->getConfig();
        if (!empty($request->get('fps'))) {
            $this->setConfig($config['width'], $config['height'],(int) $request->get('fps'));
        }

        return $this->home();
    }

    private function setConfig(int $width,int $height,int $fps): void
    {

            $config = [
                'width' => $width,
                'height' => $height,
                'fps' => $fps
            ];
            file_put_contents(__DIR__ . '/config.json', json_encode($config, JSON_THROW_ON_ERROR));

    }


    private function getConfig(): array
    {
        if (!file_exists(__DIR__ . '/config.json')) {
            $config = [
                'width' => 90,
                'height' => 50,
                'fps' => 1
            ];
            file_put_contents(__DIR__ . '/config.json', json_encode($config, JSON_THROW_ON_ERROR));
        } else {
            $configStr = file_get_contents(__DIR__ . '/config.json');
            $config = json_decode($configStr, true, 512, JSON_THROW_ON_ERROR);
        }

        return $config;
    }



    #[Route(
        '/gamefield/{id}',
        name: 'gamefieldset',
        methods: ['POST'],
    )]
    public function set(Request $request, int $id): Response
    {
        $gameFieldString = file_get_contents(__DIR__ . '/game.json');
        $gameField = json_decode($gameFieldString, true, 512, JSON_THROW_ON_ERROR);
        $cellId = 0;
        foreach ($gameField as &$row) {
            foreach ($row as &$cell) {
                if ($cellId === $id) {
                    $cell = 1;
                }
                $cellId++;
            }
            unset($cell);
        }
        unset($row);
        file_put_contents(__DIR__ . '/game.json', json_encode($gameField, JSON_THROW_ON_ERROR));

        $table = $this->createTableString($gameField);

        return $this->render('game/gametable.html.twig', [
            'table' => $table,
        ]);
    }


    private function calculateNextStep($board, int $height, int $width) {
        $oldHeight = count($board);
        $oldWidth = count($board[0]);
        $newBoard = $board;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $liveNeighbors = 0;

                // Zählen Sie die lebenden Nachbarn
                for ($i = -1; $i <= 1; $i++) {
                    for ($j = -1; $j <= 1; $j++) {
                        if ($i == 0 && $j == 0) continue; // Überspringen Sie die aktuelle Zelle
                        $ny = $y + $i;
                        $nx = $x + $j;
                        if (isset($board[$ny][$nx]) && $ny >= 0 && $ny < $height && $nx >= 0 && $nx < $width && $board[$ny][$nx]) {
                            $liveNeighbors++;
                        }
                    }
                }

                // Anwenden der Regeln des Game of Life
                if (isset($board[$y][$x])){
                    if ($board[$y][$x] && ($liveNeighbors < 2 || $liveNeighbors > 3)) {
                        $newBoard[$y][$x] = 0; // Tod durch Unterbevölkerung oder Überbevölkerung
                    } elseif (!$board[$y][$x] && $liveNeighbors == 3) {
                        $newBoard[$y][$x] = 1; // Geburt einer neuen Zelle
                    }
                } else {
                    $newBoard[$y][$x] = 0;
                }
            }
        }

        if ($oldHeight > $height || $oldWidth > $width) {
            return $this->verkleinereArray($newBoard, $width, $height);
        }

        return $newBoard;
    }


    private function verkleinereArray($array, $maxBreite, $maxHoehe): array {
        $verkleinertesArray = [];

        // Durch jede Zeile des Arrays gehen
        for ($i = 0; $i < $maxHoehe; $i++) {
            // Prüfen, ob die Zeile existiert
            if (!isset($array[$i])) {
                break;
            }

            // Jede Zeile auf die maximale Breite beschneiden
            $verkleinertesArray[$i] = array_slice($array[$i], 0, $maxBreite);
        }

        return $verkleinertesArray;
    }

    private function initializeGame($width, $height): array {
        $board = array();
        for ($y = 0; $y < $height; $y++) {
            $row = array();
            for ($x = 0; $x < $width; $x++) {
                $row[] = 0; // alle zellen sind tot
            }
            $board[] = $row;
        }
        return $board;
    }

    private function createTableString($board): string {
        $table = '';
        $id = 0;
        foreach ($board as $row) {
            $table .= '<tr>';
            foreach ($row as $cell) {
                $table .= '<td hx-trigger="click" hx-post="/gamefield/'.$id.'" hx-swap="inner" hx-target="#game_field" class="' . ($cell ? 'alive' : 'dead') . '"></td>';
                $id++;
            }
            $table .= '</tr>';
        }
        return $table;
    }
}
