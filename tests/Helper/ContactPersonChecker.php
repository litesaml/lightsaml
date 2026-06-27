<?php

namespace Tests\Helper;

use LightSaml\Model\Metadata\ContactPerson;
use Tests\BaseTestCase;

class ContactPersonChecker
{
    public static function check(
        BaseTestCase $test,
        string $type,
        ?string $company,
        ?string $givenName,
        ?string $surName,
        ?string $email,
        ?string $phone,
        ?ContactPerson $contact = null
    ): void {
        $test->assertNotNull($contact);
        $test->assertEquals($type, $contact->getContactType());
        $test->assertEquals($company, $contact->getCompany());
        $test->assertEquals($givenName, $contact->getGivenName());
        $test->assertEquals($surName, $contact->getSurName());
        $test->assertEquals($email, $contact->getEmailAddress());
        $test->assertEquals($phone, $contact->getTelephoneNumber());
    }
}
