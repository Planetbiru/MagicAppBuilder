<?php

namespace Music\Entity;

use MagicObject\MagicObject;

/**
 * EntitySong is entity of table song. You can join this entity to other entity using annotation JoinColumn. 
 * Visit https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="song")
 */
class EntitySong extends MagicObject
{

}