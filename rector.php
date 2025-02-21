<?php

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests'
    ])
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class,
        InlineConstructorDefaultToPropertyRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        StaticDataProviderClassMethodRector::class,
    ])
    ->withSets([
        PHPUnitSetList::PHPUNIT_100,
        LevelSetList::UP_TO_PHP_81,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    )->withSkip([
        RestoreDefaultNullToNullableTypePropertyRector::class,
        NullToStrictStringFuncCallArgRector::class,
        ClosureToArrowFunctionRector::class,
    ]);
