<?php
declare(strict_types=1);

namespace App\Util;

use Symfony\Component\HttpFoundation\Request;

class SessionManager
{
    public function getConfig(Request $request): Config
    {
        $session = $request->getSession();
        if (empty($session->get('config'))) {
            return Config::by(70, 40, 2);
        }
        return $session->get('config');
    }

    public function setConfig(Request $request, Config $config): void
    {
        $request->getSession()->set('config', $config);
    }


    public function getGame(Request $request, Config $config): Gamefield
    {
        if (empty($request->getSession()->get('game'))) {
            $gamefiel = Gamefield::initialize($config->getWidth(), $config->getHeight());
            $this->setGame($request, $gamefiel);
        }
        print_r($request->getSession()->get('game'));
        return Gamefield::fromArray($request->getSession()->get('game'));
    }


    public function setGame(Request $request, Gamefield $gamefield): void
    {
        $request->getSession()->set('game', $gamefield->asArray());
    }
}
