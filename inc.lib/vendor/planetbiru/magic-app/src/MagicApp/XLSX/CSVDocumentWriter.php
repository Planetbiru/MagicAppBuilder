<?php

namespace MagicApp\XLSX;

use MagicObject\Database\PicoPageData;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

class CSVDocumentWriter extends DocumentWriter
{
    private $temporaryFile;
    private $filePointer;
    /**
     * Write data
     *
     * @param PicoPageData $pageData Page data
     * @param string $fileName File name
     * @param string $sheetName Sheet name
     * @param string[] $headerFormat Data format
     * @param callable $writerFunction Writer function
     * @return self
     */
    public function write($pageData, $fileName, $sheetName, $headerFormat, $writerFunction)
    {
        $this->temporaryFile = tempnam(sys_get_temp_dir(), 'my-temp-file');
        $this->filePointer = fopen($this->temporaryFile, 'w');
        if(isset($headerFormat) && is_array($headerFormat) && is_callable($writerFunction))
        {
            $this->writeDataWithFormat($pageData, $headerFormat, $writerFunction);
        }
        else
        {
            $this->writeDataWithoutFormat($pageData);
        }
        header('Content-disposition: attachment; filename="'.$fileName.'"');
        header("Content-Type: text/csv");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');       
        readfile($this->temporaryFile);
        unlink($this->temporaryFile);
        return $this;
    }

    /**
     * Write data with format
     * @param PicoPageData $pageData Page data
     * @param string $sheetName Sheet name
     * @return void
     */
    private function writeDataWithoutFormat($pageData)
    {
        $idx = 0;
        if($this->noFetchData($pageData))
        {
            while($row = $pageData->fetch())
            {
                $keys = array_keys($row->valueArray());
                if($idx == 0)
                {
                    $this->writeHeader($keys);
                }
                $this->writeData($keys, $row);
                $idx++;
            }
        }
        else
        {
            foreach($pageData->getResult() as $row)
            {
                $keys = array_keys($row->valueArray());
                if($idx == 0)
                {
                    $this->writeHeader($keys);
                }
                $this->writeData($keys, $row);
                $idx++;
            }
        }
    }

    /**
     * Write header format
     * @param string[] $keys Data keys
     * @return self
     */
    private function writeHeader($keys)
    {
        $upperKeys = array();
        foreach($keys as $key)
        {
            $upperKeys[] = PicoStringUtil::camelToTitle($key);
        }
        fputcsv($this->filePointer, $upperKeys);
        return $this;
    }

    /**
     * Write header format
     * @param string[] $keys Data keys
     * @param MagicObject $row Data row
     * return self;
     */
    private function writeData($keys, $row)
    {
        $data = array();
        foreach($keys as $key)
        {
            $data[] = $row->get($key);
        }            
        fputcsv($this->filePointer, $data);
        return $this;
    }

    /**
     * Write data with format
     * @param PicoPageData $pageData Page data
     * @param string[] $headerFormat Data format
     * @param callable $writerFunction Writer function
     * @return void
     */
    private function writeDataWithFormat($pageData, $headerFormat, $writerFunction)
    {
        fputcsv($this->filePointer, array_keys($headerFormat));     
        $idx = 0;
        if($this->noFetchData($pageData))
        {
            while($row = $pageData->fetch())
            {
                $data = call_user_func($writerFunction, $idx, $row, $this->appLanguage);             
                $this->writeRow($data);
                $idx++;
            }
        }
        else
        {
            foreach($pageData->getResult() as $row)
            {
                $data = call_user_func($writerFunction, $idx, $row, $this->appLanguage);             
                $this->writeRow($data);
                $idx++;
            }
        }
    }

    /**
     * Write data
     * @param array $data
     * @return self
     */
    private function writeRow($data)
    {
        fputcsv($this->filePointer, $data);
        return $this;
    }
}