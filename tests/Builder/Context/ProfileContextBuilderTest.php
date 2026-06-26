<?php

namespace Tests\Builder\Context;

use LightSaml\Builder\Context\ProfileContextBuilder;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlBuildException;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Profile\Profiles;
use LightSaml\Provider\EntityDescriptor\FixedEntityDescriptorProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\BaseTestCase;

class ProfileContextBuilderTest extends BaseTestCase
{
    public function test_constructs_without_arguments()
    {
        new ProfileContextBuilder();
        $this->assertTrue(true);
    }

    public static function getters_setters_provider()
    {
        $factory = new Psr17Factory();
        return [
            [$factory->createServerRequest('GET', '/'), 'setRequest', 'getRequest'],
            [new FixedEntityDescriptorProvider(new EntityDescriptor()), 'setOwnEntityDescriptorProvider', 'getOwnEntityDescriptorProvider'],
            [Profiles::METADATA, 'setProfileId', 'getProfileId'],
            [ProfileContext::ROLE_IDP, 'setProfileRole', 'getProfileRole'],
        ];
    }

    #[DataProvider('getters_setters_provider')]
    public function test_getters_setters($value, $setter, $getter)
    {
        $builder = new ProfileContextBuilder();
        $builder->{$setter}($value);
        $this->assertSame($value, $builder->{$getter}());
    }

    public function test_build_throws_exception_when_request_not_set()
    {
        $this->expectExceptionMessage("HTTP Request not set");
        $this->expectException(LightSamlBuildException::class);
        $builder = new ProfileContextBuilder();

        $builder->build();
    }

    public function test_build_throws_exception_when_own_entity_descriptor_not_set()
    {
        $this->expectExceptionMessage("Own EntityDescriptor not set");
        $this->expectException(LightSamlBuildException::class);
        $factory = new Psr17Factory();
        $builder = new ProfileContextBuilder();
        $builder->setRequest($factory->createServerRequest('GET', '/'));

        $builder->build();
    }

    public function test_build_throws_exception_when_profile_id_not_set()
    {
        $this->expectExceptionMessage("ProfileID not set");
        $this->expectException(LightSamlBuildException::class);
        $factory = new Psr17Factory();
        $builder = new ProfileContextBuilder();
        $builder->setRequest($factory->createServerRequest('GET', '/'));
        $builder->setOwnEntityDescriptorProvider(new FixedEntityDescriptorProvider(new EntityDescriptor()));

        $builder->build();
    }

    public function test_build_throws_exception_when_profile_role_not_set()
    {
        $this->expectExceptionMessage("Profile role not set");
        $this->expectException(LightSamlBuildException::class);
        $factory = new Psr17Factory();
        $builder = new ProfileContextBuilder();
        $builder->setRequest($factory->createServerRequest('GET', '/'));
        $builder->setOwnEntityDescriptorProvider(new FixedEntityDescriptorProvider(new EntityDescriptor()));
        $builder->setProfileId(Profiles::METADATA);

        $builder->build();
    }

    public function test_build_returns_profile_context_with_request_injected()
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('POST', 'https://sp.example.com/acs');

        $builder = new ProfileContextBuilder();
        $builder->setRequest($request);
        $builder->setOwnEntityDescriptorProvider(new FixedEntityDescriptorProvider(new EntityDescriptor('http://sp.example.com')));
        $builder->setProfileId(Profiles::METADATA);
        $builder->setProfileRole(ProfileContext::ROLE_SP);

        $context = $builder->build();

        $this->assertInstanceOf(ProfileContext::class, $context);
        $this->assertSame($request, $context->getHttpRequest());
        $this->assertSame(Profiles::METADATA, $context->getProfileId());
        $this->assertSame(ProfileContext::ROLE_SP, $context->getOwnRole());
    }
}
