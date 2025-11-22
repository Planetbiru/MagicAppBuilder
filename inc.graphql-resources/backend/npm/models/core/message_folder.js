module.exports = (sequelize, DataTypes) => {
    const MessageFolder = sequelize.define('message_folder', {
        message_folder_id: {
            type: DataTypes.STRING(40),
            primaryKey: true,
            allowNull: false
        },
        name: {
            type: DataTypes.STRING(100),
            allowNull: true
        },
        admin_id: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        sort_order: {
            type: DataTypes.INTEGER,
            allowNull: true
        },
        time_create: {
            type: DataTypes.DATE,
            allowNull: true
        },
        time_edit: {
            type: DataTypes.DATE,
            allowNull: true
        },
        admin_create: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        admin_edit: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        ip_create: {
            type: DataTypes.STRING(50),
            allowNull: true
        },
        ip_edit: {
            type: DataTypes.STRING(50),
            allowNull: true
        },
        active: {
            type: DataTypes.BOOLEAN,
            allowNull: true,
            defaultValue: 1
        }
    }, {
        tableName: 'message_folder',
        timestamps: false
    });

    MessageFolder.associate = (models) => {
        MessageFolder.belongsTo(models.admin, { foreignKey: 'admin_id', as: 'ownerAdmin' });
        MessageFolder.hasMany(models.message, { foreignKey: 'message_folder_id', as: 'messages' });
    };

    return MessageFolder;
};