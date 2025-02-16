<?php

namespace MagicApp\XLSX;

use MagicObject\Database\PicoPageData;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class XLSXDocumentWriter
 *
 * This class is responsible for writing data to an XLSX document. It handles
 * both formatted and unformatted data and manages the creation of the sheet
 * headers and rows based on the provided data.
 */
class XLSXDocumentWriter extends DocumentWriter
{
    /**
     * Write data to an XLSX file
     *
     * @param PicoPageData $pageData Page data to write
     * @param string $fileName File name for the output
     * @param string $sheetName Name of the sheet to create
     * @param array $headerFormat Data format for the header
     * @param callable $writerFunction Function to format data
     * @param bool $useTemporary Whether to use a temporary file
     * @return self The current instance, allowing method chaining
     */
    public function write($pageData, $fileName, $sheetName, $headerFormat, $writerFunction, $useTemporary = true)
    {
        $writer = new XLSXWriter();

        if (isset($headerFormat) && is_array($headerFormat) && is_callable($writerFunction)) {
            $writer = $this->writeDataWithFormat($writer, $pageData, $sheetName, $headerFormat, $writerFunction);
        } else {
            $writer = $this->writeDataWithoutFormat($writer, $pageData, $sheetName);
        }

        // Set headers for file download
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $writer->writeToStdOut();
        return $this;
    }

    /**
     * Write data without specific formatting
     *
     * @param XLSXWriter $writer XLSX writer instance
     * @param PicoPageData $pageData Page data to write
     * @param string $sheetName Name of the sheet
     * @return XLSXWriter
     */
    private function writeDataWithoutFormat($writer, $pageData, $sheetName)
    {
        $idx = 0;
        if ($this->noFetchData($pageData)) {
            while ($row = $pageData->fetch()) {
                $keys = array_keys($row->valueArray());
                if ($idx === 0) {
                    $writer = $this->writeHeader($writer, $sheetName, $keys);
                }
                $writer = $this->writeData($writer, $sheetName, $keys, $row);
                $idx++;
            }
        } else {
            foreach ($pageData->getResult() as $row) {
                $keys = array_keys($row->valueArray());
                if ($idx === 0) {
                    $writer = $this->writeHeader($writer, $sheetName, $keys);
                }
                $writer = $this->writeData($writer, $sheetName, $keys, $row);
                $idx++;
            }
        }
        return $writer;
    }

    /**
     * Write the header row to the XLSX sheet
     *
     * @param XLSXWriter $writer XLSX writer instance
     * @param string $sheetName Name of the sheet
     * @param array $keys Data keys for the header
     * @return XLSXWriter
     */
    private function writeHeader($writer, $sheetName, $keys)
    {
        foreach ($keys as $key) {
            $this->headerFormat[PicoStringUtil::camelToTitle($key)] = XLSXDataType::TYPE_STRING;
        }
        $writer->writeSheetHeader($sheetName, $this->headerFormat);
        return $writer;
    }

    /**
     * Write a data row to the XLSX sheet
     *
     * @param XLSXWriter $writer XLSX writer instance
     * @param string $sheetName Name of the sheet
     * @param array $keys Data keys
     * @param MagicObject $row Data row to write
     * @return XLSXWriter
     */
    private function writeData($writer, $sheetName, $keys, $row)
    {
        $data = [];
        foreach ($keys as $key) {
            $data[] = $row->get($key);
        }
        $writer->writeSheetRow($sheetName, $data);
        return $writer;
    }

    /**
     * Write data with specific formatting
     *
     * @param XLSXWriter $writer XLSX writer instance
     * @param PicoPageData $pageData Page data to write
     * @param string $sheetName Name of the sheet
     * @param array $headerFormat Data format for the header
     * @param callable $writerFunction Function to format data
     * @return XLSXWriter
     */
    private function writeDataWithFormat($writer, $pageData, $sheetName, $headerFormat, $writerFunction)
    {
        foreach ($headerFormat as $key => $value) {
            if ($value instanceof XLSXDataType) {
                $headerFormat[$key] = $value->toString();
            }
        }
        $this->headerFormat = $headerFormat;
        $writer->writeSheetHeader($sheetName, $this->headerFormat);

        $idx = 0;
        if ($this->noFetchData($pageData)) {
            while ($row = $pageData->fetch()) {
                $data = call_user_func($writerFunction, $idx, $row);
                $writer->writeSheetRow($sheetName, $data);
                $idx++;
            }
        } else {
            foreach ($pageData->getResult() as $row) {
                $data = call_user_func($writerFunction, $idx, $row);
                $writer->writeSheetRow($sheetName, $data);
                $idx++;
            }
        }
        return $writer;
    }
}
