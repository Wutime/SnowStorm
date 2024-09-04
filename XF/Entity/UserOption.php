<?php

namespace Wutime\SnowStorm\XF\Entity;
use XF\Mvc\Entity\Structure;

class UserOption extends XFCP_UserOption
{
	public static function getStructure(Structure $structure)
	{
		$parent = parent::getStructure($structure);
		$parent->columns['wutime_snowstorm_enable'] = ['type' => self::BOOL, 'default' => true];
		return $parent;
	}
}
