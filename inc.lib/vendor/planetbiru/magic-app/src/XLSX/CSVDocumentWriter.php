<?php

namespace MagicApp\XLSX;

use MagicObject\Database\PicoPageData;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class CSVDocumentWriter
 *
 * Responsible for generating CSV documents from provided page data. This class extends 
 * DocumentWriter and implements methods to write data in CSV format, including 
 * handling headers and utilizing custom writer functions for formatted data output.
 */
class CSVDocumentWriter extends DocumentWriter
{
    private $temporaryFile;
    private $filePointer;

    /**
     * Write data to a CSV file and prepare for download
     *
     * @param PicoPageData $pageData Page data
     * @param string $fileName File name for the download
     * @param string $sheetName Sheet name (not used in CSV)
     * @param string[] $headerFormat Data format for headers
     * @param callable $writerFunction Function to write formatted data
     * @param boolean $useTemporary Flag to use temporary file
     * @return self The current instance, allowing method chaining
     */
    public function write($pageData, $fileName, $sheetName, $headerFormat, $writerFunction, $useTemporary = true)
    {
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        header("Content-Type: text/csv");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        if ($useTemporary) {
            $tempFile = tempnam(sys_get_temp_dir(), 'my-temp-file');
            $this->temporaryFile = $tempFile;
            $this->filePointer = fopen($this->temporaryFile, 'w');
            register_shutdown_function(function() use ($tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            });
        }
        
        if (isset($headerFormat) && is_array($headerFormat) && is_callable($writerFunction)) {
            $this->writeDataWithFormat($pageData, $headerFormat, $writerFunction);
        } else {
            $this->writeDataWithoutFormat($pageData);
        }

        if ($useTemporary) {
            header('Content-length: ' . filesize($this->temporaryFile));
            readfile($this->temporaryFile);
            unlink($this->temporaryFile);
        }

        return $this;
    }

    /**
     * Write data without specific format
     *
     * @param PicoPageData $pageData Page data
     * @return void
     */
    private function writeDataWithoutFormat($pageData)
    {
        $idx = 0;
        if ($this->noFetchData($pageData)) {
            while ($row = $pageData->fetch()) {
                $keys = array_keys($row->valueArray());
                if ($idx == 0) {
                    $this->writeHeader($keys);
                }
                $this->writeData($keys, $row);
                $idx++;
            }
        } else {
            foreach ($pageData->getResult() as $row) {
                $keys = array_keys($row->valueArray());
                if ($idx == 0) {
                    $this->writeHeader($keys);
                }
                $this->writeData($keys, $row);
                $idx++;
            }
        }
    }

    /**
     * Write header format to the CSV
     *
     * @param string[] $keys Data keys
     * @return self The current instance, allowing method chaining
     */
    private function writeHeader($keys)
    {
        $upperKeys = array();
        foreach ($keys as $key) {
            $upperKeys[] = PicoStringUtil::camelToTitle($key);
        }
        self::fputcsv($this->filePointer, $upperKeys);
        return $this;
    }

    /**
     * Write a single row of data to the CSV
     *
     * @param string[] $keys Data keys
     * @param MagicObject $row Data row
     * @return self The current instance, allowing method chaining
     */
    private function writeData($keys, $row)
    {
        $data = array();
        foreach ($keys as $key) {
            $data[] = $row->get($key);
        }            
        self::fputcsv($this->filePointer, $data);
        return $this;
    }

    /**
     * Write data using the provided format
     *
     * @param PicoPageData $pageData Page data
     * @param string[] $headerFormat Data format
     * @param callable $writerFunction Writer function for formatted data
     * @return void
     */
    private function writeDataWithFormat($pageData, $headerFormat, $writerFunction)
    {
        self::fputcsv($this->filePointer, array_keys($headerFormat));     
        $idx = 0;
        if ($this->noFetchData($pageData)) {
            while ($row = $pageData->fetch()) {
                $data = call_user_func($writerFunction, $idx, $row, $this->appLanguage);             
                $this->writeRow($data);
                $idx++;
            }
        } else {
            foreach ($pageData->getResult() as $row) {
                $data = call_user_func($writerFunction, $idx, $row, $this->appLanguage);             
                $this->writeRow($data);
                $idx++;
            }
        }
    }

    /**
     * Write a line of data to the CSV
     *
     * @param array $data Array of values to write
     * @return self The current instance, allowing method chaining
     */
    private function writeRow($data)
    {
        self::fputcsv($this->filePointer, $data);
        return $this;
    }
    
    /**
     * Custom implementation of fputcsv
     *
     * @param resource $handle File handle
     * @param mixed[] $fields Array of values to write
     * @param string $delimiter Field delimiter
     * @param string $enclosure Field enclosures
     * @param string $escape_char Escape enclosure characters in fields
     * @param string $record_separator Record separator
     * @return self The current instance, allowing method chaining
     */
    private function fputcsv($handle, $fields, $delimiter = ",", $enclosure = '"', $escape_char = "\\", $record_separator = "\r\n")
    {
        $result = [];
        foreach ($fields as $field) {
            $result[] = $enclosure . str_replace($enclosure, $escape_char . $enclosure, $field) . $enclosure;
        }
        if ($handle == null) {
            echo implode($delimiter, $result) . $record_separator;
        } else {
            fwrite($handle, implode($delimiter, $result) . $record_separator);
        }
        return $this;
    }
}
