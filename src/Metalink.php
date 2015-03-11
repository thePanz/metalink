<?php

namespace Pnz\Metalink;

class Metalink {

    const HASH_MD5 = 'md5';
    const HASH_SHA1 = 'sha-1';

    protected $mirrors = array();
    protected $hashes = array();
    protected $filename = null;
    protected $description = '';
    protected $published = null;

    /**
     * @param $filename
     */
    function __construct($filename)
    {
        $this->filename = $filename;
        $this->published = new \DateTime();
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }


    /**
     * @param $type
     * @return bool
     */
    protected static function isValidHashType($type) {
        return null !== $type && in_array($type, array(Metalink::HASH_MD5, Metalink::HASH_SHA1));
    }

    /**
     * @param string $type
     * @return array|string
     */
    public function getHashes($type = null)
    {
        if (Metalink::isValidHashType($type) && isset($this->hashes[$type])) {
            return $this->hashes[$type];
        }
        return $this->hashes;
    }

    /**
     * @param string $hash
     * @param string $type
     * @return $this
     */
    public function addHash($hash, $type)
    {
        if (Metalink::isValidHashType($type)) {
            $this->hashes[$type] = $hash;
            return $this;
        }
        else {
            throw new \InvalidArgumentException('Invalid HASH type:' . $type);
        }
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param \DateTime $published
     */
    public function setPublished(\DateTime $published)
    {
        $this->published = $published;
    }

    /**
     * @return array
     */
    public function getMirrors()
    {
        return $this->mirrors;
    }

    /**
     * @param string $mirror
     * @param float $priority
     * @return $this
     */
    public function addMirror($mirror, $priority = 1)
    {
        $this->mirrors[] = array(
            'url' => $mirror,
            'priority' => $priority,
        );
        return $this;
    }

    /**
     * @return string
     */
    public function buildMetalink4XML()
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString(' ');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('metalink');
        $xml->writeAttribute('xmlns', 'urn:ietf:params:xml:ns:metalink');

        // <published />
        $xml->writeElement('published', $this->getPublished()->format(\DateTime::RFC3339));

        // <file>
        $xml->startElement('file');
        $xml->writeAttribute('name', $this->getFileName());

        // <description />
        $xml->writeElement('description', $this->getDescription());

        // <hash />
        foreach ($this->getHashes() as $type => $hash) {
            $xml->startElement('hash');
            $xml->writeAttribute('type', $type);
            $xml->text($hash);
            $xml->endElement();
        }

        // <url />
        foreach ($this->getMirrors() as $mirror) {
            $xml->startElement('url');
            $xml->writeAttribute('priority', $mirror['priority']);
            $xml->text($mirror['url']);
            $xml->endElement();
        }

        // </file>
        $xml->endElement();

        // End <metalink>
        $xml->endElement();
        $xml->endDocument();
        return $xml->outputMemory();
    }
}
