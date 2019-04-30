<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\QuestionnaireRepositoryInterface;
use Illuminate\Http\Request;

class QuestionnaireController extends AbstractController
{
    public function __construct(
        Request $request,
        QuestionnaireRepositoryInterface $repository
    ) {
        $this->request = $request;
        $this->repository = $repository;

        parent::__construct();
    }
}
