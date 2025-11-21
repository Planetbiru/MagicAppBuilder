package com.planetbiru.graphqlapplication.model.repository

import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.data.jpa.repository.JpaSpecificationExecutor
import org.springframework.stereotype.Repository
import com.planetbiru.graphqlapplication.model.entity.Lantai
import org.springframework.data.jpa.repository.Modifying
import org.springframework.data.jpa.repository.Query
import org.springframework.data.repository.query.Param
import org.springframework.transaction.annotation.Transactional


@Repository
interface LantaiRepository : JpaRepository<Lantai, String>, JpaSpecificationExecutor<Lantai> {
    
    @Modifying
    @Transactional
    @Query("UPDATE Lantai a SET a.lantaiId = :newId WHERE a.lantaiId = :oldId")
    fun updateLantaiId(@Param("oldId") oldId: String, @Param("newId") newId: String): Int

}