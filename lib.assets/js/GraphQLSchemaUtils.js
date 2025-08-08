/**
 * A utility class for parsing GraphQL schema strings and normalizing entity data.
 */
class GraphQLSchemaUtils {

    /**
     * @private
     * @type {string[]}
     * An array of reserved GraphQL scalar types.
     */
    static reservedTypes = [
        'String',
        'Int',
        'Float',
        'Boolean',
        'ID'
    ];

    /**
     * @private
     * Converts a string to snake_case.
     * @param {string} str The input string.
     * @returns {string} The string in snake_case.
     */
    static toSnakeCase(str) {
        // This regex now correctly handles the first letter, preventing a leading underscore.
        return str.replace(/([A-Z])/g, (match, p1, offset) => {
            // Only add an underscore if it's not the first character
            return (offset > 0 ? '_' : '') + p1.toLowerCase();
        });
    }

    /**
     * @private
     * Converts a string to camelCase.
     * @param {string} str The input string.
     * @returns {string} The string in camelCase.
     */
    static toCamelCase(str) {
        return str.replace(/_([a-z])/g, (_, c) => c.toUpperCase());
    }

    /**
     * @private
     * Normalizes a name for case-insensitive comparison by removing underscores and spaces.
     * @param {string} name The name to normalize.
     * @returns {string} The normalized name.
     */
    static normalizeNameForComparison(name) {
        return name.replace(/[_\s]/g, '').toLowerCase();
    }

    /**
     * Normalizes a collection of entities by standardizing column names, types, and relationships.
     * This method converts relationship fields to use the referencing entity's ID type and standardizes
     * naming conventions based on the specified mode. It also normalizes the entity names themselves.
     *
     * @param {object} entities The raw entities object.
     * @param {string} [mode="snake"] - The desired naming convention for names ("snake" or "camel").
     * @returns {object} The normalized entities object.
     */
    static normalizeEntity(entities, mode = "snake") {
        const normalizedEntities = {};

        for (const [entityName, columns] of Object.entries(entities)) {
            let normalizedEntityName = entityName;

            // Normalize the entity name itself based on the mode
            if (mode === "snake") {
                if (!normalizedEntityName.includes('_')) {
                    normalizedEntityName = this.toSnakeCase(normalizedEntityName);
                }
            } else if (mode === "camel") {
                if (normalizedEntityName.includes('_')) {
                    normalizedEntityName = this.toCamelCase(normalizedEntityName);
                }
            }

            normalizedEntities[normalizedEntityName] = columns.map(col => /*NOSONAR*/ {
                let { name, type, nullable } = col;

                // Check if the column type is a custom entity type (not a reserved scalar)
                if (!this.reservedTypes.includes(type)) {
                    const referencedEntityName = type;

                    if (!/id$/i.test(name)) {
                        name = this.normalizeNameForComparison(name) === this.normalizeNameForComparison(referencedEntityName)
                            ? name + "Id"
                            : name;
                    }

                    const referencedEntity = entities[referencedEntityName];
                    if (referencedEntity) {
                        const refColumn = referencedEntity.find(c =>
                            this.normalizeNameForComparison(c.name) === this.normalizeNameForComparison(name)
                        );
                        if (refColumn) {
                            type = refColumn.type;
                        }
                    }
                }

                const hasUnderscore = name.includes('_');

                if (mode === "camel") {
                    if (hasUnderscore) {
                        name = this.toCamelCase(name);
                    }
                } else if (mode === "snake") {
                    if (!hasUnderscore && /[a-z][A-Z]/.test(name)) {
                        name = this.toSnakeCase(name);
                    }
                }

                if (type === "ID") {
                    type = "String";
                }

                return { name, type, nullable };
            });
        }

        return normalizedEntities;
    }

    /**
     * Parses a GraphQL schema string into a structured JavaScript object.
     * The parser extracts `type` and `input` definitions, along with their fields,
     * types, and nullability.
     *
     * @param {string} schemaString The GraphQL schema string to parse.
     * @returns {object} An object containing all parsed types and input definitions.
     */
    static parseGraphQLSchema(schemaString) {
        const parsedSchema = {
            types: {},
            inputs: {}
        };

        const typeRegex = /(type|input)\s+(\w+)\s*\{([\s\S]*?)\}/g;
        let match;

        while ((match = typeRegex.exec(schemaString)) !== null) {
            const typeOrInput = match[1];
            const typeName = match[2];
            const fieldsBlock = match[3];

            if (typeName === 'Query') {
                continue;
            }

            const fields = [];
            const fieldRegex = /(\w+):\s*([\w!]+)/g;
            let fieldMatch;

            while ((fieldMatch = fieldRegex.exec(fieldsBlock)) !== null) {
                const fieldName = fieldMatch[1];
                let fieldType = fieldMatch[2];
                const isRequired = fieldType.endsWith('!');
                if (isRequired) {
                    fieldType = fieldType.slice(0, -1);
                }
                fields.push({
                    name: fieldName,
                    type: fieldType,
                    nullable: !isRequired
                });
            }

            if (typeOrInput === 'type') {
                parsedSchema.types[typeName] = fields;
            } else {
                parsedSchema.inputs[typeName] = fields;
            }
        }
        return parsedSchema;
    }

    /**
     * Builds a GraphQL schema string based on a list of database entities and their columns.
     *
     * This method maps SQL data types to GraphQL types, detects relationships based on
     * foreign key naming conventions (fields ending with `_id`), and generates both
     * `type` definitions and corresponding `input` types for mutations.
     *
     * ### Features:
     * - Converts SQL types (e.g., VARCHAR, INT) to equivalent GraphQL scalar types.
     * - Supports custom camelCase and UpperCamelCase name formatting for GraphQL fields and types.
     * - Automatically detects relationships between entities using `_id` suffix convention
     * and generates proper GraphQL relationship fields.
     * - Optionally removes scalar `_id` fields when a relationship field is generated
     * (`removeIdFields` flag).
     * - Generates input types for mutations, handling composite primary keys and required fields.
     * - Generates a root `Query` type for fetching single entities and collections.
     *
     * @param {Array<Object>} entities - The list of entities to include in the schema.
     * Each entity should have:
     * - {string} name - The entity/table name.
     * - {Array<Object>} columns - Column definitions with:
     * - {string} name - Column name.
     * - {string} type - SQL data type (case-insensitive).
     * - {boolean} [nullable=true] - Whether the field is nullable.
     * - {boolean} [primaryKey=false] - Whether the column is a primary key.
     * @param {boolean} [removeIdFields=true] - If true, removes raw `_id` scalar fields
     * when a relationship field is generated for that column.
     *
     * @returns {string} A complete GraphQL schema as a string, including type definitions,
     * input types, and the root Query type for all entities.
     *
     * @example
     * const schema = buildGraphQLSchema(entities, false);
     * console.log(schema);
     */
    static buildGraphQLSchema(entities, removeIdFields = true) {
        const sqlToGraphQL = {
            CHAR: "String", 
            VARCHAR: "String", 
            TEXT: "String", 
            LONGTEXT: "String",
            INT: "Int", 
            INTEGER: "Int", 
            BIGINT: "Int", 
            SMALLINT: "Int", 
            TINYINT: "Boolean",
            DECIMAL: "Float", 
            NUMERIC: "Float", 
            FLOAT: "Float", 
            DOUBLE: "Float", 
            REAL: "Float",
            BOOLEAN: "Boolean",
            DATE: "String", 
            DATETIME: "String", 
            TIMESTAMP: "String", 
            TIME: "String",
            ENUM: "String", 
            SET: "String"
        };

        const toCamelCase = name =>
            name.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
        const toUpperCamelCase = name => {
            let camel = toCamelCase(name);
            return camel.charAt(0).toUpperCase() + camel.slice(1);
        };

        let schema = `# GraphQL Schema generated by MagicAppBuilder\n\n`;

        // Generate TYPE definitions
        entities.forEach(entity => {
            let typeName = toUpperCamelCase(entity.name);
            schema += `type ${typeName} {\n`;

            const primaryKeys = entity.columns.filter(col => col.primaryKey);
            const isCompositeKey = primaryKeys.length > 1;
        

            entity.columns.forEach(col => /*NOSONAR*/ {
                let isRelation = false;
                let isRequired = false;

                
                // Non-nullable relationships are always required
                if (isRelation && !col.nullable) {
                    isRequired = true;
                }

                // Non-key, non-nullable columns are also required
                if (isCompositeKey && col.primaryKey) {
                    isRequired = true;
                }
                
                if(col.primaryKey)
                {
                    isRequired = true;
                }

                // Non-key, non-nullable columns are also required
                if (!col.primaryKey && !isRelation && !col.nullable) {
                    isRequired = true;
                }

                if (col.name.endsWith("_id")) {
                    const relatedEntityName = col.name.slice(0, -3);
                    const relatedEntity = entities.find(
                        e => e.name.toLowerCase() === relatedEntityName.toLowerCase()
                    );

                    if (relatedEntity && relatedEntity.name.toLowerCase() !== entity.name.toLowerCase()) {
                        const relType = toUpperCamelCase(relatedEntity.name);
                        const relField = toCamelCase(relatedEntity.name);
                        schema += `    ${relField}: ${relType}${col.nullable ? "" : "!"}\n`;
                        isRelation = true;
                    }
                }

                if (!(removeIdFields && isRelation)) {
                    let gqlType;
                    if (col.primaryKey) {
                        gqlType = "ID";
                    } else {
                        gqlType = sqlToGraphQL[col.type.toUpperCase()] || "String";
                    }
                    
                    if (isRequired) {
                        gqlType += "!";
                    }
                    
                    schema += `    ${toCamelCase(col.name)}: ${gqlType}\n`;
                }
            });

            schema += `}\n\n`;
        });

        // Generate INPUT definitions
        entities.forEach(entity => {
            let inputName = toUpperCamelCase(entity.name) + "Input";
            schema += `input ${inputName} {\n`;

            const primaryKeys = entity.columns.filter(col => col.primaryKey);
            const isCompositeKey = primaryKeys.length > 1;

            entity.columns.forEach(col => {
                const isRelation =
                    col.name.endsWith("_id") &&
                    entities.some(
                        e => e.name.toLowerCase() === col.name.slice(0, -3).toLowerCase() &&
                            e.name.toLowerCase() !== entity.name.toLowerCase()
                    );

                let gqlType;
                if (col.primaryKey || isRelation) {
                    gqlType = "ID";
                } else {
                    gqlType = sqlToGraphQL[col.type.toUpperCase()] || "String";
                }

                const isRequired =
                    (isCompositeKey && col.primaryKey) ||
                    (isRelation && !col.nullable) ||
                    (!col.primaryKey && !isRelation && !col.nullable);

                if (isRequired) {
                    gqlType += "!";
                }

                schema += `    ${toCamelCase(col.name)}: ${gqlType}\n`;
            });

            schema += `}\n\n`;
        });

        // Add Query block
        schema += `type Query {\n`;
        entities.forEach(entity => {
            const typeName = toUpperCamelCase(entity.name);
            const camelCaseName = toCamelCase(entity.name);
            
            // Find all primary key columns
            const primaryKeyCols = entity.columns.filter(c => c.primaryKey);

            // If there are primary keys, create a query to fetch a single entity
            if (primaryKeyCols.length > 0) {
                // Combine all primary keys into a single argument string
                const primaryKeyArgs = primaryKeyCols.map(col => {
                    const primaryKeyName = toCamelCase(col.name);
                    return `${primaryKeyName}: ID!`;
                }).join(', ');
                
                const singleQueryName = `get${typeName}`;
                schema += `    ${singleQueryName}(${primaryKeyArgs}): ${typeName}\n`;
            }

            // Add a query for all entities
            const pluralName = `${camelCaseName}s`;
            const allQueryName = `getAll${toUpperCamelCase(pluralName)}`;

            schema += `    ${allQueryName}: [${typeName}]\n`;
        });
        schema += `}\n`;

        return schema;
    }
}