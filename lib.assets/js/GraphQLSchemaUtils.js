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

            // Skip the Query type as it is not a data model
            if (typeName === 'Query') {
                continue;
            }

            const fields = [];
            const fieldRegex = /(\w+):\s*([\w!]+)/g;
            let fieldMatch;

            // Extract fields and their types
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
                // Store as a type definition
                parsedSchema.types[typeName] = fields;
            } else if (typeOrInput === 'input') {
                // Store as an input definition
                parsedSchema.inputs[typeName] = fields;
            }
        }
        return parsedSchema;
    }
}