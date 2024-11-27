/**
     * Executes a database query based on the parameters and annotations from the caller function.
     *
     * This method uses reflection to retrieve the query string from the caller's docblock,
     * bind the parameters, and execute the query against the database.
     *
     * It analyzes the parameters and return type of the caller function, enabling dynamic query
     * execution tailored to the specified return type. Supported return types include:
     * - `void`: Returns null.
     * - `int` or `integer`: Returns the number of affected rows.
     * - `object` or `stdClass`: Returns a single result as an object.
     * - `stdClass[]`: Returns all results as an array of stdClass objects.
     * - `array`: Returns all results as an associative array.
     * - `string`: Returns the JSON-encoded results.
     * - `PDOStatement`: Returns the prepared statement for further operations if needed.
     * - `MagicObject` and its derived classes: If the return type is a class name or an array of class names,
     *   instances of that class will be created for each row fetched.
     *
     * @return mixed Returns the result based on the return type of the caller function:
     *               - null if the return type is void.
     *               - integer for the number of affected rows if the return type is int.
     *               - object for a single result if the return type is object.
     *               - an array of associative arrays for multiple results if the return type is array.
     *               - a JSON string if the return type is string.
     *               - instances of a specified class if the return type matches a class name.
     * 
     * @throws PDOException If there is an error executing the database query.
     * @throws InvalidAnnotationException If the annotations are invalid or cannot be parsed.
     * @throws InvalidReturnTypeException If the return type specified is invalid.
     */
    protected function executeNativeQuery() // NOSONAR
    {
        // Retrieve caller trace information
        $trace = debug_backtrace();

        // Get parameters from the caller function
        $callerParamValues = isset($trace[1]['args']) ? $trace[1]['args'] : array();
        
        // Get the name of the caller function and class
        $callerFunctionName = $trace[1]['function'];
        $callerClassName = $trace[1]['class'];

        // Use reflection to get annotations from the caller function
        $reflection = new ReflectionMethod($callerClassName, $callerFunctionName);
        $docComment = $reflection->getDocComment();

        // Get the query from the @query annotation
        preg_match('/@query\s*\("([^"]+)"\)/', $docComment, $matches);
        $queryString = $matches ? $matches[1] : '';
        
        $queryString = trim($queryString, " \r\n\t ");
        if(empty($queryString))
        {
            // Try reading the query in another way
            preg_match('/@query\s*\(\s*"(.*?)"\s*\)/s', $docComment, $matches);
            $queryString = $matches ? $matches[1] : '';
            if(empty($queryString))
            {
                throw new InvalidAnnotationException("No query found.\r\n".$docComment);
            }
        }

        // Get parameter information from the caller function
        $callerParams = $reflection->getParameters();

        // Get return type from the caller function
        preg_match('/@return\s+([^\s]+)/', $docComment, $matches);
        $returnType = $matches ? $matches[1] : 'void';
        
        // Trim return type
        $returnType = trim($returnType);
        
        // Change self to callerClassName
        if($returnType == "self[]")
        {
            $returnType = $callerClassName."[]";
        }
        else if($returnType == "self")
        {
            $returnType = $callerClassName;
        }

        $params = array();
        $pageable = null;
        $sortable = null;
        try {
            // Get database connection
            $pdo = $this->_database->getDatabaseConnection();
            
            // Replace array
            foreach ($callerParamValues as $index => $paramValue) {
                if($paramValue instanceof PicoPageable)
                {
                    $pageable = $paramValue;
                }
                else if($paramValue instanceof PicoSortable)
                {
                    $sortable = $paramValue;
                }
                else if (isset($callerParams[$index])) {
                    // Format parameter name according to the query
                    $paramName = $callerParams[$index]->getName();
                    if(is_array($paramValue))
                    {
                        $queryString = str_replace(":".$paramName, PicoDatabaseUtil::toList($paramValue, true, true), $queryString);
                    }
                }
            }

            if(isset($pageable) || isset($sortable))
            {
                $queryBuilder = new PicoDatabaseQueryBuilder($this->_database->getDatabaseType());
                $queryString = $queryBuilder->addPaginationAndSorting($queryString, $pageable, $sortable);
            }

            $stmt = $pdo->prepare($queryString);

            // Automatically bind each parameter
            foreach ($callerParamValues as $index => $paramValue) {
                if (isset($callerParams[$index])) {
                    if($paramValue instanceof PicoPageable || $paramValue instanceof PicoSortable)
                    {
                        // skip
                    }
                    else
                    {
                        // Format parameter name according to the query
                        $paramName = $callerParams[$index]->getName();
                        if(!is_array($paramValue))
                        {
                            $maped = $this->mapToPdoParamType($paramValue);
                            $paramType = $maped->type;
                            $paramValue = $maped->value;
                            $params[$paramName] = $paramValue;
                            $stmt->bindValue(":".$paramName, $paramValue, $paramType);
                        }
                    }
                }
            }
            
            // Send query to logger
            $debugFunction = $this->_database->getCallbackDebugQuery();
            if(isset($debugFunction) && is_callable($debugFunction))
            {
                call_user_func($debugFunction, PicoDatabaseUtil::getFinalQuery($stmt, $params));
            }

            // Execute the query
            $stmt->execute();

            if ($returnType == "void") {
                // Return null if the return type is void
                return null;
            }
            if ($returnType == "PDOStatement") {
                // Return the PDOStatement object
                return $stmt;
            } else if ($returnType == "int" || $returnType == "integer") {
                // Return the affected row count
                return $stmt->rowCount();
            } else if ($returnType == "object" || $returnType == "stdClass") {
                // Return one row as an object
                return $stmt->fetch(PDO::FETCH_OBJ);
            } else if ($returnType == "array") {
                // Return all rows as an associative array
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else if ($returnType == "string") {
                // Return the result as a JSON string
                return json_encode($stmt->fetchAll(PDO::FETCH_OBJ));
            } else {
                try {
                    // Check for array-type hinting in the return type                  
                    if (stripos($returnType, "[") !== false) {
                        $className = trim(explode("[", $returnType)[0]);      
                        if ($className == "stdClass") {
                            // Return all rows as stdClass objects
                            return $stmt->fetchAll(PDO::FETCH_OBJ);
                        } 
                        else if($className == 'MagicObject') {
                            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
                            $ret = array();
                            foreach ($result as $row) {
                                $ret[] = new MagicObject($row);
                            }
                            return $ret;                
                        }
                        else if (class_exists($className)) {
                            // Map result rows to the specified class
                            $obj = new $className();
                            if($obj instanceof MagicObject) {
                                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
                                foreach ($result as $row) {
                                    $ret[] = new $className($row);
                                }
                                return $ret;
                            }                              
                        }                    
                        throw new InvalidReturnTypeException("Invalid return type for $className");
                    } else {
                        // Return a single object of the specified class
                        $className = trim($returnType);
                        if($className == 'MagicObject') {
                            $row = $stmt->fetch(PDO::FETCH_OBJ);       
                            return new MagicObject($row);       
                        }
                        else if (class_exists($className)) {
                            $obj = new $className();
                            if($obj instanceof MagicObject) {
                                $row = $stmt->fetch(PDO::FETCH_OBJ);
                                return $obj->loadData($row);
                            }
                        }
                        throw new InvalidReturnTypeException("Invalid return type for $className");
                    }
                } catch (Exception $e) {
                    // Log the exception if the class is not found
                    throw new InvalidReturnTypeException("Invalid return type for $className");
                }
            }            
        } 
        catch (PDOException $e) 
        {
            // Handle database errors with logging
            throw new PDOException($e->getMessage(), $e->getCode(), $e);
        }
        return null;
    }