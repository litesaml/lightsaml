<?php

namespace LightSaml\Validator\Model\Xsd;

use LightSaml\Error\LightSamlXmlException;
use LiteSaml\Error;
use LiteSaml\Schema;
use LiteSaml\UnexpectedSchemaException;

class XsdValidator
{
    /**
     * @param string $xml
     *
     * @return XsdError[]
     */
    public function validateProtocol($xml)
    {
        return $this->validate($xml, 'saml-schema-protocol-2.0.xsd');
    }

    /**
     * @param string $xml
     *
     * @return XsdError[]
     */
    public function validateMetadata($xml)
    {
        return $this->validate($xml, 'saml-schema-metadata-2.0.xsd');
    }

    /**
     * @param string $xml
     * @param string $schema
     *
     * @return XsdError[]
     */
    private function validate($xml, $schema)
    {
        try {
            $errorBag = Schema::validate($xml, $schema);

            return array_map(function (Error $error) {
                $level = match ($error->level) {
                    LIBXML_ERR_FATAL => XsdError::FATAL,
                    LIBXML_ERR_ERROR => XsdError::ERROR,
                    LIBXML_ERR_WARNING => XsdError::WARNING,
                    default => 'Unknown',
                };

                return new XsdError($level, $error->code, $error->message, $error->line, $error->column);
            }, $errorBag->getErrors());
        } catch (UnexpectedSchemaException $e) {
            throw new LightSamlXmlException($e->getMessage());
        }
    }
}
