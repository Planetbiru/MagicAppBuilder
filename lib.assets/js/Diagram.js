/**
 * Represents a diagram containing a subset of entities.
 */
class Diagram {

    /**
     * Creates a new Diagram instance.
     *
     * @param {string} name - The name of the diagram.
     * @param {number} sortOrder - The sort order index of the diagram.
     * @param {Entity[]} originalEntities - The full list of entities available for selection.
     */
    constructor(name, sortOrder, originalEntities) {
        /**
         * Array of entity names included in this diagram.
         * @type {string[]}
         */
        this.entitieNames = [];
        /**
         * Name of the diagram.
         * @type {string}
         */
        this.name = name;
        /**
         * Sort order of the diagram.
         * @type {number}
         */
        this.sortOrder = sortOrder;
        /**
         * Original entities available for the diagram.
         * @type {Entity[]}
         */
        this.originalEntities = originalEntities;
        /**
         * Whether this diagram is active.
         * @type {boolean}
         */
        this.active = false;
        /**
         * Creates the ERD for this diagram.
         * @param {number} updatedWidth - The width for rendering.
         * @param {boolean} drawAutoRelationship - Whether to draw relationships.
         * @param {boolean} drawFkRelationship - Whether to draw foreign key relationships.
         */
        this.createERD = function (updatedWidth, drawAutoRelationship, drawFkRelationship) {
            this.entityRenderer.createERD(this.getData(), updatedWidth, drawAutoRelationship, drawFkRelationship);
        };
    }

    /**
     * Gets the entities included in this diagram.
     * @returns {Entity[]} The entities in the diagram.
     */
    getData() {
        let entities = [];
        for (let entity of this.originalEntities) {
            if (this.entitieNames.includes(entity.name)) {
                entities.push(entity);
            }
        }
        return entities;
    };
}