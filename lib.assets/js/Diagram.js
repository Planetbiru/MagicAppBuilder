

function Diagram(name, sortOrder, originalEntities)
{
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
     * @param {boolean} drawRelationship - Whether to draw relationships.
     */
    this.createERD = function(updatedWidth, drawRelationship)
    {
        this.entityRenderer.createERD(this.getData(), updatedWidth, drawRelationship);
    }
    /**
     * Gets the entities included in this diagram.
     * @returns {Entity[]} The entities in the diagram.
     */
    this.getData = function()
    {
        let entities = [];
        for(let entity of this.originalEntities)
        {
            if(this.entitieNames.includes(entity.name))
            {
                entities.push(entity);
            }
        }
        return entities;
    }
}