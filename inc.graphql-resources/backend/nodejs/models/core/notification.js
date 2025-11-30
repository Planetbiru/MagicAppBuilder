module.exports = (sequelize, DataTypes) => {
    const Notification = sequelize.define('notification', {
        notification_id: {
            type: DataTypes.STRING(40),
            primaryKey: true,
            allowNull: false
        },
        notification_type: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        admin_group: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        admin_id: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        icon: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        subject: {
            type: DataTypes.STRING(255),
            allowNull: true
        },
        content: {
            type: DataTypes.TEXT,
            allowNull: true
        },
        link: {
            type: DataTypes.TEXT,
            allowNull: true
        },
        is_read: {
            type: DataTypes.BOOLEAN,
            allowNull: true
        },
        time_create: {
            type: DataTypes.DATE,
            allowNull: true
        },
        ip_create: {
            type: DataTypes.STRING(50),
            allowNull: true
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
        tableName: 'notification',
        timestamps: false
    });

    Notification.associate = (models) => {
        Notification.belongsTo(models.admin, { foreignKey: 'admin_id' });
        Notification.belongsTo(models.admin_level, { foreignKey: 'admin_group' });
    };

    return Notification;
};