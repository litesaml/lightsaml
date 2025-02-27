<?php

namespace LightSaml\Model\Context;

use DOMDocument;

class SerializationContext
{
    protected DOMDocument $document;

    /**
     */
    public function __construct(?DOMDocument $document = null)
    {
        $this->document = $document ?: new DOMDocument();
    }

    public function setDocument(DOMDocument $document)
    {
        $this->document = $document;
    }

    /**
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }
}
