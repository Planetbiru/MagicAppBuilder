package com.planetbiru.graphqlapplication.model.repository.core

import com.planetbiru.graphqlapplication.model.entity.core.MessageFolder
import org.springframework.data.jpa.repository.JpaRepository

interface MessageFolderRepository : JpaRepository<MessageFolder, String> {
    // Custom query methods can be added here in the future if needed.
}