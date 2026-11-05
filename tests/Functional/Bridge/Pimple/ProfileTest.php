<?php

namespace Tests\Functional\Bridge\Pimple;

use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMXPath;
use LightSaml\Bridge\Pimple\Container\BuildContainer;
use LightSaml\Bridge\Pimple\Container\Factory\CredentialContainerProvider;
use LightSaml\Bridge\Pimple\Container\Factory\OwnContainerProvider;
use LightSaml\Bridge\Pimple\Container\Factory\PartyContainerProvider;
use LightSaml\Bridge\Pimple\Container\Factory\ProviderContainerProvider;
use LightSaml\Bridge\Pimple\Container\Factory\ServiceContainerProvider;
use LightSaml\Bridge\Pimple\Container\Factory\StoreContainerProvider;
use LightSaml\Bridge\Pimple\Container\Factory\SystemContainerProvider;
use LightSaml\Bridge\Pimple\Container\PartyContainer;
use LightSaml\Bridge\Pimple\Container\StoreContainer;
use LightSaml\Bridge\Pimple\Container\SystemContainer;
use LightSaml\Builder\EntityDescriptor\SimpleEntityDescriptorBuilder;
use LightSaml\Builder\Profile\Metadata\MetadataProfileBuilder;
use LightSaml\Builder\Profile\WebBrowserSso\Sp\SsoSpReceiveResponseProfileBuilder;
use LightSaml\Builder\Profile\WebBrowserSso\Sp\SsoSpSendAuthnRequestProfileBuilder;
use LightSaml\ClaimTypes;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use LightSaml\Error\LightSamlBuildException;
use LightSaml\Helper;
use LightSaml\Model\Metadata\EntitiesDescriptor;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Protocol\Response;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use LightSaml\SamlConstants;
use LightSaml\State\Request\RequestState;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use LightSaml\Store\EntityDescriptor\FixedEntityDescriptorStore;
use LightSaml\Store\Request\RequestStateArrayStore;
use Pimple\Container;
use Psr\EventDispatcher\EventDispatcherInterface;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Tests\BaseTestCase;
use Tests\Fixtures\Meta\TimeProviderMock;

class ProfileTest extends BaseTestCase
{
    public const OWN_ENTITY_ID = 'https://localhost/lightSAML/lightSAML';

    public function test_idp_stores()
    {
        $buildContainer = $this->getBuildContainer();
        $allIdpEntityDescriptors = $buildContainer->getPartyContainer()->getIdpEntityDescriptorStore()->all();

        $this->assertCount(4, $allIdpEntityDescriptors);
        $this->assertEquals('https://idp.testshib.org/idp/shibboleth', $allIdpEntityDescriptors[0]->getEntityID());
        $this->assertEquals('https://sp.testshib.org/shibboleth-sp', $allIdpEntityDescriptors[1]->getEntityID());
        $this->assertEquals('https://localhost/lightSAML/lightSAML-IDP', $allIdpEntityDescriptors[2]->getEntityID());
        $this->assertEquals('https://openidp.feide.no', $allIdpEntityDescriptors[3]->getEntityID());
    }

    public function test_metadata_profile()
    {
        $buildContainer = $this->getBuildContainer();

        $builder = new MetadataProfileBuilder($buildContainer);

        $context = $builder->buildContext();
        $action = $builder->buildAction();

        $action->execute($context);

        $this->assertNotNull($context->getHttpResponseContext()->getResponse());
        $xml = $context->getHttpResponseContext()->getResponse()->getContent();

        $root = new SimpleXMLElement($xml);

        $this->assertEquals('EntityDescriptor', $root->getName());
        $this->assertEquals('SPSSODescriptor', $root->SPSSODescriptor->getName());
        $this->assertEquals('https://localhost/lightsaml/lightSAML/web/sp/acs.php', $root->SPSSODescriptor->AssertionConsumerService['Location']);
    }

    public function test_send_authn_request_profile()
    {
        $buildContainer = $this->getBuildContainer();

        $idpEntityId = 'https://localhost/lightSAML/lightSAML-IDP';

        $builder = new SsoSpSendAuthnRequestProfileBuilder($buildContainer, $idpEntityId);
        $context = $builder->buildContext();
        $action = $builder->buildAction();

        $action->execute($context);

        $html = $context->getHttpResponseContext()->getResponse()->getContent();

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $code = $xpath->query('//input[@name="SAMLRequest"]')->item(0)->getAttribute('value');

        $xml = base64_decode($code, true);

        $root = new SimpleXMLElement($xml);
        $root->registerXPathNamespace('saml', SamlConstants::NS_ASSERTION);
        $this->assertEquals('AuthnRequest', $root->getName());
        $this->assertEquals(self::OWN_ENTITY_ID, (string) $root->children('saml', true)->Issuer);
        $this->assertEquals('https://localhost/lightsaml/lightSAML-IDP/web/idp/login.php', $root['Destination']);
        $this->assertEquals('Signature', $root->children('ds', true)->Signature->getName());
    }

    public function test_receive_response_profile()
    {
        $buildContainer = $this->getBuildContainer(
            '_1db06e4f91d3997b7ed3285a59f77028071db2dc5f',
            new TimeProviderMock(
                new DateTime('@' . Helper::parseSAMLTime('2015-11-22T15:37:14Z'), new DateTimeZone('UTC'))
            )
        );

        $builder = new SsoSpReceiveResponseProfileBuilder($buildContainer);

        $context = $builder->buildContext();
        $action = $builder->buildAction();

        $request = Request::create('https://localhost/lightsaml/lightSAML/web/sp/acs.php', 'POST', ['SAMLResponse' => $this->getSamlResponseCode()]);
        $context->getHttpRequestContext()->setRequest($request);

        $action->execute($context);

        /** @var Response $response */
        $response = $context->getInboundMessage();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(1, $response->getAllAssertions());
        $this->assertEquals('somebody@example.com', $response->getFirstAssertion()->getFirstAttributeStatement()
            ->getFirstAttributeByName(ClaimTypes::EMAIL_ADDRESS)->getFirstAttributeValue());
    }

    public function test_attribute_value_provider_throws_exception()
    {
        $this->expectExceptionMessage("Attribute value provider not set");
        $this->expectException(LightSamlBuildException::class);
        $buildContainer = $this->getBuildContainer();
        $buildContainer->getProviderContainer()->getAttributeValueProvider();
    }

    public function test_session_info_provider_throws_exception()
    {
        $this->expectExceptionMessage("Session info provider not set");
        $this->expectException(LightSamlBuildException::class);
        $buildContainer = $this->getBuildContainer();
        $buildContainer->getProviderContainer()->getSessionInfoProvider();
    }

    public function test_name_id_provider_throws_exception()
    {
        $this->expectExceptionMessage("Name ID provider not set");
        $this->expectException(LightSamlBuildException::class);
        $buildContainer = $this->getBuildContainer();
        $buildContainer->getProviderContainer()->getNameIdProvider();
    }

    public function test_session()
    {
        $buildContainer = $this->getBuildContainer();
        $this->assertInstanceOf(Session::class, $buildContainer->getSystemContainer()->getSession());
    }

    public function test_idp_entity_descriptor()
    {
        $pimple = new Container();
        $pimple->register(new PartyContainerProvider());
        $buildContainer = new BuildContainer($pimple);

        $this->assertInstanceOf(EntityDescriptorStoreInterface::class, $buildContainer->getPartyContainer()->getIdpEntityDescriptorStore());
    }

    public function test_sp_entity_descriptor()
    {
        $pimple = new Container();
        $pimple->register(new PartyContainerProvider());
        $buildContainer = new BuildContainer($pimple);

        $this->assertInstanceOf(EntityDescriptorStoreInterface::class, $buildContainer->getPartyContainer()->getSpEntityDescriptorStore());
    }

    private function getBuildContainer($inResponseTo = null, ?TimeProviderInterface $timeProvider = null)
    {
        $pimple = new Container();

        $pimple['event-dispatcher'] = $this->createMock(EventDispatcherInterface::class);

        $buildContainer = new BuildContainer($pimple);

        // OWN
        $ownCredential = new X509Credential(
            X509Certificate::fromFile(__DIR__ . '/../../../resources/web_saml.crt'),
            KeyHelper::createPrivateKey(__DIR__ . '/../../../resources/web_saml.key', null, true)
        );
        $ownCredential->setEntityId(self::OWN_ENTITY_ID);

        $ownEntityDescriptor = new SimpleEntityDescriptorBuilder(
            self::OWN_ENTITY_ID,
            'https://localhost/lightsaml/lightSAML/web/sp/acs.php',
            null,
            $ownCredential->getCertificate()
        );

        $buildContainer->getPimple()->register(new OwnContainerProvider(
            $ownEntityDescriptor,
            [$ownCredential]
        ));

        $systemContainerProvider = new SystemContainerProvider(
            true,
            $pimple['event-dispatcher']
        );

        // SYSTEM
        $buildContainer->getPimple()->register($systemContainerProvider);
        if ($timeProvider instanceof TimeProviderInterface) {
            $pimple[SystemContainer::TIME_PROVIDER] = function () use ($timeProvider) {
                return $timeProvider;
            };
        }

        // PARTY
        $buildContainer->getPimple()->register(new PartyContainerProvider());
        $pimple[PartyContainer::IDP_ENTITY_DESCRIPTOR] = function () {
            $idpProvider = new FixedEntityDescriptorStore();
            $idpProvider->add(
                EntitiesDescriptor::load(__DIR__ . '/../../../resources/testshib-providers.xml')
            );
            $idpProvider->add(
                EntityDescriptor::load(__DIR__ . '/../../../resources/localhost-lightsaml-lightsaml-idp.xml')
            );
            $idpProvider->add(
                EntityDescriptor::load(__DIR__ . '/../../../resources/openidp.feide.no.xml')
            );

            return $idpProvider;
        };

        // STORE
        $buildContainer->getPimple()->register(
            new StoreContainerProvider(
                $buildContainer->getSystemContainer()
            )
        );
        if ($inResponseTo) {
            $pimple[StoreContainer::REQUEST_STATE_STORE] = function () use ($inResponseTo) {
                $store = new RequestStateArrayStore();
                $store->set(new RequestState($inResponseTo));

                return $store;
            };
        }

        // PROVIDER
        $buildContainer->getPimple()->register(
            new ProviderContainerProvider()
        );

        // CREDENTIAL
        $buildContainer->getPimple()->register(
            new CredentialContainerProvider(
                $buildContainer->getPartyContainer(),
                $buildContainer->getOwnContainer()
            )
        );

        // SERVICE
        $buildContainer->getPimple()->register(
            new ServiceContainerProvider(
                $buildContainer->getCredentialContainer(),
                $buildContainer->getStoreContainer(),
                $buildContainer->getSystemContainer()
            )
        );

        return $buildContainer;
    }

    /**
     * @return string
     */
    private function getSamlResponseCode()
    {
        return 'PD94bWwgdmVyc2lvbj0iMS4wIj8+CjxzYW1scDpSZXNwb25zZSB4bWxuczpzYW1scD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOnByb3RvY29sIiBJRD0iX2I4YzNlZjQ0ZmZjZDNjMjc1OWYyYWU0ZTdjZGVjN2YwZGVhNGU0MGRkOCIgSW5SZXNwb25zZVRvPSJfMWRiMDZlNGY5MWQzOTk3YjdlZDMyODVhNTlmNzcwMjgwNzFkYjJkYzVmIiBWZXJzaW9uPSIyLjAiIElzc3VlSW5zdGFudD0iMjAxNS0xMS0yMlQxNTozODoxNFoiIERlc3RpbmF0aW9uPSJodHRwczovL2xvY2FsaG9zdC9saWdodHNhbWwvbGlnaHRTQU1ML3dlYi9zcC9hY3MucGhwIj48c2FtbDpJc3N1ZXIgeG1sbnM6c2FtbD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmFzc2VydGlvbiIgRm9ybWF0PSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6bmFtZWlkLWZvcm1hdDplbnRpdHkiPmh0dHBzOi8vbG9jYWxob3N0L2xpZ2h0U0FNTC9saWdodFNBTUwtSURQPC9zYW1sOklzc3Vlcj48ZHM6U2lnbmF0dXJlIHhtbG5zOmRzPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjIj4KICA8ZHM6U2lnbmVkSW5mbz48ZHM6Q2Fub25pY2FsaXphdGlvbk1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyIvPgogICAgPGRzOlNpZ25hdHVyZU1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyNyc2Etc2hhMSIvPgogIDxkczpSZWZlcmVuY2UgVVJJPSIjX2I4YzNlZjQ0ZmZjZDNjMjc1OWYyYWU0ZTdjZGVjN2YwZGVhNGU0MGRkOCI+PGRzOlRyYW5zZm9ybXM+PGRzOlRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyNlbnZlbG9wZWQtc2lnbmF0dXJlIi8+PGRzOlRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyIvPjwvZHM6VHJhbnNmb3Jtcz48ZHM6RGlnZXN0TWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3NoYTEiLz48ZHM6RGlnZXN0VmFsdWU+YUk2SFFEQ294d0prcGdNczZLNUVzUk5rOWxBPTwvZHM6RGlnZXN0VmFsdWU+PC9kczpSZWZlcmVuY2U+PC9kczpTaWduZWRJbmZvPjxkczpTaWduYXR1cmVWYWx1ZT5NTFprTnVBUkhEZU9KZ1lISkd3eERSM2lMUGxpcUJNdjFOME9ydHc5eEo4YXBQUEc1OU5jcW5xcDY5SnhOV3NZUENDMituUEZKOVUzY1lQRlY3algzaFQvNHNGZ2VVVkJKQ2pWWHpiMWFuQjI2UW51UEN1cmJjYjZqVUdTdE4zSkhEQTYyRHRrVm16NjRub2ZrZkVRSStNa1p3UWtjRC9qYUJZaXVRYm51c1NVTG1yeXBMUVN6MU9ibHR0bTBaV2J6T3l4TDFCdDRZNHdRQ2ZOR3RiQSswcE9QZ1VIby9zeDlCRExmTVdidkpVQ2p6RW1uK20zd1NocHllU0NXbGZLdHR0bUNhcFBvY0NWWXNGa1YxejhaUmFkWHY0ZG1nc3Vic2E2VVhCSjc2UXRLSzNYR1VqQ2JKQnkyUWo2RWJhOGNxUDhWWEZhV0NqYUNTQU5wOEFpRFE9PTwvZHM6U2lnbmF0dXJlVmFsdWU+CjxkczpLZXlJbmZvPjxkczpYNTA5RGF0YT48ZHM6WDUwOUNlcnRpZmljYXRlPk1JSUR5akNDQXJLZ0F3SUJBZ0lKQUpOT0Z1UWQ3MjdjTUEwR0NTcUdTSWIzRFFFQkJRVUFNRXd4Q3pBSkJnTlZCQVlUQWxKVE1SRXdEd1lEVlFRSUV3aENaV3huY21Ga1pURVNNQkFHQTFVRUNoTUpUR2xuYUhSVFFVMU1NUll3RkFZRFZRUURFdzFzYVdkb2RITmhiV3d1WTI5dE1CNFhEVEUxTURreE16RTVNREUwTUZvWERUSTFNRGt4TURFNU1ERTBNRm93VERFTE1Ba0dBMVVFQmhNQ1VsTXhFVEFQQmdOVkJBZ1RDRUpsYkdkeVlXUmxNUkl3RUFZRFZRUUtFd2xNYVdkb2RGTkJUVXd4RmpBVUJnTlZCQU1URFd4cFoyaDBjMkZ0YkM1amIyMHdnZ0VpTUEwR0NTcUdTSWIzRFFFQkFRVUFBNElCRHdBd2dnRUtBb0lCQVFDN3BVS09QTXlFMm9TY0hMUEdKRlRlcEs5ajFIMDNlL3MvV25PTnc4WndZQmFCSVlJUXVYNnVFOGpGUGREMHVRU2FZcE93NWg1VGdxNnhCVjdtMmtQTzUzaHM4Z0VHV1JiQ2RDdHhpOUVNSndJT1lyK2lzRzBOK0R2VjlLeWJKZjZ0cWNNNTBQaUZqVk50Zng4SXViTXBBS0NicXVhcWRMYUhIMHJnUDFoYmduR201WVpreUVLNHM4eHVMVURTNnFMN043YS9lejJaazQ1dTNMM3FGY3VuY1BJNUJUbkpnNmZxbHlwRGhDRE9CSTVMancxMEhtZ1pIUElYek9oRVBWVityWDJpSGhGNFY5dnpFb2VJVUFCWVhRVk5SUk5IcFBkVnNLNmlUVGt5dmJyR0ovdHYzb0ZaaE5PU0wwS3V5K1E5bmxFOWZFRnF5VXlkSjY3dnNYcVpBZ01CQUFHamdhNHdnYXN3SFFZRFZSME9CQllFRkhQVDZFeTFxZ3hNek1JdDJkM09XdXd6ZlBTVU1Id0dBMVVkSXdSMU1IT0FGSFBUNkV5MXFneE16TUl0MmQzT1d1d3pmUFNVb1ZDa1RqQk1NUXN3Q1FZRFZRUUdFd0pTVXpFUk1BOEdBMVVFQ0JNSVFtVnNaM0poWkdVeEVqQVFCZ05WQkFvVENVeHBaMmgwVTBGTlRERVdNQlFHQTFVRUF4TU5iR2xuYUhSellXMXNMbU52YllJSkFKTk9GdVFkNzI3Y01Bd0dBMVVkRXdRRk1BTUJBZjh3RFFZSktvWklodmNOQVFFRkJRQURnZ0VCQUhrSHR3SkJvZU9odnIwNk0wTWlrS2M5OXplNlRxQUd2ZitRa2dGb1Yxc1dHQWgzTktjQVIrWFNsZksrc1FXckhHa2lpYTVoV0tnQVBNTVVia0xQOURGV2tqYksyNDFpc0NaWkQvTHZBMWFuYlYrN1BpZG4rc3daNWRSN3luWDJ2ajBrRlliK1ZzR1BrYXZOY2o4Uk4vRGR1aE4vVG1pNXNRQWxXaGF3MDZVQWVFcVh0RmVMYlRnTGZmQmFqN1BtUjBJWWp2VFpBMFgyRmRSdTBHWFJ4bjd6Z2hqcHZTcTludVdhM3BHYmZkVnRMNkdJa3dZVVBjRHpqcjRPZUdYTm1JWmUvd01Dbno2VkdaWStMVWd6aS80REFDNlYzT2pNdWhkcVMvMitvMStDWEN3TjA4Q0lIUVY2K0FVQmVuRVZhd01zaWFkTEJneDNrRmU1aVhyWVJNQT08L2RzOlg1MDlDZXJ0aWZpY2F0ZT48L2RzOlg1MDlEYXRhPjwvZHM6S2V5SW5mbz48L2RzOlNpZ25hdHVyZT48c2FtbHA6U3RhdHVzPjxzYW1scDpTdGF0dXNDb2RlIFZhbHVlPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6c3RhdHVzOlN1Y2Nlc3MiLz48L3NhbWxwOlN0YXR1cz48c2FtbDpFbmNyeXB0ZWRBc3NlcnRpb24geG1sbnM6c2FtbD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmFzc2VydGlvbiI+PHhlbmM6RW5jcnlwdGVkRGF0YSB4bWxuczp4ZW5jPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyMiIHhtbG5zOmRzaWc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiIFR5cGU9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI0VsZW1lbnQiPjx4ZW5jOkVuY3J5cHRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyNhZXMxMjgtY2JjIi8+PGRzaWc6S2V5SW5mbyB4bWxuczpkc2lnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjIj48eGVuYzpFbmNyeXB0ZWRLZXk+PHhlbmM6RW5jcnlwdGlvbk1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMDQveG1sZW5jI3JzYS1vYWVwLW1nZjFwIi8+PHhlbmM6Q2lwaGVyRGF0YT48eGVuYzpDaXBoZXJWYWx1ZT5tVU9BRTJ1QVBRendiODZXSGhNZXVvOGVBSGtEKzVOVjVCUVN4MnhNS21EVXp1bWdZNkxiLzRxeVBvMTBsbGZ6WjFDdDl1bFNhMm5JL1crSVhoV3lQKzI2RXdnOCsrMS9ySHFSYUJ4WTlxSyt3citJVjNPRUE1QmxQVXJ0TXlYZnVaejlnd3FCd0E2bFlNcXpaMFVPYTFFQXdORmtjZWgyc2tKTmpIZFZ3aU0xdEZLdVFJd205NFlNUStHUEovZHJJWDhKM21mTFBwVUk0UnhBOGgwdlB6UW1sNDUyL01JT01ya1B5TTJvcXYwMXRVWmJVeHB1WWNrc1p5U29iQWlRdjVDYXQwTFBQczNiaEdPTDhFSS9vSUtzeHQwWjRKYk9lT0lXNDI1Rk5OMHRsblQ4c1lnbHlRY1BYQmRDZE1Zb3kxQ2xiVERKQVBGRUQ1OGRNMmNqS2c9PTwveGVuYzpDaXBoZXJWYWx1ZT48L3hlbmM6Q2lwaGVyRGF0YT48L3hlbmM6RW5jcnlwdGVkS2V5PjwvZHNpZzpLZXlJbmZvPgogICA8eGVuYzpDaXBoZXJEYXRhPgogICAgICA8eGVuYzpDaXBoZXJWYWx1ZT42TllpdTVoY3o1MGVuUFh1SWVEK3QxSUxxMW1CbVBnRjVlMm5Zb2FHTCtnQS9weDBmQ3M5ZElyLzB6MmRjellnVitROEUrYXJ0Wm1QYm1qLzNqUWNmSzA0eVpkVi8vRit0R05oUVgzdUpuYXFJVDlYYTl0Q2Jzb0xpZDJ0TW5xMmRnemxvU3VVZHgrTkxjSnlWclZhSzh6TXk3bU9VV21Gd1BXZTVvRjVkSXY3Vld4RjhqK2tUcDNEc2dWZmVLZlZobXBQQUUwYk9qeFdZSjFUSFlQS21CNEhLd245b0I4RFczUWdCNXVHaEFTMWUxcUUzSld0VjRzdjZvU3Y2T2hwT2hpdEQ3UFNHM2VNK0FJWVhra0RpcFdTTU9kaElPSVJiWDRoc1krNzN6YjNyUldmMVhSUVZWVGdmZVF5b2UwbkZ3OVBlemw4amZSelJJOVNrS2hBVW8rOHBtS0pVQjFlM005RmUrWDkrdTliNzdCZ1ROQUhaWGFLWko2dGZObGxUbmxsUG9TZkNqVENncE05c0RRcTdmVk13WW1Id1haSUZOYjFrMStZTzVsWk5mRG9ySmR3QW1EcGtVVHA2b0srMUk4TDdLNmp1VU9EcHpSVHBSTHdGNjRWOVVRb3lOcFVGRmcvQVB5NHUyQUZNM0RwSmhoVHczRGlNaHlzcGQ5cWI3V3h2VmczcXRrWHdwUDZtaVh3dDV6Lzc1UG1SK0dadVVxL1NwSGY1ckNMTlI3ZGJwdTdQeHJPOWJXSzlDVVNkSzBZcDhBMjl2djNpMkFaT1YzYmgwUkxWWGJXRE8vaC9pbHkycWlaU0l2UDlyM1lqYTBON2Z5RkpsTE5ZWjhFaS9KSnJSV042RzdDWFQ5Uy9GMi83VENTbi9VdnArS1hyMXVZeG1Ca1dTQmRaYW5oV093N0tML2NSektmTzdiUysyQ3RSNEdhZU1uQlYrc2pGeWJRK3NQT2JMTGhLcDBQR1Mxb0J4b3VuemZDdXphOUNCdVF6VWpDVUZPblpqamtpVlplWWh4UkJrTk9nSVZJOTFDaXZybGRzY1BkRDhRZWVJTzFyV3JEdUhnOHBFcGg3TTNBeHpGYUFIdGVvZmVCeXpRWTE2OWhBNkJOWGZrWEZqR3Jldlk0N0hzTlhIRExFQS9xbFdMVzNzdFlxa29Tc25YVGNhcjZuaW9GQjVPekdjU3dtcFZxMEFjSTRVODNDMnlEb1VJbGhyZFFTbHk1akpLOWJmRjFmbGYwU3BpWTREN1BhdHpCYVpBUHZ4ZkRISWZZUEFJY1Qxc0U1azI0RGtTbkNJdmMrdHFGR3ZKYVNvTzRRN1dmSHhkQytSUlhpaVBpWkxwd1d2WnF0U2FkQUhheWN3TzJsOUlGd3Z1MHhYWU12ZlcvVERVZC9OUWwyMmk4V2ZOSEE4VWtBSC9HaW9lMDhlckJmU3hMd2o2UUZObGFnY0IvbkJXQkw3ZEUzUmltdWo4d21TQnA3cUY1d0lCdjA0RkZuVzVZNVJObERXaFRrbzRlWlk2RXk0dVdiM2xac3l2L0xZZTBPUk9rS2Q0TUtySjFoMloxT0JpOWhSV0MwWHN2akVGYUpRVGZiVDI4cmdRK0VVYnE4TThTbVdHZFdGcittVUhBNUZWOXQ2VjM1WkZUZ2l0VXdOMWlKcEx5SGlLb3BlamNIRTdDdy9oQjg4YlM3WHZXTFJIRXY0V1U4T25DcVZDbTB3MVVMWm1BY2l1Yms2OG45TnlVU2lNZ3NueGNPSjA4bWs0SmowVklSaWkxRzk3NW5vTHlSNlFPdjh1aUQyUUJiM1lGdkpPMlhKTUlITEFkbTlvUlBURW5iVXFvRFdBdzFwYS9OMTNrcmx0Q1EwVzFPbVhyYWNxK1I2czNob0ZTdkRpTTBnUWlDbzhTZEZkT1FSQk1FWVcyTyt2WHBha3V2aEQxRU1ycGNaN0dQWUFnemN0eERHQzdYSEVWZHE3MGxhbzhraVkwdGhZYkRtWG5oS2k2UWpEM0hmVTQ0eGRpdnpLbnpUckkxZlFNQkVCZUFXZ0NUK1ZsWDU2VHJMSDRYUVNKYS8ycFhtelpUcjdkWnBDZ1NDenAvWkE1eUthei9MdXBHYjhMUVZMMkxuclhveTNTRFJvTXZ3WjAwUzZ4UnpmdjdBVHVOYy9lbVp6bzNhcnVteXA0bFhTSGJMYXF0aC90ay9FakxnU1RRK2ljUTF4ak9TTWZZZmlzbWZoSFpleFU3UmFsWGNqeVFvV043WWI2UWtXVTdZemdrUGtJYmcwVzVJekY5RFpKRkRUT0s1UDAyQm1tUmdOWXRIdW5xZFFyRXkyL3l2M3BWUThVUGpiMHhJbGVwTkNZWktKUHU2YmprQ3Q5eFlTSktSenNXWGpmblMwYnVkOWQ4dFN1Zk54K2pnNFNVcStiQlNzanFZTDBCb3cxZkNONVIzdmcwd2pTdG15U1NhR011bUZXMzVSRXF3bGhDMGhlL0FNVVZlNU4wQWRiaWdyUThDMlNob3F1bm85R2RWWjRoN2hRYkpsejdvckFoQlVXRWFSZEpDUU5iZnA3aDM5OEloaEJWQW9USGVmQUZ2ZUFFL1hKWG9aTG56NTJSYUdkOTdBNEFpV01CeVg0MVhSRDUvUzRxNVlWZVQ2NFhRRms0STl1Y0JnT0doKzJteSt1aEsvVUQyMG9yb0lpOEdXMU16cXRoQ01uMFNFRFUxUkUwU3lDc2crc0oxZlUxYWwvNkRkYURqZkZndnpJZmg2ZmpPYVJvY1JoQWtLZ3FKcXhicWdObm02WERYM0tteitZTlNJa2hVV3ZPbTFhNXVydEt6WHR2QjM5bkM3UDlUNlVOSmhsRnhlTEFmVlJrU2VMd2NGeTdnTjJRbittc2J4aE9JdUZ2Y0tsc0RjbXR1M0VjdzVFL1QxZWMxTngwWXNPTnVKbS9RQ1dYYk1za1MrSHpCQjZtRG56Z0haRHBOQUUwZ1pOWWdlZUM1M1ZKNXI3M3J3OTJtNWowRmRURkJ3SnlPMWlFaGZlM29ZaGtHVDVTMENFcjlobWZJbEwwOVdLYTVPbDZCTHlUT0lSK2JucndqVlczNmx0WTRCN0MrRDFUZ2lsVjgyYlpxdHU4WG1wM29rZmVXeUxHR1NyS2lDK0F6QTQ0SEFVU3VNUEpuNmZseDBNMU44MFpkMWo3dHZpczFoY0tQZDBteG5JcmpTV2p3aGgwamNpV2RmT3N0UE8xMDZQa3R2R2NTRktqOHNpMks5VUVwekJTdVNFMS84M1A2QXlGMnRPQ3VhMEE2VWF5ZlpHazNNWDN2VEdWajE1d2hkRzZGWkZjbUpOdlVzaXlmM1pmaGdXRWNDQUtDZTk4RjJva0ZiNmtKVFRWTXBJTE92RTErd1YrN1lNZFJ2L2IveXNNdkxhVDUyR0wyZ3ZyK1R3L3EvZk14alJFSlp0YnNSMmtjV2QxY1VRbWVQTms0UGVVVFF6b0M0bEdpVUVNYTBvVGdkTmFVVmY2TDJQbFRtY0tVbm04VURGejYrdkZndXk3UzZLSUFKUEFOcDl6REE5eUdYUFpzeXJnWFB1N1ppa21KbFoyeTc4UVFCR0V0VzZzakhMc1lkc2hFT3BENzlkclZqOG8waHNUYWR3MkdnMTdqQzJEVGtkK1FkdDEyRkxlbVVVK1ZFbEFRSGpYa2ZHNzR3bGdtUGo5bTFlYjJLRTVscDNsNWp3c2t0Y3NKMmMybnNneVVoVnRmMUJ3ay9XZFdveWtYWDZPLzhrb0I4eklTR093MkxnWXRyb1RRMm9wcjhhcWZMVlR1dkFuNHBHck1GaVFRSWY1bnRhRzE4VzltNUhyWkVUUEpleTNZbUZRK0lxQmFGOTE5WGVqdktkNjJzYS9kemFqQVpDSlNGbGloK05hdndYckVpQnFlTTdnTnNVT2YxZlNhTDBQV0NEdWtJS3dFdUNpZ1dBajk3K1dRZU1zeEViQkZGTWhNU1FmMHh6REdycTh0MDdjcWFWUlVDeWdRenJuaXRIeEhiYzZFMFN4TUNqKzcyOUJlUnhpUktKTEpqMFdiWjl0ZlR2QnVDZnFtVGhob3E4VFZ6UlUySjg5QW5lU256cmlrTjhzWmxrK0dCcnhqMXZwZWJFQndjd2IxQ0gzSnNmYU1uWnh4YVE5emkyVi9IN1FUL004NnppR2lqbFRLRUxYdnozK0ljRUNWMTliVjIyOGxGZFptemlOcHFLK2d4VnNSMThUL0ZodlFQVGpBam15WkNoeGMrQmFwSGhKZ3dVOHZlOXlBREhLQVd5SWxUVWpYNS9xaGpoaGJrWjNLWFFrQ2sxcG51MUJnVHJZZk5kWlRhbWdyUTIxTmNFUXcvbEFnUzZmd1NNV0lncHV2N3hDdGxxMWVVL1FISVJPclBwQjZoamJ5QzBuclZyL2d5bXlvRnNBWWR4dlNaaFdYbEltZTdJcFd1Y1RobDM5M016TktFd0pONDNYc3FQYzVBUWQ3U1RIK1ZWbXlVQVh3dFpvdVBrYmoyVVhsY2JVOHBTdmFYUFpVSEhpQjBRMTM1TVEyMDdGeHpSOFI2VkljWVIwVC93bmw0YUxOVHlJL0RFWXRkWHBwcmFzK0laZ0UyYzZMTVVONk53S0txZUFRaHovSG8vWFMvZ1l3dUNERytZamZhOFVEK0l5N3pqL0RQNklaSmErdGtsVUxlNjFOeVZDMUU4RHM1Q0FVdE5kWndmemdxSHJPWWpST0pUWE5OM1JLZTRia1hBd0s0NzJvMkQ3L3lMUHJwVW5VSmJiQnZ1OEMwN3UrMjhyaFJId1pRZTJjK04rMERzcGhKQnBKdm82ZkVJNFpSeTdBWmFla0ZlZ2VVOEJaZkQvVXNvb09wRG9Lb0p2ME1kOXhMQmNQTUhNNDVRaEcweGhvRVlvUkVHZ00wVXBZaWliSnk4enBTdlkzTEpvMWJCM3RtNE5Gc0NMRnNhcXBCeWcrNjJqd1M1dUEvb1E2S0lTNU9YVnBDdzhDcVMvTWc3bGVDdkdNaVpCeEtSMWdlMWx5RHFQZDZ0TVJ3Z3BOalJnSDNkQTBJNCtjdENaT3FjY3g0TVd5WEZzL1phTXZmRUwrUUlRYmRhNFA0SmFUS2VrWDI3V1JUYVd2eXVXaXFUVjF5QXFpUS8wZ25WMjNUOE1jVUZDTTdRbFFlSHlxa0hjb0RQUDhVRURhQ2ZMR0p1clJGVW8vZjM2UTBVYjhDUXJzbEJ3aTlxNFNKMVlHcm9wMGlYaU9MUFAzRFI0cXhWSUplNjFMSUhLemNvTXpzR25Db2cxeW9JbVQzZkRkSGtmR3FoeHRjTFpkZG5zNFFyYmdKbmg5dTFSUjNTaFRQbjNEdVg0M1c5Y2VUOVVjSW1WWkpkTFVEeitEdTFmS3RZOXM2TXBnVmYzbGtZajBpODlzTGJiTFNGWEpvQ01QNXdhQmhPaXNXRlhZQ0JzV1FRd3o2b2d1dEd2VUJGUVZXN3YrMmNPRkxYZENYWXFzbW5zYldsSzVFSmtTc0pubmU1QVlGOE1Ma3hWR0dZSUhlTVRjTm1kd2x4UHorT3JPN3N1eTlNcVNHNHpSbG9ZTEswdGM1eWtOMzF5L2VrM2x1WFNjUlhnNDVhV0x3Q1hIMk5hMXFIWlFPRG5uVU9MUnJvcDJBbWpUb2JWVUxHbkhubnZsdmJldXJjRkhjZTdHaVpOWjNIc0NkbWpjRmhvbVBYRXA1blpvd2Ricnd1V1dFQmoyZW8wVk5ZRll5VXI5SndkUVRnS1RZOFVJTkxNamRYY3FRdXhiOHdzbldIR2h3TDV3M3ZYUENOQlBJclRxVkxuSVd5SUtkc0htVi9meU9zQ3E1YTlTY1YxditUS0k5M2ZqaW1FTjdXQ1RUTldRR2FSWS9wcVMrTUxkSUhvTGlmMklSRmo5ZzBBaEdLQTk0U3M1eDBsODR6SFRmVzRsMmw2MGJJa0lmYlY4cFN1NFlCVmVkRmJyZGxLQzNaWUVXaFpkZmFhSnZBM2ViMFBJWkIrWFZSOFprQ2pLclFNamYyZlBXb2hsSjVZUXcwOFFBUlg3SHRVaVlpUk0zNEtMUHdISmo4VzdTUzRKSlNIajRXTkNjd1hhT1hBK1hPRzQvOEUyaE1sNVExci9FVWw0YUxOTkJILzNSWnM2SUFPTEk4SGVuSkxIWjRDUDZUT29tOFg2YVdkZUVrdi9MSTV3dXU5MndyNnN2c3lLVlVMeDZTYzFjQWxtelBpNWViR3JHYUVCTFhiam1qT1BvNGFtQ3hIZ0JHMXlpNldXbDd4c2dTcDVEam1mL2xYQUNCZ0JtMmJ1TEdUNVRaSHdkOXp4NFNobXQxOVFXQzBkZWF6ZDFYaUpKRTR2Vnh0VjdURkh6SmRIWW8yczhhekNHeUMycGw3TnJheWZlUDFsNGhKUGJEM1I4OVpvSTZlcjFPanhqY2hPNWZwS3l2NitqR2FaR1JJcnZpWmxOS1VCbEtodE1qenFJRlgwTUt4OGRjS0Z1SkpTZUY4d0VHc1lKWTI2bVhjVUgvRFEvK2pkTDBsYnVYV3VXM3RzbXNhZE1ldWNMSmhweDZyVEN5QmFPSWEyN2lCMG9UQy92Y2hZcHMrTmJQaGs0SzFTdlVVZHRNcTN5dTJSRG0zQk14ZWd2cFRwTGJpaXdUSC9ycm5qY0VHRnJoVGpVUnIxQ2FpVWx0M2xmdHc3RGhWMnFPVWFRS3NvWlhIRXMxK09qM3A4Mk1aaDdXRmF3VHIwYlNMVWcyanh1eHE5NE1TNWs2WldHOG41cXR5bUNFaEc1S0k4bHFnWDdCTnFGMzZoQmxmQ2xSRmtyaUd0U3ZPTWgxUklNdEgrRUV6L1F0ejRpWndHTkZoeVA3bUd1NGxMT3g2ODQrdHF5dURtTDNoT1ZNQXBvTzNTbmNtLzhFQjBnVFNHTFJITHlqajMycDMzcnQrY1pjS1lUK0pVV0MvTCtQd3hSR1ZzcVdGbktvMzdFRHJZc2RYTXdXazBnRi9GZnpYOGptdnlKRlZHb0ZBcDh5c2ZOMGVWbWdWNzR5dlUwYXBuMkZQaW9aM2hmeTh4eFVmZE82SnZLeVF6OTZtY3NIdG1hSm9FNERMQlA2S1c2cXZqV3UrL005L0dSaTY4V2c0bHJOYW1IQi85RTQweEsrMkdLVmJuVXpiWXk5bTRsZUJiM2tiOGo0WmpJYVBMdnMrSXNDVUJEaUdrNXd3bnlMWEV4Q2llZkJzSE9uS01DbDdDNXJHNzVjcWlaRjFWYWE4dzRDUXRnK01qd29UZHhDbkU4MThDR2cyNDZwMmhyNjNQeCtVRUdyVUo0T2hnVXkwN0FlaGJ2VUJQam9VbEl3d2lQZDZWMDFOcFcxMUd6SS9DUC9qZVlFWGdJUnpXSGFvT2YvTzEreDEvMWhNUjRHQWJsK2VGVy82NDFpMi9mQUU0YlNFUHBuT3BrUE5GRGptTHgwMXphWEgxQkhBRVVRaU9rdUxNSm40M09DdmtsYld5WEdSU2EyRmMyOExOVVYzVDAwNUJRRy80WFZHQT09PC94ZW5jOkNpcGhlclZhbHVlPgogICA8L3hlbmM6Q2lwaGVyRGF0YT4KPC94ZW5jOkVuY3J5cHRlZERhdGE+PC9zYW1sOkVuY3J5cHRlZEFzc2VydGlvbj48L3NhbWxwOlJlc3BvbnNlPgo=';
    }
}
