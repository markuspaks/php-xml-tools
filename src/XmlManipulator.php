<?php

namespace XmlTools;

use DOMDocument;
use DOMElement;

class XmlManipulator
{
    protected DOMDocument $document;

    public function __construct($xml)
    {
        $this->document = $this->createDocument($xml);
    }

    public function getDocument(): DOMDocument
    {
        return $this->document;
    }

    public function removeNamespaces()
    {
        $this->document = $this->loopAllElementsForNewDocument($this->document, function (DOMDocument $newDocument, DOMElement $element) {

            $nodeName = $element->nodeName;
            $explodedNodeName = explode(':', $element->nodeName);
            if (count($explodedNodeName) === 2) {
                $nodeName = $explodedNodeName[1];
            }

            $nodeValue = $element->nodeValue;
            if ($element->childNodes->count() > 1) {
                $nodeValue = null;
            }

            return $newDocument->createElement($nodeName, $nodeValue);
        });
    }

    public function removeValues()
    {
        $this->document = $this->loopAllElementsForNewDocument($this->document, function (DOMDocument $newDocument, DOMElement $element) {
            return $newDocument->createElement($element->nodeName);
        });
    }

    protected function createDocument($xml): DOMDocument
    {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $doc->formatOutput = true;

        return $doc;
    }

    protected function loopAllElements($node, $callback)
    {
        if (!($node instanceof DOMElement) && !($node instanceof DOMDocument)) {
            return;
        }

        if ($node instanceof DOMElement) {
            $callback($node);
        }

        foreach ($node->childNodes as $childNode) {
            $this->loopAllElements($childNode, $callback);
        }
    }

    protected function loopAllElementsForNewDocument(DOMDocument $document, $callback): DOMDocument
    {
        $newDocument = new DOMDocument();
        $newDocument->formatOutput = true;
        $parentElementsMapping = [];
        $this->loopAllElements($document, function ($element) use ($newDocument, $callback, &$parentElementsMapping) {
            if ($parentElementsMapping === []) {
                $newParent = $newDocument;
            } else {
                $oldParent = $element->parentNode;
                $newParent = array_reduce($parentElementsMapping, function ($sum, $member) use ($oldParent) {
                    if ($member['old'] === $oldParent) {
                        return $member['new'];
                    }

                    return $sum;
                });
            }

            $newElement = $callback($newDocument, $element, $newParent);

            $newParent->appendChild($newElement);

            $parentElementsMapping[] = [
                'old' => $element,
                'new' => $newElement
            ];
        });

        return $newDocument;
    }
}
