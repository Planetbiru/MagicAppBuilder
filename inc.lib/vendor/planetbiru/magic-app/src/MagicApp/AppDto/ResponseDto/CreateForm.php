<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class CreateForm
 *
 * Represents the response structure for a list-based form in a UI. This class holds metadata
 * about the module that the form is associated with, such as the module's ID, name, and title,
 * as well as the status of the request (via the response code and message). It also contains
 * the main data structure (`CreateFormData`) that holds the filter and data controls for the list.
 *
 * The `CreateForm` class is used to encapsulate all necessary information for rendering a form 
 * that interacts with a list of data. It provides details about the module's context and
 * feedback about the request, as well as the controls and data required for filtering and 
 * displaying the list content in the UI.
 *
 * **Key Features**:
 * - Hold the metadata about the module (ID, name, and title).
 * - Provide the response status and message for request feedback.
 * - Contain the `CreateFormData` structure, which manages the list's filter and data controls.
 * - Allow easy access to the response details and the data form structure.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class CreateForm extends ToString
{
    /**
     * The namespace where the module is located, such as "/", "/admin", "/supervisor", etc.
     *
     * @var string
     */
    protected $namespace;
    
    /**
     * The ID of the module associated with the data.
     *
     * @var string
     */
    protected $moduleId;

    /**
     * The name of the module associated with the data.
     *
     * @var string
     */
    protected $moduleName;

    /**
     * The title of the module associated with the data.
     *
     * @var string
     */
    protected $moduleTitle;

    /**
     * The response code indicating the status of the request.
     *
     * @var string|null
     */
    protected $responseCode;

    /**
     * A message providing additional information about the response.
     *
     * @var string|null
     */
    protected $responseMessage;

    /**
     * The main data structure containing the list form.
     *
     * @var CreateFormData|null
     */
    protected $data;

    /**
     * Get the namespace where the module is located.
     *
     * @return string The namespace.
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the namespace where the module is located.
     *
     * @param string $namespace The namespace to set.
     * @return self The current instance for method chaining.
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the module ID associated with the data.
     *
     * @return string The module ID.
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * Set the module ID associated with the data.
     *
     * @param string $moduleId The module ID to set.
     * @return self The current instance for method chaining.
     */
    public function setModuleId($moduleId)
    {
        $this->moduleId = $moduleId;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the module name associated with the data.
     *
     * @return string The module name.
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Set the module name associated with the data.
     *
     * @param string $moduleName The module name to set.
     * @return self The current instance for method chaining.
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the module title associated with the data.
     *
     * @return string The module title.
     */
    public function getModuleTitle()
    {
        return $this->moduleTitle;
    }

    /**
     * Set the module title associated with the data.
     *
     * @param string $moduleTitle The module title to set.
     * @return self The current instance for method chaining.
     */
    public function setModuleTitle($moduleTitle)
    {
        $this->moduleTitle = $moduleTitle;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the response code indicating the status of the request.
     *
     * @return string|null The response code.
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set the response code indicating the status of the request.
     *
     * @param string|null $responseCode The response code to set.
     * @return self The current instance for method chaining.
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the response message providing additional information about the response.
     *
     * @return string|null The response message.
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Set the response message providing additional information about the response.
     *
     * @param string|null $responseMessage The response message to set.
     * @return self The current instance for method chaining.
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the main data structure containing the list form.
     *
     * @return CreateFormData|null The data structure for the form.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the main data structure containing the list form.
     *
     * @param CreateFormData|null $data The data structure to set.
     * @return self The current instance for method chaining.
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this; // Returns the current instance for method chaining.
    }
}
