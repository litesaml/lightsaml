<?php

namespace Tests\Credential;

use LightSaml\Credential\RsaPssKey;
use phpseclib3\Crypt\RSA;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseTestCase;

class RsaPssKeyTest extends BaseTestCase
{
    /** @return array<string, array{string}> */
    public static function hash_provider(): array
    {
        return [
            'SHA256' => ['SHA256'],
            'SHA384' => ['SHA384'],
            'SHA512' => ['SHA512'],
        ];
    }

    #[DataProvider('hash_provider')]
    public function test_verifies_rsa_pss_signature(string $hash): void
    {
        $digest = strtolower($hash);
        $private = RSA::createKey(2048);
        $data = 'the-canonicalized-signed-info';

        $signature = $private
            ->withPadding(RSA::SIGNATURE_PSS)
            ->withHash($digest)
            ->withMGFHash($digest)
            ->withSaltLength(strlen(hash($digest, '', true)))
            ->sign($data);

        $key = new RsaPssKey($hash);
        $key->loadKey((string) $private->getPublicKey());

        $this->assertSame(1, $key->verifySignature($data, $signature));
        $this->assertSame(0, $key->verifySignature($data, "\x00" . substr($signature, 1)));
        $this->assertSame(0, $key->verifySignature('tampered', $signature));
    }

    public function test_wrong_digest_does_not_verify(): void
    {
        $private = RSA::createKey(2048);
        $data = 'payload';

        $signature = $private
            ->withPadding(RSA::SIGNATURE_PSS)
            ->withHash('sha512')
            ->withMGFHash('sha512')
            ->withSaltLength(64)
            ->sign($data);

        $key = new RsaPssKey('SHA256');
        $key->loadKey((string) $private->getPublicKey());

        $this->assertSame(0, $key->verifySignature($data, $signature));
    }
}
