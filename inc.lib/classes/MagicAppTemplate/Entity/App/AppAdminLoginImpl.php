<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppAdminLoginImpl class represents an entity in the "admin" table.
 *
 * This entity maps to the "admin" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAppTemplate\Entity\App
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin")
 */
class AppAdminLoginImpl extends MagicObject
{
	/**
	 * Admin ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.TIMEBASED)
	 * @NotNull
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Admin ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $name;

	/**
	 * Username
	 * 
	 * @Column(name="username", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Username")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $username;

	/**
	 * Password
	 * 
	 * @Column(name="password", type="varchar(512)", length=512, nullable=true)
	 * @Label(content="Password")
	 * @MaxLength(value=512)
	 * @var string
	 */
	protected $password;

    /**
	 * Password Version
	 * 
	 * @Column(name="password_version", type="varchar(512)", length=512, nullable=true)
	 * @Label(content="Password Version")
	 * @MaxLength(value=512)
	 * @var string
	 */
	protected $passwordVersion;

	/**
	 * Admin Level ID
	 * 
	 * @Column(name="admin_level_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Level ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminLevelId;
	
	/**
	 * Admin Level ID
	 * 
	 * @JoinColumn(name="admin_level_id", referenceColumnName="admin_level_id", referenceTableName="admin_level")
	 * @Label(content="Admin Level")
	 * @var AppAdminLevelMinImpl
	 */
	protected $adminLevel;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $email;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Phone")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $phone;
	
	/**
	 * Language ID
	 * 
	 * @Column(name="language_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Language ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $languageId;
	
	/**
	 * Blocked
	 * 
	 * @Column(name="blocked", type="tinyint(1)", length=1, default_value=false, nullable=true)
	 * @DefaultColumn(value="false")
	 * @Label(content="Blocked")
	 * @var bool
	 */
	protected $blocked;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value=true, nullable=true)
	 * @DefaultColumn(value="true")
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

    /**
     * Serializes selected properties of the admin object into an array format.
     *
     * This method is useful for storing minimal user data (e.g. in a session).
     * It avoids sensitive fields such as passwords.
     *
     * @return array The serialized representation of the admin object.
     */
    public function serialize()
    {
        return array(
            'v1' => $this->adminId,
            'v2' => $this->name,
            'v3' => $this->username,
            'v4' => $this->email,
            'v5' => $this->phone,
            'v6' => $this->languageId,
            'v7' => $this->adminLevelId,
            'v8' => $this->blocked,
            'v9' => $this->active,
            'v10' => $this->passwordVersion
        );
    }

    /**
     * Unserializes the object from the given serialized string.
     *
     * This method restores object properties from the serialized data.
     *
     * @param string $data The serialized string.
     * @return self Returns the current instance for method chaining.
     */
    public function unserialize($data)
    {
        $this->adminId         = isset($data['v1']) ? $data['v1'] : null;
        $this->name            = isset($data['v2']) ? $data['v2'] : null;
        $this->username        = isset($data['v3']) ? $data['v3'] : null;
        $this->email           = isset($data['v4']) ? $data['v4'] : null;
        $this->phone           = isset($data['v5']) ? $data['v5'] : null;
        $this->languageId      = isset($data['v6']) ? $data['v6'] : null;
        $this->adminLevelId    = isset($data['v7']) ? $data['v7'] : null;
        $this->blocked         = isset($data['v8']) ? $data['v8'] : false;
        $this->active          = isset($data['v9']) ? $data['v9'] : true;
        $this->passwordVersion = isset($data['v10']) ? $data['v10'] : null;
    }

    /**
     * Get the stored password version from session for a given user ID.
     *
     * @param string $sessionId The target session ID to access.
     * @param string $userId The user ID to retrieve the password version for.
     * @return mixed|null The stored password version, or null if not found.
     */
    public function getPasswordVersionFromSession($sessionId)
    {
        // Tutup session aktif saat ini jika ada
        session_write_close();

        // Set session ID target
        session_id($sessionId);
        session_start();

        // Ambil versi password
        $key = 'password_version';
        $passwordVersion = isset($_SESSION[$key]) ? $_SESSION[$key] : null;

        // Tutup kembali session
        session_write_close();

        return $passwordVersion;
    }

    /**
     * Updates the password version for a specific user.
     *
     * This function is useful for enforcing password changes across multiple devices or sessions.
     * It accesses each session by ID, sets the new password version keyed by a hash of the user ID,
     * and then closes the session properly.
     *
     * @param mixed  $newVersion     The new password version to be stored in the session.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function updatePasswordVersion($newVersion) {
        $sessionId = sha1($this->getAdminId());
        session_write_close();
        session_id($sessionId);
        session_start();
        $_SESSION['password_version'] = $newVersion;
        session_write_close();
    }

    /**
     * Updates the password version in all active sessions for a specific user.
     *
     * This function is useful for enforcing password changes across multiple devices or sessions.
     * It accesses each session by ID, sets the new password version keyed by a hash of the user ID,
     * and then closes the session properly.
     *
     * @param array  $sessionIdList  An array of session IDs representing the user's active sessions.
     * @param mixed  $newVersion     The new password version to be stored in the session.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function updatePasswordVersionInAllSessions($sessionIdList, $newVersion) {
        foreach ($sessionIdList as $sessionId) {
            session_write_close();
            session_id($sessionId);
            session_start();
            $_SESSION['password_version'] = $newVersion;
            session_write_close();
        }
    }

    public function validPasswordVersion()
    {
        $sessionId = sha1($this->getAdminId());
        $version1 = trim($this->getPasswordVersionFromSession($sessionId));
        $version2 = trim($this->getPasswordVersion());
         return $version1 == $version2;
    }

}