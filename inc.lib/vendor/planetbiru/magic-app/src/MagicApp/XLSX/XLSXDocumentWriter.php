<?php

namespace MagicApp\XLSX;

use MagicApp\AppLanguage;
use MagicObject\Database\PicoPageData;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

class XLSXDocumentWriter extends DocumentWriter
{
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
        $writer = new XLSXWriter();
        if(isset($headerFormat) && is_array($headerFormat) && is_callable($writerFunction))
        {
            $writer = $this->writeDataWithFormat($writer, $pageData, $sheetName, $headerFormat, $writerFunction);
        }
        else
        {
            $writer = $this->writeDataWithoutFormat($writer, $pageData, $sheetName);
        }    
        header('Content-disposition: attachment; filename="'.$fileName.'"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $writer->writeToStdOut();
        return $this;
    }

    /**
     * Write data with format
     * @param XLSXWriter $writer XLSX writter
     * @param PicoPageData $pageData Page data
     * @param string $sheetName Sheet name
     * @return XLSXWriter XLSX writter
     */
    private function writeDataWithoutFormat($writer, $pageData, $sheetName)
    {
        $idx = 0;
        if($this->noFetchData($pageData))
        {
            while($row = $pageData->fetch())
            {
                $keys = array_keys($row->valueArray());
                if($idx == 0)
                {
                    $writer = $this->writeHeader($writer, $sheetName, $keys);
                }
                $writer = $this->writeData($writer, $sheetName, $keys, $row);
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
                    $writer = $this->writeHeader($writer, $sheetName, $keys);
                }
                $writer = $this->writeData($writer, $sheetName, $keys, $row);
                $idx++;
            }
        }
        return $writer;
    }

    /**
     * Write header format
     * @param XLSXWriter $writer XLSX writter
     * @param string $sheetName Sheet name
     * @param string[] $keys Data keys
     * @return XLSXWriter XLSX writter
     */
    private function writeHeader($writer, $sheetName, $keys)
    {
        foreach($keys as $key)
        {
            $this->headerFormat[PicoStringUtil::camelToTitle($key)] = XLSXDataType::TYPE_STRING;
        }
        $writer->writeSheetHeader($sheetName, $this->headerFormat);
        return $writer;
    }

    /**
     * Write header format
     * @param XLSXWriter $writer XLSX writter
     * @param string $sheetName Sheet name
     * @param string[] $keys Data keys
     * @param MagicObject $row Data row
     * @return XLSXWriter XLSX writter
     */
    private function writeData($writer, $sheetName, $keys, $row)
    {
        $data = array();
        foreach($keys as $key)
        {
            $data[] = $row->get($key);
        }            
        $writer->writeSheetRow($sheetName, $data);
        return $writer;
    }

    /**
     * Write data with format
     * @param XLSXWriter $writer XLSX writter
     * @param PicoPageData $pageData Page data
     * @param string $sheetName Sheet name
     * @param string[] $headerFormat Data format
     * @param callable $writerFunction Writer function
     * @return XLSXWriter XLSX writter
     */
    private function writeDataWithFormat($writer, $pageData, $sheetName, $headerFormat, $writerFunction)
    {
        foreach($headerFormat as $key=>$value)
        {
            if($value instanceof XLSXDataType)
            {
                $headerFormat[$key] = $value->toString();
            }
        }
        $this->headerFormat = $headerFormat;        
        $writer->writeSheetHeader($sheetName, $this->headerFormat);
        $idx = 0;
        if($this->noFetchData($pageData))
        {
            while($row = $pageData->fetch())
            {
                $data = call_user_func($writerFunction, $idx, $row, $this->appLanguage);             
                $writer->writeSheetRow($sheetName, $data);
                $idx++;
            }
        }
        else
        {
            foreach($pageData->getResult() as $row)
            {
                $data = call_user_func($writerFunction, $idx, $row, $this->appLanguage);             
                $writer->writeSheetRow($sheetName, $data);
                $idx++;
            }
        }
        return $writer;
    }
}