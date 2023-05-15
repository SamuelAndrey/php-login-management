<?php

namespace SamuelAndrey\Belajar\PHP\MVC\Middleware;

use SamuelAndrey\Belajar\PHP\MVC\App\View;
use SamuelAndrey\Belajar\PHP\MVC\Config\Database;
use SamuelAndrey\Belajar\PHP\MVC\Domain\Session;
use SamuelAndrey\Belajar\PHP\MVC\Repository\SessionRepository;
use SamuelAndrey\Belajar\PHP\MVC\Repository\UserRepository;
use SamuelAndrey\Belajar\PHP\MVC\Service\SessionService;

class MustNotLoginMiddleware implements Middleware
{
    private SessionService $sessionService;

    public function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function before(): void
    {
        $user = $this->sessionService->current();
        if ($user != null) {
            View::redirect('/');
        } 
    }
}