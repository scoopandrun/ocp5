<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Core\Exceptions\Client\Auth\ForbiddenException;
use App\Core\Exceptions\Client\Auth\UnauthorizedException;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        if ($user && !$user->getIsAdmin()) {
            throw new ForbiddenException();
        }
    }
}
