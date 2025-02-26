<?php

namespace LightSaml\Validator\Model\Subject;

use LightSaml\Error\LightSamlValidationException;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Subject;
use LightSaml\Model\Assertion\SubjectConfirmation;
use LightSaml\Model\Assertion\SubjectConfirmationData;
use LightSaml\Validator\Model\NameId\NameIdValidatorInterface;

class SubjectValidator implements SubjectValidatorInterface
{
    public function __construct(protected NameIdValidatorInterface $nameIdValidator)
    {
    }

    /**
     * @throws LightSamlValidationException
     *
     * @return void
     */
    public function validateSubject(Subject $subject)
    {
        if (
            false == $subject->getNameID()
            && false == $subject->getAllSubjectConfirmations()
        ) {
            throw new LightSamlValidationException('Subject MUST contain either an identifier or a subject confirmation');
        }

        if ($subject->getNameID()) {
            $this->nameIdValidator->validateNameId($subject->getNameID());
        }

        foreach ($subject->getAllSubjectConfirmations() as $subjectConfirmation) {
            $this->validateSubjectConfirmation($subjectConfirmation);
        }
    }

    /**
     * @throws LightSamlValidationException
     */
    protected function validateSubjectConfirmation(SubjectConfirmation $subjectConfirmation)
    {
        if (false == Helper::validateRequiredString($subjectConfirmation->getMethod())) {
            throw new LightSamlValidationException('Method attribute of SubjectConfirmation MUST contain at least one non-whitespace character');
        }
        if (false == Helper::validateWellFormedUriString($subjectConfirmation->getMethod())) {
            throw new LightSamlValidationException('SubjectConfirmation element has Method attribute which is not a wellformed absolute uri.');
        }
        if ($subjectConfirmation->getNameID()) {
            $this->nameIdValidator->validateNameId($subjectConfirmation->getNameID());
        }
        if ($subjectConfirmation->getSubjectConfirmationData()) {
            $this->validateSubjectConfirmationData($subjectConfirmation->getSubjectConfirmationData());
        }
    }

    protected function validateSubjectConfirmationData(SubjectConfirmationData $subjectConfirmationData)
    {
        if ($subjectConfirmationData->getRecipient() && false == Helper::validateWellFormedUriString($subjectConfirmationData->getRecipient())) {
            throw new LightSamlValidationException('Recipient of SubjectConfirmationData must be a wellformed absolute URI.');
        }
        if (
            $subjectConfirmationData->getNotBeforeTimestamp()
            && $subjectConfirmationData->getNotOnOrAfterTimestamp()
            && $subjectConfirmationData->getNotBeforeTimestamp() >= $subjectConfirmationData->getNotOnOrAfterTimestamp()
        ) {
            throw new LightSamlValidationException('SubjectConfirmationData NotBefore MUST be less than NotOnOrAfter');
        }
    }
}
