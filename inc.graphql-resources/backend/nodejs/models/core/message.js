module.exports = (sequelize, DataTypes) => {
    const Message = sequelize.define('message', {
        message_id: {
            type: DataTypes.STRING(40),
            primaryKey: true,
            allowNull: false
        },
        message_direction: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        icon: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        // sender_id, receiver_id, message_folder_id are already defined
        subject: {
            type: DataTypes.STRING(255),
            allowNull: true
        },
        content: {
            type: DataTypes.TEXT,
            allowNull: true
        },
        sender_id: {
            type: DataTypes.STRING(40),
            allowNull: true // Can be from 'system'
        },
        receiver_id: {
            type: DataTypes.STRING(40),
            allowNull: false
        },
        message_folder_id: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        is_read: {
            type: DataTypes.BOOLEAN,
            defaultValue: false
        },
        time_create: {
            type: DataTypes.DATE,
            defaultValue: DataTypes.NOW
        },
        time_read: {
            type: DataTypes.DATE,
            allowNull: true
        },
        ip_read: {
            type: DataTypes.STRING(50),
            allowNull: true
        }
    }, {
        tableName: 'message',
        timestamps: false
    });

    Message.associate = (models) => {
        Message.belongsTo(models.admin, { as: 'sender', foreignKey: 'sender_id' });
        Message.belongsTo(models.admin, { as: 'receiver', foreignKey: 'receiver_id' });
        Message.belongsTo(models.message_folder, { as: 'folder', foreignKey: 'message_folder_id' });
    };

    return Message;
};