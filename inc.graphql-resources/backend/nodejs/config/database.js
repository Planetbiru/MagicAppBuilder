const { Sequelize, DataTypes } = require('sequelize');

let sequelize;

const commonOptions = {
    dialectOptions: {
        // Return date/time values as strings, not Date objects.
        dateStrings: true
    },
    logging: false, // Set to console.log to see SQL queries
    define: {
        timestamps: false, // Assuming no `createdAt` and `updatedAt` fields
        // This is important to prevent Sequelize from changing column names to camelCase
        quoteIdentifiers: false,
        freezeTableName: true // Prevent Sequelize from pluralizing table names
    }
};

if (process.env.DB_DIALECT === 'sqlite') {
    sequelize = new Sequelize({
        dialect: 'sqlite',
        storage: process.env.DB_FILE, // Path to the database file
        ...commonOptions
    });
} else {
    sequelize = new Sequelize(
        process.env.DB_NAME,
        process.env.DB_USER,
        process.env.DB_PASS,
        {
            host: process.env.DB_HOST,
            port: process.env.DB_PORT,
            dialect: process.env.DB_DIALECT,
            ...commonOptions
        }
    );
}

const models = {};

// Dynamically import all models
const fs = require('fs');
const path = require('path');
const modelsDir = path.join(__dirname, '../models');

// Define the Session model explicitly for connect-session-sequelize
models.Session = sequelize.define('Session', {
  sid: {
    type: Sequelize.STRING,
    primaryKey: true,
  },
  expires: Sequelize.DATE,
  data: Sequelize.TEXT,
});


const loadModels = (dir) => {
  fs.readdirSync(dir, { withFileTypes: true }).forEach(entry => {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      // Recurse into subdirectory
      loadModels(fullPath);
    } else if (entry.isFile() && entry.name.indexOf('.') !== 0 && entry.name.slice(-3) === '.js') {
      // Load the model file
      const model = require(fullPath)(sequelize, DataTypes);
      if (model && model.name) {
        models[model.name] = model;
      }
    }
  });
};

loadModels(modelsDir); // Start the recursive loading from the base models directory
// Set up associations
Object.keys(models).forEach(modelName => {
  if (models[modelName].associate) {
    models[modelName].associate(models);
  }
});

console.log('Models loaded from config/database.js:', Object.keys(models));

module.exports = { sequelize, models };