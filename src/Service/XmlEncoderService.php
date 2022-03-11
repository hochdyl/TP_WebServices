<?php

namespace App\Service;

use SimpleXMLElement;

class XmlEncoderService
{
    /**
     * Convert an array to XML.
     */
    function encode($array): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<response/>');

        foreach ($array as $key => $value) {
            if (is_int($key)) {
                $key = "e";
            }
            if (is_array($value)) {
                $label = $xml->addChild($key);
                $this->encode($value, $label);
            }
            else {
                $xml->addChild($key, $value);
            }
        }

        return $xml;
    }
}