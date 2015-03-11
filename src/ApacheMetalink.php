<?php

namespace Pnz\Metalink;

class ApacheMetalink {

    const PRIORITY_HTTP = 7;
    const PRIORITY_FTP = 3;
    const PRIORITY_BACKUP = 1;

    protected $metalink;
    protected $baseUrl = 'http://www.apache.org/dyn/closer.cgi?path=';
    protected $filername;
    protected $path = null;

    /**
     * @param string $path
     */
    public function __construct($path = null)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('Invalid PATH');
        }
        $this->path = $path;
        $this->metalink = new Metalink(basename($path));
        $this->initArchive();
    }

    /**
     * 
     */
    protected function initArchive()
    {
        $json = file_get_contents($this->getUrl());
        $data = json_decode($json, true);

        $this->path = $data['path_info'];

        // Adding HTTP mirrors
        foreach ($data['http'] as $httpUrl) {
            $this->metalink->addMirror($httpUrl. $this->path, ApacheMetalink::PRIORITY_HTTP);
        }

        // Adding FTP mirrors
        foreach ($data['ftp'] as $httpUrl) {
            $this->metalink->addMirror($httpUrl. $this->path, ApacheMetalink::PRIORITY_FTP );
        }

        // Adding Backup mirrors
        foreach ($data['backup'] as $backupUrl) {
            $this->metalink->addMirror($backupUrl . $this->path, ApacheMetalink::PRIORITY_BACKUP);
        }

        $this->initHash(reset($data['backup']));
    }

    /**
     * @param $mirror
     */
    protected function initHash($mirror)
    {
        $hash = file_get_contents($mirror . $this->path . '.md5');
        $hash = explode(' ', $hash);
        $hash = reset($hash);
        $this->metalink->addHash($hash, Metalink::HASH_MD5);
    }

    private function getUrl()
    {
        return $this->baseUrl . $this->path . '&asjson=1';
    }

    /**
     * @return string
     */
    public function getMetalink4XML()
    {
        return $this->metalink->buildMetalink4XML();

    }

}
