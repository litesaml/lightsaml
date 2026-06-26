<?php

declare(strict_types=1);

namespace RectorRules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Removes @return TYPE when the native return type is ?TYPE
 * (PHPDoc is the non-nullable version of a nullable native type).
 */
final class RemoveOutdatedReturnTagRector extends AbstractRector
{
    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly StaticTypeMapper $staticTypeMapper,
        private readonly DocBlockUpdater $docBlockUpdater,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove @return TYPE when native type is already ?TYPE', [
            new CodeSample(
                '/** @return StatusCode */ public function get(): ?StatusCode {}',
                'public function get(): ?StatusCode {}'
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        assert($node instanceof ClassMethod);

        if ($node->returnType === null) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnTagValue = $phpDocInfo->getReturnTagValue();

        if ($returnTagValue === null) {
            return null;
        }

        // Keep tags that have a description (e.g. "@return bool True if...")
        if ($returnTagValue->description !== '') {
            return null;
        }

        $nativeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->returnType);
        $docType = $phpDocInfo->getReturnType();

        if ($docType instanceof MixedType) {
            return null;
        }

        // Remove tag when PHPDoc type == native type minus null
        // e.g. @return StatusCode with native ?StatusCode
        $nativeWithoutNull = TypeCombinator::removeNull($nativeType);
        if ($nativeWithoutNull->equals($docType)) {
            $phpDocInfo->removeByName('@return');
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

            return $node;
        }

        return null;
    }
}
