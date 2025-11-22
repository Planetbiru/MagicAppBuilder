module.exports = (sequelize, DataTypes) => {
    const Admin = sequelize.define('admin', {
        admin_id: {
            type: DataTypes.STRING(40),
            primaryKey: true,
            allowNull: false
        },
        name: {
            type: DataTypes.STRING(100),
            allowNull: true
        },
        username: {
            type: DataTypes.STRING(100),
            allowNull: true
        },
        password: {
            type: DataTypes.STRING(512),
            allowNull: true
        },
        password_version: {
            type: DataTypes.STRING(512),
            allowNull: true
        },
        admin_level_id: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        gender: {
            type: DataTypes.STRING(2),
            allowNull: true
        },
        birth_day: {
            type: DataTypes.DATEONLY,
            allowNull: true
        },
        email: {
            type: DataTypes.STRING(100),
            allowNull: true
        },
        phone: {
            type: DataTypes.STRING(100),
            allowNull: true
        },
        language_id: {
            type: DataTypes.STRING(40),
            allowNull: true
        },
        validation_code: {
            type: DataTypes.TEXT,
            allowNull: true
        },
        last_reset_password: {
            type: DataTypes.DATE,
            allowNull: true
        },
        blocked: {
            type: DataTypes.BOOLEAN,
            allowNull: true,
            defaultValue: 0
        },
        active: {
            type: DataTypes.BOOLEAN,
            allowNull: true,
            defaultValue: 1
        }
    }, {
        tableName: 'admin',
        timestamps: false
    });

    Admin.associate = (models) => {
        Admin.belongsTo(models.admin_level, {
            foreignKey: 'admin_level_id',
            as: 'admin_level'
        });
        Admin.hasMany(models.message, {
            foreignKey: 'sender_id'
        });
        Admin.hasMany(models.message, {
            foreignKey: 'receiver_id'
        });
        Admin.hasMany(models.notification, {
            foreignKey: 'admin_id'
        });
        Admin.hasMany(models.message_folder, {
            foreignKey: 'admin_id', as: 'ownedMessageFolders'
        });
    };

    return Admin;
};
