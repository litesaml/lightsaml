<?php

namespace Tests\Helper;

use LightSaml\Model\Metadata\KeyDescriptor;
use Tests\BaseTestCase;

class KeyDescriptorChecker
{
    public static function checkCertificateCN(BaseTestCase $test, ?string $use, string $cn, ?KeyDescriptor $kd = null): void
    {
        $test->assertNotNull($kd);
        $test->assertEquals($use, $kd->getUse());
        $test->assertNotEmpty($kd->getCertificate()->getData());
        $crt = openssl_x509_parse($kd->getCertificate()->toPem());
        $test->assertEquals($cn, $crt['subject']['CN']);
    }
}
