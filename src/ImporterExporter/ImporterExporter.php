<?php

namespace PragmaRX\Support\ImporterExporter;

use Storage;
use Exception;
use ArrayObject;

class ImporterExporter
{
    const CSV_DELIMITER = ',';
    const CSV_DELIMITER_EXCEL = ';';
    const CSV_ENCLOSURE = '"';
    const CSV_ESCAPE = '\\';
    const CSV_LINE_LENGTH = 65536;
    const SUPPORTED_TYPES = ['json', 'csv'];

    private $data = null;
    private $rawData = null;
    private $processData = null;
    private $profiles = null;

    public function __construct()
    {
        $this->profiles = new ArrayObject();
    }

    private function detectCsvConfig($data)
    {
        $delimiters = [
            ';',
            ',',
            "\t",
        ];

        foreach ($delimiters as $delimiter)
        {
            if (strpos($data, $delimiter) > 0)
            {
                break;
            }
        }

        $enclosures = [
            '"'.$delimiter.'"',
            "'".$delimiter."'",
            '', // must always be last!
        ];

        foreach ($enclosures as $enclosure)
        {
            if (empty($enclosure) || strpos($data, $enclosure) > 0)
            {
                break;
            }
        }

        return [$delimiter, $enclosure];
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    private function identifyType($fileName)
    {
        $type = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (! in_array($type, static::SUPPORTED_TYPES))
        {
            throw new \Exception('File type not supported, it must be one of those: '.join(', ', static::SUPPORTED_TYPES));
        }

        return $type;
    }

    public function import($fileName)
    {
        $type = $this->identifyType($fileName);

        if ($type == 'json')
        {
            return $this->importFromJson($fileName);
        }

        if ($type == 'csv')
        {
            return $this->importFromCsv($fileName);
        }
    }

    public function addProfile($id, $callback)
    {
        $this->profiles[ $id ] = $callback;
    }

    public function executeProfile($profileId)
    {
        $profile = $this->profiles[ $profileId ];

        $this->data = $profile($this, $this->data);

        return $this;
    }

    public function exportToCSV($filename, $data, $delimiter = self::CSV_DELIMITER, $enclosure = self::CSV_ENCLOSURE,
                                $arrayIndexAsHeader = false)
    {
        $fp = fopen($filename, 'w');
        $hasHeader = false;
        foreach ($data as $fields) {
            if ($arrayIndexAsHeader === true && !$hasHeader) {
                fputcsv($fp, array_keys($fields), $delimiter, $enclosure);
                $hasHeader = true;
            }
            fputcsv($fp, $fields, $delimiter, $enclosure);
        }
        fclose($fp);
    }

    public function exportToJSON($filename, $data)
    {
        $json = json_encode($data);

        return file_put_contents($filename, $json);
    }

    protected function getRawDataFromFile($file)
    {
        return Storage::disk('local')->get($file);
    }

    /**
     * @param $escape
     * @param $row
     * @param $delimiter
     * @param $enclosure
     * @return array
     */
    private function importCsv($row, $delimiter, $enclosure, $escape)
    {
        return str_getcsv($row, $delimiter, $enclosure, $escape);
    }

    public function importFromCSV($filename, $escape = self::CSV_ESCAPE)
    {
        $result = [];

        $data = $this->getRawDataFromFile($filename);
        $data = preg_split('/\n|\r\n?/', $data);

        if ($data)
        {
            list($delimiter, $enclosure) = $this->detectCsvConfig($data[0]);

            $data[0] = preg_replace('/[\x00-\x1F\x7F]/', '', $data[0]);

            $keys = $this->importCsv($data[0], $delimiter, $enclosure, $escape);

            foreach ($data as $row)
            {
                if (empty(trim($row)))
                {
                    continue;
                }

                if (mb_detect_encoding($row, null, true) !== 'UTF-8')
                {
                    $row = utf8_encode($row);
                }
                else
                {
                    $row = utf8_decode($row);
                }

                if ($row = $this->importCsv($row, $delimiter, $enclosure, $escape))
                {
                    try {
                        $result[] = array_combine($keys, $row);
                    }
                    catch (\Exception $exception)
                    {
                        echo "Error in file: ";
                        dd($row);
                    }
                }
            }
        }

        // Remove title row
        unset($result[0]);

        return $result;
    }

    public function importFromJson($file)
    {
        $data = $this->getRawDataFromFile($file);

        if ($data === false || empty($data)) {
            return false;
        }

        $data = str_replace(' ', '_SPACE_', $data);
        $data = preg_replace('/\s+/', '', $data);
        $data = preg_replace('/\t+/', '', $data);
        $data = str_replace('_SPACE_', ' ', $data);

        $data = json_decode($data, true);

        $jsonLastError = json_last_error();

        if ($jsonLastError !== JSON_ERROR_NONE) {
            $errorMsg = null;
            switch ($jsonLastError) {
                case JSON_ERROR_DEPTH:
                    $errorMsg = 'Maximum json depth reached.';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $errorMsg = 'Json CTRL char error';
                    break;
                case JSON_ERROR_SYNTAX:
                    $errorMsg = 'Json syntax error';
                    break;
                default:
                    $errorMsg = 'Json unknown error';
            }

            throw new Exception($errorMsg);
        }

        return $data;
    }
}
