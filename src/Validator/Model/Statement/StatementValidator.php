<?php

namespace LightSaml\Validator\Model\Statement;

use LightSaml\Error\LightSamlValidationException;
use LightSaml\Helper;
use LightSaml\Model\Assertion\AbstractStatement;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Assertion\AttributeStatement;
use LightSaml\Model\Assertion\AuthnContext;
use LightSaml\Model\Assertion\AuthnStatement;

class StatementValidator implements StatementValidatorInterface
{
    /**
     * @throws LightSamlValidationException
     */
    public function validateStatement(AbstractStatement $statement): void
    {
        if ($statement instanceof AuthnStatement) {
            $this->validateAuthnStatement($statement);
        } elseif ($statement instanceof AttributeStatement) {
            $this->validateAttributeStatement($statement);
        } else {
            throw new LightSamlValidationException(sprintf("Unsupported Statement type '%s'", $statement::class));
        }
    }

    private function validateAuthnStatement(AuthnStatement $statement): void
    {
        if (false == $statement->getAuthnInstantTimestamp()) {
            throw new LightSamlValidationException('AuthnStatement MUST have an AuthnInstant attribute');
        }
        if (null !== $statement->getSessionIndex() && trim($statement->getSessionIndex()) === '') {
            throw new LightSamlValidationException('SessionIndex attribute of AuthnStatement must contain at least one non-whitespace character');
        }
        if ($statement->getSubjectLocality() instanceof \LightSaml\Model\Assertion\SubjectLocality) {
            if (null !== $statement->getSubjectLocality()->getAddress() && trim($statement->getSubjectLocality()->getAddress()) === '') {
                throw new LightSamlValidationException('Address attribute of SubjectLocality must contain at least one non-whitespace character');
            }
            if (null !== $statement->getSubjectLocality()->getDnsName() && trim($statement->getSubjectLocality()->getDnsName()) === '') {
                throw new LightSamlValidationException('DNSName attribute of SubjectLocality must contain at least one non-whitespace character');
            }
        }
        if (false == $statement->getAuthnContext()) {
            throw new LightSamlValidationException('AuthnStatement MUST have an AuthnContext element');
        }
        $this->validateAuthnContext($statement->getAuthnContext());
    }

    private function validateAuthnContext(AuthnContext $authnContext): void
    {
        if (
            false == $authnContext->getAuthnContextClassRef()
            && false == $authnContext->getAuthnContextDecl()
            && false == $authnContext->getAuthnContextDeclRef()
        ) {
            throw new LightSamlValidationException('AuthnContext element MUST contain at least one AuthnContextClassRef, AuthnContextDecl or AuthnContextDeclRef element');
        }

        if (
            $authnContext->getAuthnContextClassRef()
            && $authnContext->getAuthnContextDecl()
            && $authnContext->getAuthnContextDeclRef()
        ) {
            throw new LightSamlValidationException('AuthnContext MUST NOT contain more than two elements.');
        }

        if ($authnContext->getAuthnContextClassRef() && false == Helper::validateWellFormedUriString($authnContext->getAuthnContextClassRef())) {
            throw new LightSamlValidationException('AuthnContextClassRef has a value which is not a wellformed absolute uri');
        }
        if ($authnContext->getAuthnContextDeclRef() && false === Helper::validateWellFormedUriString($authnContext->getAuthnContextDeclRef())) {
            throw new LightSamlValidationException('AuthnContextDeclRef has a value which is not a wellformed absolute uri');
        }
    }

    private function validateAttributeStatement(AttributeStatement $statement): void
    {
        if (false == $statement->getAllAttributes()) {
            throw new LightSamlValidationException('AttributeStatement MUST contain at least one Attribute or EncryptedAttribute');
        }

        foreach ($statement->getAllAttributes() as $attribute) {
            $this->validateAttribute($attribute);
        }
    }

    /**
     * @throws LightSamlValidationException
     */
    private function validateAttribute(Attribute $attribute): void
    {
        if (trim($attribute->getName()) === '') {
            throw new LightSamlValidationException('Name attribute of Attribute element MUST contain at least one non-whitespace character');
        }
    }
}
