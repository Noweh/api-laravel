<?php

namespace App\Services\Validation;

class QuestionnaireValidator extends AbstractValidator
{
    public $rules = [];
    public $translatedFieldsRules = ['title' => 'required|min:3'];
}