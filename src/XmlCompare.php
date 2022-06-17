<?php

namespace XmlTools;

class XmlCompare
{
    protected XmlManipulator $document1;
    protected XmlManipulator $document2;

    public function __construct($xml1, $xml2)
    {
        $this->document1 = new XmlManipulator($xml1);
        $this->document2 = new XmlManipulator($xml2);
    }

    public function getDocument1(): XmlManipulator
    {
        return $this->document1;
    }

    public function getDocument2(): XmlManipulator
    {
        return $this->document2;
    }

    public function removeNamespaces()
    {
        $this->document1->removeNamespaces();
        $this->document2->removeNamespaces();
    }

    public function removeValues($removeValues)
    {
        $this->document1->removeValues();
        $this->document2->removeValues();
    }

    public function diff()
    {
        $tempName1 = $this->writeTemp($this->document1);
        $tempName2 = $this->writeTemp($this->document2);

        $output = null;
        exec('diff ' . escapeshellarg($tempName1) . ' ' . escapeshellarg($tempName2), $output);

        return $output;
    }

    protected function writeTemp(XmlManipulator $document): string
    {
        $tempName1 = tempnam(sys_get_temp_dir(), 'xml');
        file_put_contents($tempName1, $document->getDocument()->saveXML());

        return $tempName1;
    }
}
