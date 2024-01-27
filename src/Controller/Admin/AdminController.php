<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Core\Exceptions\Client\Auth\ForbiddenException;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->request->user?->getIsAdmin()) {
            throw new ForbiddenException();
        }
    }
}
