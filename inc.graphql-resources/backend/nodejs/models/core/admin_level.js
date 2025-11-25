module.exports = (sequelize, DataTypes) => {
    const AdminLevel = sequelize.define('admin_level', {
        admin_level_id: {
            type: DataTypes.STRING(40),
            primaryKey: true,
            allowNull: false
        },
        name: {
            type: DataTypes.STRING(100),
            allowNull: true
        },
        special_access: {
            type: DataTypes.BOOLEAN,
            defaultValue: 0
        },
        sort_order: {
            type: DataTypes.INTEGER,
            defaultValue: 0
        },
        default_data: {
            type: DataTypes.BOOLEAN,
            defaultValue: 0
        },
        active: {
            type: DataTypes.BOOLEAN,
            defaultValue: 1
        }
    }, {
        tableName: 'admin_level',
        timestamps: false
    });

    AdminLevel.associate = (models) => {
        AdminLevel.hasMany(models.admin, {
            foreignKey: 'admin_level_id'
        });
    };

    return AdminLevel;
};