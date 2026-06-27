<?php

namespace LightSaml\Action;

interface DebugPrintTreeActionInterface
{
    /** @return array<string, array<mixed>> */
    public function debugPrintTree(): array;
}
