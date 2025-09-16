/**
 * Generates a random 32-character hexadecimal string.
 * This can be used as a cryptographic key or unique identifier.
 * @returns {string} A 32-character hexadecimal string.
 */
function generateRandomHex32() {
    let hex = '';
    // Loop 32 times to generate each hexadecimal character
    for (let i = 0; i < 32; i++) {
        // Generate a random number between 0 and 15, then convert it to a hexadecimal string
        hex += Math.floor(Math.random() * 16).toString(16);
    }
    return hex;
}

/**
 * Encrypts a given value using AES-256-CBC with HMAC-SHA256 for integrity.
 * The output is a Base64 encoded string containing IV, HMAC, and Ciphertext.
 * @param {*} value The value to be encrypted. It will be converted to a string.
 * @param {string} key The encryption key (UTF-8 string).
 * @param {boolean} [skipEncrypt=false] If true, the value is returned as plaintext without encryption.
 * @returns {string} The Base64 encoded encrypted string, or the plaintext if skipEncrypt is true.
 */
function encryptValue(value, key, skipEncrypt = false) {
    const plaintext = value.toString(); // Convert the input value to a string for encryption
    if (skipEncrypt) {
        return plaintext; // If encryption is skipped, return the plaintext directly
    }
    const iv = CryptoJS.lib.WordArray.random(16); // Generate a random 16-byte (128-bit) Initialization Vector (IV)
    const keyWordArray = CryptoJS.enc.Utf8.parse(key); // Parse the key string into a WordArray for CryptoJS

    // Encrypt the plaintext using AES-256-CBC mode with PKCS7 padding
    const encrypted = CryptoJS.AES.encrypt(plaintext, keyWordArray, {
        iv: iv, // Use the generated IV
        mode: CryptoJS.mode.CBC, // Cipher Block Chaining mode
        padding: CryptoJS.pad.Pkcs7 // PKCS7 padding scheme
    });

    const ciphertext = encrypted.ciphertext; // Extract the raw ciphertext WordArray

    // Calculate HMAC-SHA256 over the concatenation of ciphertext and IV for integrity verification
    const hmacInput = ciphertext.clone().concat(iv); // Create input for HMAC by concatenating ciphertext and IV
    const hmac = CryptoJS.HmacSHA256(hmacInput, keyWordArray); // Compute HMAC using the key

    // Concatenate IV (16 bytes), HMAC (32 bytes), and Ciphertext
    // This order is crucial for correct decryption and integrity check
    const result = iv.clone().concat(hmac).concat(ciphertext);

    // Convert the final WordArray to a Base64 string for storage/transmission
    return CryptoJS.enc.Base64.stringify(result);
}

/**
 * Decrypts a Base64 encoded string that was encrypted using `encryptValue`.
 * It performs an integrity check using HMAC before decryption.
 * @param {string} encryptedBase64 The Base64 encoded string containing IV, HMAC, and Ciphertext.
 * @param {string} key The decryption key (UTF-8 string).
 * @returns {string|null} The decrypted plaintext string, or null if decryption fails (e.g., HMAC mismatch, invalid format).
 */
function decryptValue(encryptedBase64, key) {
    if (!encryptedBase64) return null; // Return null if the input is empty

    const keyWordArray = CryptoJS.enc.Utf8.parse(key); // Parse the key string into a WordArray
    const data = CryptoJS.enc.Base64.parse(encryptedBase64); // Parse the Base64 input back into a WordArray

    // Minimum expected size: 16 bytes (IV) + 32 bytes (HMAC) = 48 bytes
    if (data.sigBytes < 48) return null; // Return null if data is too short

    const ivSize = 16; // Size of the Initialization Vector in bytes
    const hmacSize = 32; // Size of the HMAC in bytes (SHA256 produces 32 bytes)

    // Extract IV, HMAC, and Ciphertext from the combined data WordArray
    const iv = CryptoJS.lib.WordArray.create(data.words.slice(0, ivSize / 4), ivSize);
    const hmac = CryptoJS.lib.WordArray.create(data.words.slice(ivSize / 4, (ivSize + hmacSize) / 4), hmacSize);
    const ciphertext = CryptoJS.lib.WordArray.create(data.words.slice((ivSize + hmacSize) / 4), data.sigBytes - ivSize - hmacSize);

    // Recalculate HMAC using the extracted ciphertext and IV to verify integrity
    const hmacInput = ciphertext.clone().concat(iv);
    const expectedHmac = CryptoJS.HmacSHA256(hmacInput, keyWordArray);

    // Compare the recalculated HMAC with the extracted HMAC
    // If they don't match, the data has been tampered with or the key is wrong
    if (CryptoJS.enc.Hex.stringify(expectedHmac) !== CryptoJS.enc.Hex.stringify(hmac)) {
        console.warn("HMAC mismatch"); // Log a warning for HMAC mismatch
        return null; // Return null on integrity check failure
    }

    // Decrypt the ciphertext using AES-256-CBC with the extracted IV and key
    const decrypted = CryptoJS.AES.decrypt({ ciphertext: ciphertext }, keyWordArray, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    });

    // Convert the decrypted WordArray to a UTF-8 string
    return decrypted.toString(CryptoJS.enc.Utf8);
}

/**
 * Recursively encrypts values within an object, array, or a primitive.
 * @param {*} obj The object, array, or primitive value to encrypt.
 * @param {string} key The encryption key.
 * @returns {*} The encrypted object, array, or string.
 */
function encryptObject(obj, key) {
    // If the value is a primitive (string, number, boolean), encrypt it directly
    if (typeof obj === 'string' || typeof obj === 'number' || typeof obj === 'boolean') {
        return encryptValue(obj, key);
    } else if (Array.isArray(obj)) {
        // If it's an array, recursively encrypt each item
        return obj.map(item => encryptObject(item, key));
    } else if (typeof obj === 'object' && obj !== null) {
        // If it's an object, create a new object and recursively encrypt its properties
        const result = {};
        for (let k in obj) {
            // Ensure to only process own properties to avoid issues with prototype chain
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                result[k] = encryptObject(obj[k], key);
            }
        }
        return result;
    }
    return obj; // Return the original value if it's not a recognized type for encryption
}

/**
 * Recursively encrypts values within an object/array and generates environment variable names
 * for the encrypted values, storing them in a mapping. The original values in the object/array
 * are replaced with placeholders referencing these environment variables.
 * @param {*} obj The object, array, or primitive value to process.
 * @param {string} key The encryption key.
 * @param {string} prefix A string prefix used to construct unique environment variable names.
 * @param {Object.<string, string>} mapping An object to store the generated environment variable names and their encrypted values.
 * @param {boolean} [skipEncrypt=false] If true, the values are stored as plaintext in the mapping, and placeholders are generated.
 * @returns {*} The modified object/array with placeholders, or a placeholder string if the input was a primitive.
 */
function encryptObjectEnv(obj, key, prefix, mapping, skipEncrypt = false) {
    // If the value is a primitive, encrypt it and create an environment variable entry
    if (typeof obj === 'string' || typeof obj === 'number' || typeof obj === 'boolean') {
        // Generate a clean environment variable name from the prefix
        const envName = `${prefix.replace(/[^A-Za-z0-9_]/g, '_').toUpperCase()}`;
        // Store the encrypted (or plaintext if skipEncrypt) value in the mapping
        mapping[envName] = encryptValue(obj, key, skipEncrypt);
        // Return a placeholder string that references the environment variable
        return `\${${envName}}`;
    } else if (Array.isArray(obj)) {
        // If it's an array, recursively process each item with an updated prefix
        return obj.map((item, idx) => encryptObjectEnv(item, key, `${prefix}_${idx}`, mapping, skipEncrypt));
    } else if (typeof obj === 'object' && obj !== null) {
        // If it's an object, create a new object and recursively process its properties
        const result = {};
        for (let k in obj) {
            // Ensure to only process own properties
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                result[k] = encryptObjectEnv(obj[k], key, `${prefix}_${k}`, mapping, skipEncrypt);
            }
        }
        return result;
    }
    return obj; // Return the original value if not a primitive, array, or object
}

/**
 * Recursively decrypts values within an object, array, or a primitive string.
 * @param {*} obj The object, array, or primitive value to decrypt.
 * @param {string} key The decryption key.
 * @returns {*} The decrypted object, array, or string.
 */
function decryptObject(obj, key) {
    // If the value is a string, attempt to decrypt it
    if (typeof obj === 'string') {
        const decrypted = decryptValue(obj, key);
        // If decryption is successful and not an empty string, return the decrypted value
        // Otherwise, return the original string (e.g., if it wasn't encrypted or decryption failed)
        return decrypted !== null ? decrypted : obj;
    } else if (Array.isArray(obj)) {
        // If it's an array, recursively decrypt each item
        return obj.map(item => decryptObject(item, key));
    } else if (typeof obj === 'object' && obj !== null) {
        // If it's an object, create a new object and recursively decrypt its properties
        const result = {};
        for (let k in obj) {
            // Ensure to only process own properties
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                result[k] = decryptObject(obj[k], key);
            }
        }
        return result;
    }
    return obj; // Return the original value if it's not a recognized type for decryption
}

/**
 * Retrieves the list of properties to be processed from the 'propsInput' textarea.
 * Each line in the textarea is considered a separate property path.
 * @returns {string[]} An array of trimmed, non-empty property paths.
 */
function getPropertiesToProcess() {
    return document.getElementById('propsInput').value
        .split('\n') // Split the input by newlines
        .map(p => p.trim()) // Trim whitespace from each line
        .filter(p => p !== ''); // Filter out empty lines
}

/**
 * Encrypts specified properties within a YAML input string.
 * The encrypted values replace the original values in the YAML output.
 */
function encryptYaml() {
    const input = document.getElementById('yamlInput').value; // Get YAML input string
    const key = document.getElementById('keyInput').value.trim(); // Get encryption key
    const props = getPropertiesToProcess(); // Get list of properties to encrypt

    try {
        const data = jsyaml.load(input); // Parse YAML string into a JavaScript object
        // Iterate over each property path specified by the user
        for (let prop of props) {
            const keys = prop.split('.'); // Split property path into individual keys
            let ref = data; // Start reference at the root of the data object
            // Traverse the object based on the property path, stopping before the last key
            for (let i = 0; i < keys.length - 1; i++) {
                ref = ref[keys[i]];
                if (!ref) break; // If a part of the path doesn't exist, stop
            }
            const last = keys[keys.length - 1]; // Get the last key in the path
            // If the reference and the last key exist, encrypt the value at that path
            if (ref && Object.prototype.hasOwnProperty.call(ref, last) && ref[last] !== undefined) {
                ref[last] = encryptObject(ref[last], key); // Encrypt the value
            }
        }
        // Dump the modified JavaScript object back into a YAML string
        const output = jsyaml.dump(data, { lineWidth: 160 });
        document.getElementById('yamlOutput').value = output; // Display the encrypted YAML
    } catch (e) {
        document.getElementById('yamlOutput').value = 'Error: ' + e.message; // Display any errors
    }
}

/**
 * Generates an environment variable script based on the provided mapping and OS type.
 * @param {Object.<string, string>} mapping An object containing environment variable names and their values.
 * @param {string} os The operating system type ('windows' or 'linux'/'macos').
 * @returns {string} The generated shell script for setting environment variables.
 */
function generateEnvScript(mapping, os) {
    if (os === 'windows') {
        // For Windows, use 'setx' command to set persistent environment variables
        return Object.entries(mapping).map(([k, v]) => `setx ${k} "${v}"`).join('\n');
    } else {
        // For Linux/macOS, use 'export' command
        return Object.entries(mapping).map(([k, v]) => `export ${k}="${v}"`).join('\n');
    }
}

/**
 * Decrypts specified properties within a YAML input string.
 * The decrypted values replace the encrypted values in the YAML output.
 */
function decryptYaml() {
    const input = document.getElementById('yamlInput').value; // Get YAML input string
    const key = document.getElementById('keyInput').value.trim(); // Get decryption key
    const props = getPropertiesToProcess(); // Get list of properties to decrypt

    try {
        const data = jsyaml.load(input); // Parse YAML string into a JavaScript object
        // Iterate over each property path specified by the user
        for (let prop of props) {
            const keys = prop.split('.'); // Split property path into individual keys
            let ref = data; // Start reference at the root of the data object
            // Traverse the object based on the property path, stopping before the last key
            for (let i = 0; i < keys.length - 1; i++) {
                ref = ref[keys[i]];
                if (!ref) break; // If a part of the path doesn't exist, stop
            }
            const last = keys[keys.length - 1]; // Get the last key in the path
            // If the reference and the last key exist, decrypt the value at that path
            if (ref && Object.prototype.hasOwnProperty.call(ref, last) && ref[last] !== undefined) {
                ref[last] = decryptObject(ref[last], key); // Decrypt the value
            }
        }
        // Dump the modified JavaScript object back into a YAML string
        const output = jsyaml.dump(data, { lineWidth: 160 });
        document.getElementById('yamlOutput').value = output; // Display the decrypted YAML
    } catch (e) {
        document.getElementById('yamlOutput').value = 'Error: ' + e.message; // Display any errors
    }
}

/**
 * Recursive: replace all values with ENV placeholders + store them in envMapping
 */
function placeholderAll(obj, prefix, mapping) {
    if (typeof obj === 'string' || typeof obj === 'number' || typeof obj === 'boolean') {
        const envName = `${toEnvName(prefix)}`;
        mapping[envName] = obj; // Store the original value
        return `\${${envName}}`; // Replace with placeholder
    } else if (Array.isArray(obj)) {
        return obj.map((item, idx) => placeholderAll(item, `${prefix}_${idx}`, mapping));
    } else if (typeof obj === 'object' && obj !== null) {
        const result = {};
        for (let k in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                result[k] = placeholderAll(obj[k], `${prefix}_${k}`, mapping);
            }
        }
        return result;
    }
    return obj;
}

/**
 * Generate Placeholder (semua property diganti dengan ENV)
 */
function generatePlaceholder() {
    const input = document.getElementById('yamlInput').value;
    const osType = document.getElementById('osSelect').value;
    const envMapping = {};
    try {
        const data = jsyaml.load(input);
        const outputObj = placeholderAll(data, "root", envMapping);
        const output = jsyaml.dump(outputObj, { lineWidth: 160 });

        document.getElementById('yamlOutput').value = output;
        document.getElementById('envMapping').value = Object.entries(envMapping)
            .map(([k, v]) => `${k}=${v}`).join('\n');
        document.getElementById('envScript').value = generateEnvScript(envMapping, osType);
    } catch (e) {
        document.getElementById('yamlOutput').value = 'Error: ' + e.message;
    }
}

/**
 * Encrypt and then generate placeholders (all properties)
 */
function encryptYamlEnv() {
    const input = document.getElementById('yamlInput').value;
    const key = document.getElementById('keyInput').value.trim();
    const osType = document.getElementById('osSelect').value;
    const envMapping = {};
    const props = getPropertiesToProcess(); // Ambil daftar properti yang ingin dienkripsi

    try {
        const data = jsyaml.load(input);

        // Hanya enkripsi properti yang dipilih
        for (let prop of props) {
            const keys = prop.split('.');
            let ref = data;
            for (let i = 0; i < keys.length - 1; i++) {
                ref = ref[keys[i]];
                if (!ref) break;
            }
            const last = keys[keys.length - 1];
            if (ref && Object.prototype.hasOwnProperty.call(ref, last) && ref[last] !== undefined) {
                const envName = toEnvName(prop);
                const value = String(ref[last]);
                const encrypted = CryptoJS.AES.encrypt(value, key).toString();
                envMapping[envName] = encrypted;
                ref[last] = `\${${envName}}`; // Ganti dengan placeholder ENV
            }
        }

        const output = jsyaml.dump(data, { lineWidth: 160 });

        document.getElementById('yamlOutput').value = output;
        document.getElementById('envMapping').value = Object.entries(envMapping)
            .map(([k, v]) => `${k}=${v}`).join('\n');
        document.getElementById('envScript').value = generateEnvScript(envMapping, osType);
    } catch (e) {
        document.getElementById('yamlOutput').value = 'Error: ' + e.message;
    }
}


/**
 * Convert camelCase or PascalCase string to UPPER_SNAKE_CASE
 * @param {string} str The input string (e.g., "baseApplicationNamespace")
 * @returns {string} The converted string (e.g., "BASE_APPLICATION_NAMESPACE")
 */
function toEnvName(str) {
    return str
        .replace(/^root_?/i, '')               // buang prefix "root" atau "root_"
        .replace(/([a-z0-9])([A-Z])/g, '$1_$2')
        .replace(/[^A-Za-z0-9_]/g, '_')
        .toUpperCase();
}

/**
 * Converts a JavaScript object/array to an XML string.
 *
 * @param {Object|Array} data     The input object/array to convert.
 * @param {string} rootName       The name of the root XML element.
 * @param {string} itemName       The name to use for array items.
 * @returns {string}              The resulting XML string.
 */
function toXml(data, rootName = "root", itemName = "item") {
    const doc = document.implementation.createDocument("", "", null);
    const root = doc.createElement(rootName);
    doc.appendChild(root);

    arrayToXml(data, root, rootName, itemName, doc);

    const serializer = new XMLSerializer();
    const rawXml = serializer.serializeToString(doc);

    return formatXml(rawXml); // pretty print
}

/**
 * Recursively converts object/array to XML nodes
 */
function arrayToXml(data, parent, parentName, itemName, doc) {
    if (Array.isArray(data)) {
        data.forEach(item => {
            const child = doc.createElement(itemName);
            parent.appendChild(child);
            arrayToXml(item, child, parentName, itemName, doc);
        });
    } else if (typeof data === "object" && data !== null) {
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const value = data[key];
                if (Array.isArray(value)) {
                    value.forEach(subItem => {
                        const child = doc.createElement(key);
                        parent.appendChild(child);
                        arrayToXml(subItem, child, key, itemName, doc);
                    });
                } else if (typeof value === "object" && value !== null) {
                    const child = doc.createElement(key);
                    parent.appendChild(child);
                    arrayToXml(value, child, key, itemName, doc);
                } else {
                    const child = doc.createElement(key);
                    child.textContent = value;
                    parent.appendChild(child);
                }
            }
        }
    } else {
        parent.textContent = data;
    }
}

/**
 * Simple XML formatter with indentation
 */
function formatXml(xml) {
    const PADDING = "  "; // 2 spaces
    let formatted = "";
    let pad = 0;

    // tambahkan newline antar tag
    xml = xml.replace(/>\s*</g, ">\n<");

    xml.split("\n").forEach(node => {
        node = node.trim();
        if (!node) return;

        if (/^<\//.test(node)) {
            // closing tag
            pad -= 1;
            formatted += PADDING.repeat(pad) + node + "\n";
        } else if (/^<[^>]+\/>/.test(node)) {
            // self closing
            formatted += PADDING.repeat(pad) + node + "\n";
        } else if (/^<[^>]+>.*<\/[^>]+>$/.test(node)) {
            // open + close di baris yang sama
            formatted += PADDING.repeat(pad) + node + "\n";
        } else if (/^<[^>]+>/.test(node)) {
            // opening tag
            formatted += PADDING.repeat(pad) + node + "\n";
            pad += 1;
        } else {
            // text node
            formatted += PADDING.repeat(pad) + node + "\n";
        }
    });

    return formatted.trim();
}


/**
 * Recursively adds object/array data to an XML element.
 *
 * @param {any} data              The data to convert (array, object, or scalar).
 * @param {Element} xmlElement    The XML element to append to.
 * @param {string} currentName    The current element name.
 * @param {string} itemName       The name to use for array items.
 * @param {Document} doc          The XML document instance.
 */
function arrayToXml(data, parent, parentName, itemName, doc) {
    if (Array.isArray(data)) {
        data.forEach(item => {
            const child = doc.createElement(itemName);
            parent.appendChild(child);
            arrayToXml(item, child, parentName, itemName, doc);
        });
    } else if (typeof data === "object" && data !== null) {
        Object.entries(data).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                // array → buat banyak <item>
                const wrapper = doc.createElement(key);
                parent.appendChild(wrapper);
                value.forEach(v => {
                    const child = doc.createElement(itemName);
                    wrapper.appendChild(child);
                    arrayToXml(v, child, key, itemName, doc);
                });
            } else if (typeof value === "object" && value !== null) {
                const child = doc.createElement(key);
                parent.appendChild(child);
                arrayToXml(value, child, key, itemName, doc);
            } else {
                const child = doc.createElement(key);
                child.textContent = value;
                parent.appendChild(child);
            }
        });
    } else {
        parent.textContent = data;
    }
}


/**
 * Converts a YAML string to an XML string.
 * @param {*} input - The input YAML string.
 * @returns string - The resulting XML string.
 */
function yamlToXml(input)
{
    const data = jsyaml.load(input);
    return toXml(data, "root", "item");
}

/**
 * Copy envioronemnt variable to clipboard
 * Copies the envioronemnt variable content from the associated textarea to the clipboard.
 * @param {*} button - The button element that was clicked.
 */
function copyEnv(button) {
    let textarea = button.closest('.form-group').querySelector('textarea');
    let originalText = button.textContent;
    let yaml = textarea.value;
    button.disabled = true;
    // Copy to clipboard
    navigator.clipboard.writeText(yaml).then(() => {
        // Show success message
        button.textContent = button.dataset.copiedCaption || 'Copied!';
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}


/**
 * Copy Yaml to clipboard
 * Copies the YAML content from the associated textarea to the clipboard.
 * @param {*} button - The button element that was clicked.
 */
function copyYaml(button) {
    let textarea = button.closest('.form-group').querySelector('textarea');
    let originalText = button.textContent;
    let yaml = textarea.value;
    button.disabled = true;
    // Copy to clipboard
    navigator.clipboard.writeText(yaml).then(() => {
        // Show success message
        button.textContent = button.dataset.copiedCaption || 'Copied!';
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}

/**
 * Copy Xml to clipboard
 * Converts the YAML in the associated textarea to XML and copies it to the clipboard.
 * @param {*} button - The button element that was clicked.
 */
function copyXml(button) {
    let textarea = button.closest('.form-group').querySelector('textarea');
    let originalText = button.textContent;
    let yaml = textarea.value;
    let xml = yamlToXml(yaml);
    button.disabled = true;
    // Copy to clipboard
    navigator.clipboard.writeText(xml).then(() => {
        // Show success message
        button.textContent = button.dataset.copiedCaption || 'Copied!';
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}

/**
 * Encrypts selected properties and generates environment variable placeholders recursively.
 * @param {*} obj The object, array, or primitive value to encrypt.
 * @param {string} key The encryption key.
 * @param {Object} envMapping The mapping object to store environment variable names and values.
 * @param {boolean} [skipEncrypt=false] If true, the values are stored as plaintext in the mapping.
 * @returns {*} The encrypted object, array, or string with placeholders.
 */
function encryptYamlEnvRecursive() {
    const input = document.getElementById('yamlInput').value;
    const key = document.getElementById('keyInput').value.trim();
    const osType = document.getElementById('osSelect').value;
    const envMapping = {};
    const props = getPropertiesToProcess(); // e.g. ["database"]

    try {
        const data = jsyaml.load(input);

        // Fungsi rekursif untuk mengenkripsi semua scalar property
        function encryptScalars(obj, path) {
            if (typeof obj === 'string' || typeof obj === 'number' || typeof obj === 'boolean') {
                const envName = toEnvName(path);
                const encrypted = CryptoJS.AES.encrypt(String(obj), key).toString();
                envMapping[envName] = encrypted;
                return `\${${envName}}`;
            } else if (Array.isArray(obj)) {
                return obj.map((item, idx) => encryptScalars(item, `${path}_${idx}`));
            } else if (typeof obj === 'object' && obj !== null) {
                let result = {};
                for (let prop in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                        result[prop] = encryptScalars(obj[prop], `${path}_${prop}`);
                    }
                }
                return result;
            }
            return obj;
        }

        // Proses setiap property yang dipilih
        for (let prop of props) {
            const keys = prop.split('.');
            let ref = data;
            for (let i = 0; i < keys.length - 1; i++) {
                ref = ref[keys[i]];
                if (!ref) break;
            }
            const last = keys[keys.length - 1];
            if (ref && Object.prototype.hasOwnProperty.call(ref, last) && ref[last] !== undefined) {
                ref[last] = encryptScalars(ref[last], prop);
            }
        }

        const output = jsyaml.dump(data, { lineWidth: 160 });
        document.getElementById('yamlOutput').value = output;
        document.getElementById('envMapping').value = Object.entries(envMapping)
            .map(([k, v]) => `${k}=${v}`).join('\n');
        document.getElementById('envScript').value = generateEnvScript(envMapping, osType);
    } catch (e) {
        document.getElementById('yamlOutput').value = 'Error: ' + e.message;
    }
}