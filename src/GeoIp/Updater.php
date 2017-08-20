<?php

namespace PragmaRX\Support\GeoIp;

class Updater
{
    const GEOLITE2_URL_BASE = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City';

    protected $databaseFileGzipped;

    protected $databaseFile;

    protected $md5File;

    protected $messages = [];

    /**
     * Add a message.
     *
     * @param $string
     */
    private function addMessage($string)
    {
        $this->messages[] = $string;
    }

    protected function databaseIsUpdated($geoDbFileUrl, $geoDbMd5Url, $destinationPath)
    {
        $destinationGeoDbFile = $this->removeGzipExtension($destinationPath . DIRECTORY_SEPARATOR . basename($geoDbFileUrl));

        $this->md5File = $this->getHTTPFile($geoDbMd5Url, $destinationPath . DIRECTORY_SEPARATOR);

        if (! file_exists($destinationGeoDbFile)) {
            return false;
        }

        if ($updated = file_get_contents($this->md5File) == md5_file($destinationGeoDbFile)) {
            $this->addMessage('Database is already updated.');
        }

        return $updated;
    }

    /**
     * Download gzipped database, unzip and check md5.
     *
     * @param $destinationPath
     * @param $geoDbUrl
     * @return bool
     */
    protected function downloadGzipped($destinationPath, $geoDbUrl)
    {
        if (! $this->databaseFileGzipped = $this->getHTTPFile($geoDbUrl, ($destination = $destinationPath . DIRECTORY_SEPARATOR))) {
            $this->addMessage("Unable to download file {$geoDbUrl} to {$destination}.");
        }

        $this->databaseFile = $this->dezipGzFile($destinationPath . DIRECTORY_SEPARATOR . basename($geoDbUrl));

        return $this->md5Match();
    }

    private function getDbFileName($geoDbUrl)
    {
        return $geoDbUrl ?: static::GEOLITE2_URL_BASE . '.mmdb.gz';
    }

    private function getMd5FileName($geoDbMd5Url)
    {
        return $geoDbMd5Url ?: static::GEOLITE2_URL_BASE . '.md5';
    }

    /**
     * Get messages.
     *
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Make directory.
     *
     * @param $destinationPath
     * @return bool
     */
    protected function makeDir($destinationPath)
    {
        return file_exists($destinationPath) || mkdir($destinationPath, 0770, true);
    }

    /**
     * Compare MD5s.
     *
     * @return bool
     */
    private function md5Match()
    {
        if (! $match = md5_file($this->databaseFile) == file_get_contents($this->md5File)) {
            $this->addMessage("MD5 is not matching for {$this->databaseFile} and {$this->md5File}.");

            return false;
        }

        $this->addMessage("Database successfully downloaded to {$this->databaseFile}.");

        return true;
    }

    /**
     * Remove .gzip extension from file.
     *
     * @param $filePath
     * @return mixed
     */
    protected function removeGzipExtension($filePath)
    {
        return str_replace('.gz', '', $filePath);
    }

    /**
     * Download and update GeoIp database.
     *
     * @param $destinationPath
     * @param null $geoDbUrl
     * @param null $geoDbMd5Url
     * @return bool
     */
    public function updateGeoIpFiles($destinationPath, $geoDbUrl = null, $geoDbMd5Url = null)
    {
        if ($this->databaseIsUpdated($geoDbUrl = $this->getDbFileName($geoDbUrl), $this->getMd5FileName($geoDbMd5Url), $destinationPath)) {
            return true;
        }

        if ($this->downloadGzipped($destinationPath, $geoDbUrl)) {
            return true;
        }

        $this->addMessage("Unknown error downloading {$geoDbUrl}.");

        return false;
    }

    /**
     * Read url to file.
     *
     * @param $uri
     * @param $destinationPath
     * @return bool|string
     */
    protected function getHTTPFile($uri, $destinationPath)
    {
        set_time_limit(360);

        if (! $this->makeDir($destinationPath)) {
            return false;
        }

        $fileWriteName = $destinationPath . basename($uri);

        if (($fileRead = @fopen($uri,"rb")) === false || ($fileWrite = @fopen($fileWriteName, 'wb')) === false) {
            $this->addMessage("Unable to open {$uri} (read) or {$fileWriteName} (write).");

            return false;
        }

        while(! feof($fileRead))
        {
            $content = @fread($fileRead, 1024*16);

            $success = fwrite($fileWrite, $content);

            if ($success === false) {
                $this->addMessage("Error downloading file {$uri} to {$fileWriteName}.");

                return false;
            }
        }

        fclose($fileWrite);

        fclose($fileRead);

        return $fileWriteName;
    }

    /**
     * Extract gzip file.
     *
     * @param $filePath
     * @return bool|mixed
     */
    protected function dezipGzFile($filePath)
    {
        $buffer_size = 8192; // read 8kb at a time

        $out_file_name = $this->removeGzipExtension($filePath);

        $fileRead = gzopen($filePath, 'rb');

        $fileWrite = fopen($out_file_name, 'wb');

        if ($fileRead === false || $fileWrite === false) {
            $this->addMessage("Unable to extract gzip file {$filePath} to {$out_file_name}.");

            return false;
        }

        while(!gzeof($fileRead)) {
            $success = fwrite($fileWrite, gzread($fileRead, $buffer_size));

            if ($success === false) {
                $this->addMessage("Error degzipping file {$filePath} to {$out_file_name}.");

                return false;
            }
        }

        // Files are done, close files
        fclose($fileWrite);

        gzclose($fileRead);

        return $out_file_name;
    }
}
