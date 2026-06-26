<?php

namespace LightSaml\Resolver\Session;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use LightSaml\State\Sso\SsoSessionState;
use LightSaml\State\Sso\SsoState;
use LightSaml\Store\Sso\SsoStateStoreInterface;

class SessionProcessor implements SessionProcessorInterface
{
    public function __construct(protected SsoStateStoreInterface $ssoStateStore, protected TimeProviderInterface $timeProvider)
    {
    }

    /**
     * @param Assertion[] $assertions
     */
    public function processAssertions(array $assertions, string $ownEntityId, string $partyEntityId): void
    {
        $now = $this->timeProvider->getDateTime()->setTimezone(new DateTimeZone('GMT'));
        $ssoState = $this->ssoStateStore->get();

        foreach ($assertions as $assertion) {
            if ($assertion instanceof Assertion) {
                if ($this->supportsSession($assertion)) {
                    $this->checkSession($ownEntityId, $partyEntityId, $ssoState, $assertion, $now);
                }
            } else {
                throw new InvalidArgumentException('Expected Assertion');
            }
        }

        $this->ssoStateStore->set($ssoState);
    }

    protected function supportsSession(Assertion $assertion): bool
    {
        return
            $assertion->hasBearerSubject()
            && null != $assertion->getSubject()
            && null != $assertion->getSubject()->getNameID()
        ;
    }

    protected function checkSession(string $ownEntityId, string $partyEntityId, SsoState $ssoState, Assertion $assertion, DateTime $now)
    {
        $sessions = $this->filterSessions($ssoState, $assertion, $ownEntityId, $partyEntityId);

        if ($sessions === []) {
            $this->createSession($ssoState, $assertion, $now, $ownEntityId, $partyEntityId);
        } else {
            $this->updateLastAuthn($sessions, $now);
        }
    }

    protected function createSession(SsoState $ssoState, Assertion $assertion, DateTime $now, string $ownEntityId, string $partyEntityId): \LightSaml\State\Sso\SsoSessionState
    {
        $ssoSession = new SsoSessionState();
        $ssoSession->setIdpEntityId($partyEntityId)
            ->setSpEntityId($ownEntityId)
            ->setNameId($assertion->getSubject()->getNameID()->getValue())
            ->setNameIdFormat($assertion->getSubject()->getNameID()->getFormat())
            ->setSessionIndex($assertion->getFirstAuthnStatement()->getSessionIndex())
            ->setSessionInstant($assertion->getFirstAuthnStatement()->getAuthnInstantDateTime())
            ->setFirstAuthOn($now)
            ->setLastAuthOn($now)
        ;
        $ssoState->addSsoSession($ssoSession);

        return $ssoSession;
    }

    /**
     * @param SsoSessionState[] $sessions
     */
    protected function updateLastAuthn(array $sessions, DateTime $now)
    {
        foreach ($sessions as $session) {
            $session->setLastAuthOn($now);
        }
    }

    /**
     *
     * @return SsoSessionState[]
     */
    protected function filterSessions(SsoState $ssoState, Assertion $assertion, string $ownEntityId, string $partyEntityId): array
    {
        return $ssoState->filter(
            $partyEntityId,
            $ownEntityId,
            $assertion->getSubject()->getNameID()->getValue(),
            $assertion->getSubject()->getNameID()->getFormat(),
            $assertion->getFirstAuthnStatement()->getSessionIndex()
        );
    }
}
