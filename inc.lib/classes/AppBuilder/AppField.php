<?php

namespace AppBuilder;

use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoObjectParser;

/**
 * Class AppField
 *
 * Represents a field in an application with various properties that determine its behavior 
 * in operations like insert, edit, detail views, listing, and exporting.
 *
 * This class encapsulates the metadata of fields including their names, labels, data types, 
 * and various flags for operations they participate in (such as required fields and key fields).
 */
class AppField // NOSONAR
{
    /**
     * The name of the field.
     *
     * @var string
     */
    private $fieldName;

    /**
     * The label for the field, used for display purposes.
     *
     * @var string
     */
    private $fieldLabel;

    /**
     * Indicates whether the field should be included in insert operations.
     *
     * @var bool
     */
    private $includeInsert;

    /**
     * Indicates whether the field should be included in edit operations.
     *
     * @var bool
     */
    private $includeEdit;

    /**
     * Indicates whether the field should be included in detail views.
     *
     * @var bool
     */
    private $includeDetail;

    /**
     * Indicates whether the field should be included in list views.
     *
     * @var bool
     */
    private $includeList;

    /**
     * Indicates whether the field should be included in export operations.
     *
     * @var bool
     */
    private $includeExport;

    /**
     * Indicates if this field is a key field (e.g., primary key).
     *
     * @var bool
     */
    private $key;

    /**
     * Indicates whether this field is required for input.
     *
     * @var bool
     */
    private $required;

    /**
     * The type of element associated with this field (e.g., text, number).
     *
     * @var string
     */
    private $elementType;

    /**
     * The type of element to use for filtering data associated with this field.
     *
     * @var string
     */
    private $filterElementType;

    /**
     * The data type of the field (e.g., string, integer).
     *
     * @var string
     */
    private $dataType;

    /**
     * A filter applied to the input data for this field.
     *
     * @var string
     */
    private $inputFilter;

    /**
     * Data associated with this field from a reference (e.g., lookup data).
     *
     * @var MagicObject
     */
    private $referenceData;

    /**
     * Filters to be applied to reference data.
     *
     * @var MagicObject
     */
    private $referenceFilter;

    /**
     * Additional output data associated with this field, which may be used in various contexts.
     *
     * @var array
     */
    private $additionalOutput;
    
    /**
     * Indicates whether multiple data can be handled.
     *
     * This variable is used to specify if the system should allow multiple data entries
     * or not. It is typically used in scenarios where more than one item can be processed
     * simultaneously.
     *
     * @var bool
     */
    private $multipleData = false;

    /**
     * Indicates whether multiple filters are allowed.
     *
     * This variable determines if the system allows applying more than one filter at a time
     * to the data. If set to true, multiple filters can be applied concurrently.
     *
     * @var bool
     */
    private $multipleFilter = false;

    /**
     * Constructor that initializes the AppField object based on the provided MagicObject.
     *
     * @param MagicObject $value A MagicObject containing field properties to initialize the instance.
     */
    public function __construct($value)
    {
        $this->fieldName = $value->getFieldName();
        $this->fieldLabel = $value->getFieldLabel();
        $this->includeInsert = $this->isTrue($value->getIncludeInsert());
        $this->includeEdit = $this->isTrue($value->getIncludeEdit());
        $this->includeDetail = $this->isTrue($value->getIncludeDetail());
        $this->includeList = $this->isTrue($value->getIncludeList());
        $this->includeExport = $this->isTrue($value->getIncludeExport());
        $this->key = $this->isTrue($value->getIsKey());
        $this->required = $this->isTrue($value->getIsInputRequired());
        $this->elementType = $value->getElementType();
        $this->dataType = $value->getDataType();
        $this->filterElementType = $value->getFilterElementType();
        $this->inputFilter = $value->getInputFilter();
        $this->referenceData = PicoObjectParser::parseJsonRecursive($value->getReferenceData());
        $this->referenceFilter = PicoObjectParser::parseJsonRecursive($value->getReferenceFilter());
        $this->additionalOutput = $value->getAdditionalOutput();
        $this->multipleData = $this->isTrue($value->getMultipleData());
        $this->multipleFilter = $this->isTrue($value->getMultipleFilter());
    }

    /**
     * Retrieves non-updateable columns for the specified entity.
     *
     * This method provides a list of fields that cannot be updated after creation, 
     * often for auditing purposes (e.g., timestamps, admin info).
     *
     * @param EntityInfo $entityInfo An EntityInfo instance containing metadata about the entity.
     * @return string[] An array of column names that are non-updateable.
     */
    public static function getNonupdatableColumns($entityInfo)
    {
        return [
            $entityInfo->getTimeCreate(),
            $entityInfo->getAdminCreate(),
            $entityInfo->getIpCreate()
        ];
    }

    /**
     * Determines if a given value represents a "true" state.
     *
     * This method checks various representations of true (like '1', true, etc.) 
     * and returns a boolean result.
     *
     * @param mixed $value The value to check for truthiness.
     * @return bool Returns true if the value is considered true, otherwise false.
     */
    private function isTrue($value)
    {
        return (is_string($value) && ($value == '1' || strtolower($value) == 'true')) || 
               ($value === 1 || $value === true);
    }

    /**
     * Retrieves the name of the field.
     *
     * @return string The name of the field.
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Retrieves the label for the field.
     *
     * @return string The field label, suitable for display to users.
     */
    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }

    /**
     * Checks if the field should be included in insert operations.
     *
     * @return bool True if the field is included in insert operations, otherwise false.
     */
    public function getIncludeInsert()
    {
        return $this->includeInsert;
    }

    /**
     * Checks if the field should be included in edit operations.
     *
     * @return bool True if the field is included in edit operations, otherwise false.
     */
    public function getIncludeEdit()
    {
        return $this->includeEdit;
    }

    /**
     * Checks if the field should be included in detail views.
     *
     * @return bool True if the field is included in detail views, otherwise false.
     */
    public function getIncludeDetail()
    {
        return $this->includeDetail;
    }

    /**
     * Checks if the field should be included in list views.
     *
     * @return bool True if the field is included in list views, otherwise false.
     */
    public function getIncludeList()
    {
        return $this->includeList;
    }

    /**
     * Checks if this field is a key field.
     *
     * @return bool True if this field is a key field, otherwise false.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Checks if the field is required for input.
     *
     * @return bool True if the field is required, otherwise false.
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Retrieves the type of element associated with this field.
     *
     * @return string The element type (e.g., text, number).
     */
    public function getElementType()
    {
        return $this->elementType;
    }

    /**
     * Retrieves the filter element type used for filtering data related to this field.
     *
     * @return string The filter element type.
     */
    public function getFilterElementType()
    {
        return $this->filterElementType;
    }

    /**
     * Retrieves the data type of the field.
     *
     * @return string The data type (e.g., string, integer).
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Retrieves the input filter applied to this field's data.
     *
     * @return string The input filter.
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * Retrieves the reference data associated with this field.
     *
     * @return MagicObject The reference data for lookups or related fields.
     */
    public function getReferenceData()
    {
        return $this->referenceData;
    }

    /**
     * Retrieves the reference filter applied to the reference data.
     *
     * @return MagicObject The filter applied to reference data.
     */
    public function getReferenceFilter()
    {
        return $this->referenceFilter;
    }

    /**
     * Retrieves any additional output data associated with this field.
     *
     * @return array Additional output data, used for various purposes.
     */
    public function getAdditionalOutput()
    {
        return $this->additionalOutput;
    }

    /**
     * Checks if the field should be included in export operations.
     *
     * @return bool True if the field is included in export operations, otherwise false.
     */
    public function getIncludeExport()
    {
        return $this->includeExport;
    }

    /**
     * Sets whether the field should be included in export operations.
     *
     * @param bool $includeExport Indicates if the field should be included in exports.
     * @return self Returns the current instance for method chaining.
     */
    public function setIncludeExport($includeExport)
    {
        $this->includeExport = $includeExport;
        return $this;
    }

    /**
     * Get the status of multiple data.
     *
     * This method returns the current setting for handling multiple data entries.
     * It indicates whether multiple data entries are allowed (true) or not (false).
     *
     * @return bool  The status of multiple data handling (true if allowed, false if not).
     */ 
    public function getMultipleData()
    {
        return $this->multipleData;
    }

    /**
     * Set the status of multiple data.
     *
     * This method sets whether multiple data entries should be allowed or not.
     * It is used to enable or disable the simultaneous processing of multiple data entries.
     *
     * @param bool $multipleData  The status to set (true to allow multiple data, false to disallow).
     *
     * @return self  The current instance for method chaining.
     */ 
    public function setMultipleData($multipleData)
    {
        $this->multipleData = $multipleData;

        return $this;
    }

    /**
     * Get the status of multiple filters.
     *
     * This method returns the current setting for applying multiple filters concurrently.
     * If set to true, multiple filters can be applied simultaneously to the data.
     *
     * @return bool  The status of multiple filter handling (true if allowed, false if not).
     */ 
    public function getMultipleFilter()
    {
        return $this->multipleFilter;
    }

    /**
     * Set the status of multiple filters.
     *
     * This method sets whether multiple filters can be applied to the data concurrently.
     * It is used to enable or disable the simultaneous application of filters.
     *
     * @param bool $multipleFilter  The status to set (true to allow multiple filters, false to disallow).
     *
     * @return self  The current instance for method chaining.
     */ 
    public function setMultipleFilter($multipleFilter)
    {
        $this->multipleFilter = $multipleFilter;

        return $this;
    }

}