<?php

declare(strict_types=1);

namespace RectorRules;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class PhpDocToNativeTypeRector extends AbstractRector
{
    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly StaticTypeMapper $staticTypeMapper,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add native type hints from PHPDoc @return and @param annotations', [
            new CodeSample(
                '/** @return string */ public function getName() {}',
                'public function getName(): string {}'
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
        $changed = false;

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        // Add return type from PHPDoc if missing
        if ($node->returnType === null && $phpDocInfo->getReturnTagValue() !== null) {
            $returnType = $phpDocInfo->getReturnType();

            if (!$returnType instanceof MixedType) {
                $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
                if ($returnTypeNode !== null) {
                    $node->returnType = $returnTypeNode;
                    $changed = true;
                }
            }
        }

        // Add param types from PHPDoc if missing
        foreach ($node->params as $param) {
            if ($param->type !== null) {
                continue;
            }

            // Skip promoted constructor properties — their type placement is handled by other rules
            if ($param->flags !== 0) {
                continue;
            }

            if (!$param->var instanceof Variable || !is_string($param->var->name)) {
                continue;
            }

            $paramName = $param->var->name;
            $paramTagValue = $phpDocInfo->getParamTagValueByName($paramName);

            if ($paramTagValue === null) {
                continue;
            }

            $paramType = $phpDocInfo->getParamType($paramName);

            if ($paramType instanceof MixedType) {
                continue;
            }

            $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType, TypeKind::PARAM);
            if ($paramTypeNode !== null) {
                $param->type = $paramTypeNode;
                $changed = true;
            }
        }

        return $changed ? $node : null;
    }
}
