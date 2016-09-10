<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\block;


use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\RedstoneUtil;

class RedstoneTorch extends Torch implements RedstoneSource, RedstoneTarget{
	const TICK_DELAY = 100;

	protected $id = self::REDSTONE_TORCH_ON;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function isPowered() : bool{
		return ($this->id == self::REDSTONE_TORCH_ON) ? true : false;
	}

	public function getLightLevel(){
		return $this->isPowered() ? 7 : 0;
	}

	public function getDrops(Item $item) : array{
		return [
			[self::REDSTONE_TORCH_ON, 0, 1]
		];
	}

	public function setPowered(Block $block, bool $powered){
		$block->id = $powered ? self::REDSTONE_TORCH_ON : self::REDSTONE_TORCH_OFF;
		$block->getLevel()->setBlock($block, $block, true, false);
	}

	public function onUpdate($type){
		parent::onUpdate($type);
		if($type == Level::BLOCK_UPDATE_NORMAL){
			$this->getLevel()->scheduleUpdate($this, self::TICK_DELAY);
		}elseif($type == Level::BLOCK_UPDATE_SCHEDULED){
			$receiving = $this->isReceivingPower($this);
			if($this->isPowered() == $receiving){
				$this->setPowered($this, !$receiving);
			}
		}
	}

	public function isReceivingPower(Block $block) : bool{
		$faces = [
			1 => 4,
			2 => 5,
			3 => 2,
			4 => 3,
			5 => 0,
			6 => 0,
			0 => 0,
		];
		$attached = $block->getSide($faces[$block->getDamage()]);
		return RedstoneUtil::isEmittingPower($attached, Vector3::getOppositeSide($faces[$block->getDamage()]));
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		return $this->isPowered() ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}

	public function getIndirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		$faces = [
			1 => 4,
			2 => 5,
			3 => 2,
			4 => 3,
			5 => 0,
			6 => 0,
			0 => 0,
		];
		if($faces[$block->getDamage()] == $face){
			return self::REDSTONE_POWER_MIN;
		}
		return parent::getIndirectRedstonePower($block, $face, $powerMode);
	}

	public function getDirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return ($this->isPowered() and $face == Vector3::SIDE_UP) ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}

	public function hasDirectRedstonePower(Block $block, int $face, int $powerMode) : bool{
		return $this->getDirectRedstonePower($block, $face, $powerMode) > 0;
	}


	public function getRedstonePowerStrength(Vector3 $pos) : int{
		return $this->isPowered() ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}
}
