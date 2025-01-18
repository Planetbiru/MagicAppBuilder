# Manual

## Request

### GET

MagicAppBuilder accepts GET requests with **URLEncoded** parameters. This is typically used for retrieving data or querying resources from the server. The request body is generally empty, and all parameters are passed as part of the URL.

### POST

MagicAppBuilder accepts POST requests in two formats:

1.  **URLEncoded** - When the request `Content-Type` header is set to `application/x-www-form-urlencoded`, the request body contains form data in URL-encoded format.
2.  **JSON** - When the request `Content-Type` header is set to `application/json`, the request body contains JSON-formatted data.

The choice of format should be specified in the `Content-Type` header of the request, and MagicAppBuilder will handle the data accordingly.

### PUT

MagicAppBuilder accepts PUT requests in two formats:

1.  **URLEncoded** - When the request `Content-Type` header is set to `application/x-www-form-urlencoded`, the request body contains form data in URL-encoded format.
2.  **JSON** - When the request `Content-Type` header is set to `application/json`, the request body contains JSON-formatted data.

Similar to POST requests, the `Content-Type` header will determine how the request data is parsed and processed.

## Response

MagicAppBuilder typically responds with **JSON** as the default format for API responses. This format is ideal for structured data and easy parsing in various programming environments.

However, there are exceptions based on the nature of the request:

1.  **Report Generation**  
    For certain report generation requests, MagicAppBuilder can send responses in either **CSV** or **XLSX** formats. This is commonly used for exporting large datasets or tabular information.
    
2.  **Other Content Types**  
    MagicAppBuilder can also return data in a variety of other formats, including but not limited to:
    
    -   **Plain Text** (`text/plain`)
    -   **HTML** (`text/html`)
    -   **XML** (`application/xml`)
    -   **Images** (e.g., **JPEG** (`image/jpeg`), **PNG** (`image/png`))
    -   **Audio** (e.g., **MP3** (`audio/mpeg`))

The format in which the response is sent is typically determined by the `Accept` header in the request or by specific request parameters.

## Input Types

1. **`string`**  
   Equivalent to `<input type="text">` in HTML. Used for simple text input. The corresponding data type is `string`.

2. **`multilinestring`**  
   Equivalent to `<textarea>` in HTML. Used for longer or multiline text input. The corresponding data type is `string`.

3. **`number`**  
   Equivalent to `<input type="number" step="any">` in HTML. Used for numeric input, allowing any decimal number. The corresponding data type is `float`.

4. **`integer`**  
   Equivalent to `<input type="number" step="1">` in HTML. Used for integer input, allowing only whole numbers. The corresponding data type is `integer`.

5. **`float`**  
   Equivalent to `<input type="number" step="any">` in HTML. Used for floating-point numeric input, allowing decimal numbers. The corresponding data type is `float`.

6. **`date`**  
   Equivalent to `<input type="date">` in HTML. Used for selecting a date (year, month, and day). The corresponding data type is `string`.

7. **`time`**  
   Equivalent to `<input type="time">` in HTML. Used for selecting a time (hours and minutes). The corresponding data type is `string`.

8. **`datetime`**  
   Equivalent to `<input type="datetime-local">` in HTML. Used for selecting both date and time in a local format. The corresponding data type is `string`.

9. **`color`**  
   Equivalent to `<input type="color">` in HTML. Used for selecting a color from a color picker. The corresponding data type is `string`.

10. **`year`**  
    Equivalent to `<input type="year">` in HTML. Used for selecting a year. The corresponding data type is `string`.

11. **`week`**  
    Equivalent to `<input type="week">` in HTML. Used for selecting a week within a given year. The corresponding data type is `string`.

12. **`select`**  
    Equivalent to `<select>` in HTML. This input type allows the user to select one option from a dropdown list. The corresponding data type is `string`, where the selected option is returned as a string.

13. **`multipleselect`**  
    Equivalent to `<select multiple>` in HTML. This input type allows the user to select multiple options from a dropdown list. The corresponding data type is `array`, where the selected options are returned as an array of strings.

14. **`checkbox`**  
    Equivalent to `<input type="checkbox">` in HTML. This input type allows the user to select a single option that represents a boolean state (checked or unchecked). The corresponding data type is `boolean`, where the value is `true` if the checkbox is checked, or `false` if it is unchecked.

## Data Types

1. **`string`**  
   A sequence of characters (text), which can include letters, numbers, and special symbols. Strings can be in any language.

2. **`integer`**  
   A whole number, either positive or negative. This data type represents whole values without fractions or decimals.

3. **`float`**  
   A number that can have a fractional part, represented with a decimal point. This data type allows for real numbers with more precision than integers.

4. **`boolean`**  
   A data type that can represent one of two values: `true` or `false`. It is typically used for binary decisions or flags.

5. **`array`**  
   An associative array of strings. The array can have string indices, with each index storing a value that is a string. Arrays are useful for organizing data in a structured way, where each element can be referenced by a unique key.
   
