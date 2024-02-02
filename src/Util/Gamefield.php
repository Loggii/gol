<?php
declare(strict_types=1);

namespace App\Util;

use Symfony\Component\HttpFoundation\Request;

class Gamefield
{
    private function __construct(private array $gamefield)
    {
    }

    public static function fromArray(array $gamefield): self
    {
        return new self($gamefield);
    }

    public static function initialize(int $width, int $height): self
    {
        $board = [];
        for ($y = 0; $y < $height; $y++) {
            $row = array();
            for ($x = 0; $x < $width; $x++) {
                $row[] = 0; // alle zellen sind tot
            }
            $board[] = $row;
        }
        return new self($board);
    }

    public function calculateNextStep(Config $config): Gamefield
    {
        $width = $config->getWidth();
        $height = $config->getHeight();
        $oldHeight = count($this->gamefield);
        $oldWidth = count($this->gamefield[0]);
        $newBoard = $this->gamefield;

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
                if (isset($board[$y][$x])) {
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
            return new self($this->verkleinereArray($newBoard, $width, $height));
        }

        return new self($newBoard);
    }

    private function verkleinereArray($array, $width, $height): array
    {
        $verkleinertesArray = [];

        // Durch jede Zeile des Arrays gehen
        for ($i = 0; $i < $height; $i++) {
            // Prüfen, ob die Zeile existiert
            if (!isset($array[$i])) {
                break;
            }

            // Jede Zeile auf die maximale Breite beschneiden
            $verkleinertesArray[$i] = array_slice($array[$i], 0, $width);
        }

        return $verkleinertesArray;
    }

    public function changeCellStatus(int $id): void
    {
        $cellId = 0;
        $found = false;
        foreach ($this->gamefield as &$row) {
            foreach ($row as &$cell) {
                if ($cellId === $id) {
                    if ($cell == 1) {
                        $cell = 0;
                    } else {
                        $cell = 1;
                    }
                    $found = true;
                    break;
                }
                $cellId++;
            }
            unset($cell);
            if ($found) {
                break;
            }
        }
    }

    public function asArray(): array
    {
        return $this->gamefield;
    }
}
