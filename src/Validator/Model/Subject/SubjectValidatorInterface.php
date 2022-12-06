<?php

namespace LightSaml\Validator\Model\Subject;

use LightSaml\Model\Assertion\Subject;

interface SubjectValidatorInterface
{
    /**
     * @return void
     */
    public function validateSubject(Subject $subject);
}
