<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## 4.0.9 (2022-12-06)

### Documentation

* Change documentation URL.

## 4.0.8 (2022-11-26)

### Continuous Integrations

* Add script in composer for test, phpcs & phpstan use in CI.

## 4.0.7 (2022-11-26)

### Code Refactoring

* Remove .changelog and README.md from export.
* Remove Makefile.

## 4.0.6 (2022-11-26)

### Code Refactoring

* Development files won’t be added to git archive.

## 4.0.5 (2022-11-26)

### Documentation

* Move assets to .github directory.

## 4.0.4 (2022-11-26)

### Code Refactoring

* Introduce PSR-4 autoloading.

## 4.0.3 (2022-11-26)

### Documentation

* Add LICENSE document.

## 4.0.2 (2022-11-25)

### Continuous Integrations

* Automatic update of CHANGELOG on commit on master.

### Documentation

* Preparing CHANGELOG for automation.

## 4.0.1 (2022-08-05)

+ Change package name in banner.
+ Add PHPStan to CI.
+ Clean code with PHPStan.
+ Clean code by removing unnecessary backslash.

## 4.0.0 (2022-06-23)

+ Replace symfony/event-dispatcher dependency by psr/event-dispatcher.

## 3.0.3 (2022-05-29)

+ Change package description.

## 3.0.2 (2022-05-27)

+ Fix composer documentation settings.

## 3.0.1 (2022-05-27)

+ Fix Documentation.

## 3.0.0 (2022-05-27)

+ Move doc in dedicated repository.
+ Move resources files for tests in tests directory.
+ Drop deprecated files.
+ Move schemas in dedicated package.
+ Update composer metadata.
+ Update Readme.

## 2.3.4 (2022-05-27)

+ Fix LightSaml\Model\XmlDSig\SignatureXmlReader::validate() exception catching

## 2.3.3 (2022-03-24)

+ Fix return types in LightSaml\Context\AbstractContext & LightSaml\Meta\ParameterBag

## 2.3.2 (2022-03-02)

+ Fix param types in LightSaml\Model\Assertion\Conditions class

## 2.3.1 (2022-03-01)

+ Fix input id in SamlPostResponse

## 2.3.0 (2022-02-09)

+ Update to symfony packages 6.0

## 2.2.0 (2022-02-09)

+ Run tests by GitHub's actions.

## 2.1.0 (2021-04-07)

+ Update PHPUnit 8.4+

## 2.0.1 (2021-04-07)

+ Clean code with php-cs-fixer

## 2.0.0 (2021-01-20)

+ PHP 7.2+ & Symfony 5

## 1.0.2 (2014-07-09)

+ Logout Request Builder
+ Improve code metric
+ Support for formatted certificate in message XML
+ "KeyDescriptor" elment "use" attribute should be optional
+ Support for EntitiesDescriptor element
+ InResponseTo attribute optional for StatusResponse
+ InvalidArgumentException at LogoutRequest ->setNotOnOrAfter()
+ New method in Signature Validator for array of keys
+ New method EntitiesDescriptor::getByEntityId
+ Fix AuthnRequest send and Response receive bidnings and url
+ Logging of sent/received messages in Binding
+ NameIDPolicy made optional in AuthnRequest?
+ SignatureMethod element made optional
+ StatusCode missing from status
+ Optional constructor arguments
+ Support for IdpSsoDescriptor Attributes & NameIdFormat
