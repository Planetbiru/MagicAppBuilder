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
     * @param boolean $useTemporary Use temporary file
     * @return self
     */
    public function write($pageData, $fileName, $sheetName, $headerFormat, $writerFunction, $useTemporary = true)
    {
        if($useTemporary)
        {
            return $this->writeWithTemporary($pageData, $fileName, $headerFormat, $writerFunction);
        }
        else
        {
            return $this->writeWithoutTemporary($pageData, $fileName, $headerFormat, $writerFunction);
        }
    }
    
    /**
     * Download with temporary
     *
     * @param PicoPageData $pageData Page data
     * @param string $fileName File name
     * @param string[] $headerFormat Data format
     * @param callable $writerFunction Writer function
     * @return self
     */
    private function writeWithTemporary($pageData, $fileName, $headerFormat, $writerFunction)
    {
        $this->temporaryFile = tempnam(sys_get_temp_dir(), 'my-temp-file');
        $this->filePointer = fopen($this->temporaryFile, 'w');
        if(isset($headerFormat) && is_array($headerFormat) && is_callable($writerFunction))
        {
            $this->writeDataToFileWithFormat($pageData, $headerFormat, $writerFunction);
        }
        else
        {
            $this->writeDataToFileWithoutFormat($pageData);
        }
        header('Content-disposition: attachment; filename="'.$fileName.'"');
        header("Content-Type: text/csv");
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($this->temporaryFile));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');       
        readfile($this->temporaryFile);
        unlink($this->temporaryFile);
        return $this;
    }
    
    /**
     * Download without temporary
     *
     * @param PicoPageData $pageData Page data
     * @param string $fileName File name
     * @param string[] $headerFormat Data format
     * @param callable $writerFunction Writer function
     * @return self
     */
    private function writeWithoutTemporary($pageData, $fileName, $headerFormat, $writerFunction)
    {
        $this->temporaryFile = null;
        $this->filePointer = null;
        
        header('Content-disposition: attachment; filename="'.$fileName.'"');
        header("Content-Type: text/csv");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');       
        
        if(isset($headerFormat) && is_array($headerFormat) && is_callable($writerFunction))
        {
            $this->writeDataToObWithFormat($pageData, $headerFormat, $writerFunction);
        }
        else
        {
            $this->writeDataToObWithoutFormat($pageData);
        }
        return $this;
    }
    
    /**
     * Write data with format
     * @param PicoPageData $pageData Page data
     * @param string $sheetName Sheet name
     * @return void
     */
    private function writeDataToObWithoutFormat($pageData)
    {
        $idx = 0;
        if($this->noFetchData($pageData))
        {
            while($row = $pageData->fetch())
            {
                $keys = array_keys($row->valueArray());
                if($idx == 0)
                {
                    $this->writeHeaderToOb($keys);
                }
                $this->writeDataToFile($keys, $row);
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
                    $this->writeHeaderToOb($keys);
                }
                $this->writeDataToFile($keys, $row);
                $idx++;
            }
        }
    }

    /**
     * Write data with format
     * @param PicoPageData $pageData Page data
     * @param string $sheetName Sheet name
     * @return void
     */
    private function writeDataToFileWithoutFormat($pageData)
    {
        $idx = 0;
        if($this->noFetchData($pageData))
        {
            while($row = $pageData->fetch())
            {
                $keys = array_keys($row->valueArray());
                if($idx == 0)
                {
                    $this->writeHeaderToFile($keys);
                }
                $this->writeDataToFile($keys, $row);
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
                    $this->writeHeaderToFile($keys);
                }
                $this->writeDataToFile($keys, $row);
                $idx++;
            }
        }
    }

    /**
     * Write header format
     * @param string[] $keys Data keys
     * @return self
     */
    private function writeHeaderToFile($keys)
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
    private function writeDataToFile($keys, $row)
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
    private function writeDataToFileWithFormat($pageData, $headerFormat, $writerFunction)
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