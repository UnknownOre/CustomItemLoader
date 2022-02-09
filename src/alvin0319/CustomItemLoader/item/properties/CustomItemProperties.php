<?php

/*
 *    ____          _                  ___ _                 _                    _
 *   / ___|   _ ___| |_ ___  _ __ ___ |_ _| |_ ___ _ __ ___ | |    ___   __ _  __| | ___ _ __
 *  | |  | | | / __| __/ _ \| '_ ` _ \ | || __/ _ \ '_ ` _ \| |   / _ \ / _` |/ _` |/ _ \ '__|
 *  | |__| |_| \__ \ || (_) | | | | | || || ||  __/ | | | | | |__| (_) | (_| | (_| |  __/ |
 *   \____\__,_|___/\__\___/|_| |_| |_|___|\__\___|_| |_| |_|_____\___/ \__,_|\__,_|\___|_|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace alvin0319\CustomItemLoader\item\properties;

use alvin0319\CustomItemLoader\data\CustomItemData;
use InvalidArgumentException;
use pocketmine\block\BlockToolType;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use ReflectionClass;
use function explode;

final class CustomItemProperties{

	private static int $itemId = 0;

	/** @var string */
	protected string $name;
	/** @var int */
	protected int $id;
	/** @var int */
	protected int $meta;
	/** @var string */
	protected string $namespace;
	/** @var int */
	protected int $runtimeId;
	/** @var bool */
	protected bool $durable = false;
	/** @var int|null */
	protected ?int $max_durability = null;
	/** @var bool */
	protected bool $allow_off_hand = false;
	/** @var bool */
	protected bool $can_destroy_in_creative = false;
	/** @var int */
	protected int $creative_category = 1;
	/** @var bool */
	protected bool $hand_equipped = true;
	/** @var int */
	protected int $max_stack_size = 64;
	/** @var float */
	protected float $mining_speed = 1;
	/** @var bool */
	protected bool $food = false;
	/** @var bool */
	protected bool $can_always_eat = false;
	/** @var int|null */
	protected ?int $nutrition = null;
	/** @var float|null */
	protected ?float $saturation = null;
	/** @var Item|null */
	protected ?Item $residue = null;
	/** @var bool */
	protected bool $armor = false;
	/** @var int */
	protected int $defence_points;
	/** @var CompoundTag|null */
	protected ?CompoundTag $nbt = null;
	/** @var bool */
	protected bool $isBlock = false;
	/** @var int */
	protected int $blockId;
	/** @var bool */
	protected bool $tool = false;
	/** @var int */
	protected int $toolType = BlockToolType::NONE;
	/** @var int */
	protected int $toolTier = 0;

	protected bool $add_creative_inventory = false;

	protected int $attack_points = 0;

	protected int $foil;

	protected int $armorSlot = ArmorInventory::SLOT_HEAD;

	protected string $texture;

	protected string $legacy_id = "";

	public function __construct(string $name, array $data){
		$this->name = $name;
		$this->parseData($data);
	}

	private function parseData(array $data) : void{
		$id = 1000 + self::$itemId++;
		$allow_off_hand = (int) ($data["allow_off_hand"] ?? false);
		$can_destroy_in_creative = (int) ($data["can_destroy_in_creative"] ?? false);
		$creative_category = (int) ($data["creative_category"] ?? 1); // 1 건축 2 자연 3 아이템
		$hand_equipped = (int) ($data["hand_equipped"] ?? true);
		$max_stack_size = (int) ($data["max_stack_size"] ?? 64);
		$mining_speed = (float) ($data["mining_speed"] ?? 1);

		$food = (int) ($data["food"] ?? false);
		$can_always_eat = (int) ($data["can_always_eat"] ?? false);
		$nutrition = (int) ($data["nutrition"] ?? 1);
		$saturation = (float) ($data["saturation"] ?? 1);
		$residue = isset($data["residue"]) ? ItemFactory::getInstance()->get((int) $data["residue"]["id"], (int) ($data["residue"]["meta"] ?? 0)) : ItemFactory::getInstance()->get(0);

		$armor = isset($data["armor"]) ? $data["armor"] : false;
		$defence_points = $data["defence_points"] ?? 0;
		$armor_slot = $data["armor_slot"] ?? "helmet";
		$armor_class = $data["armor_class"] ?? "diamond";

		$foil = (int) ($data["foil"] ?? 0);

		$armor_slot_int = match($armor_slot){
			"helmet" => ArmorInventory::SLOT_HEAD,
			"chest" => ArmorInventory::SLOT_CHEST,
			"leggings" => ArmorInventory::SLOT_LEGS,
			"boots" => ArmorInventory::SLOT_FEET,
			default => throw new InvalidArgumentException("Unknown armor slot $armor_slot given.")
		};
		$armor_slot_int += 2; // wtf mojang

		static $accepted_armor_values = ["gold", "none", "leather", "chain", "iron", "diamond", "elytra", "turtle", "netherite"];

		//static $accepted_armor_position_values = ["slot.armor.legs", "none", "slot.weapon.mainhand", "slot.weapon.offhand", "slot.armor.head", "slot.armor.chest", "slot.armor.feet", "slot.hotbar", "slot.inventory", "slot.enderchest", "slot.saddle", "slot.armor", "slot.chest"];

		$isBlock = $data["isBlock"] ?? false;

		$blockId = $isBlock ? $data["blockId"] : 0;

		$add_creative_inventory = ($data["add_creative_inventory"] ?? false);

		$attack_points = (int) ($data["attack_points"] ?? 1);

		$isBlock = $data["isBlock"] ?? false;

		$blockId = $isBlock ? $data["blockId"] : 0;

		$add_creative_inventory = ($data["add_creative_inventory"] ?? false);

		$tool = $data["tool"] ?? false;
		$tool_type = $data["tool_type"] ?? BlockToolType::NONE;
		$tool_tier = $data["tool_tier"] ?? 0;

		$runtimeId = 5000 + $id;

		$this->id = $id;
		$this->runtimeId = $runtimeId;
		$this->meta = 0; // TODO
		$this->namespace = $data["namespace"];
		$this->allow_off_hand = (bool) $allow_off_hand;
		$this->can_destroy_in_creative = (bool) $can_destroy_in_creative;
		$this->creative_category = (int) $creative_category;
		$this->hand_equipped = (bool) $hand_equipped;
		$this->max_stack_size = $max_stack_size;
		$this->mining_speed = $mining_speed; // TODO: find out property for this

		$this->armor = $armor;
		$this->defence_points = $defence_points;

		$this->isBlock = $isBlock;
		$this->blockId = $blockId;

		$this->add_creative_inventory = $add_creative_inventory;

		$this->tool = $tool;
		$this->toolType = $tool_type;
		$this->toolTier = $tool_tier;

		$this->attack_points = $attack_points;

		$this->foil = $foil;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getNamespace() : string{
		return $this->namespace;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getMeta() : int{
		return $this->meta;
	}

	public function getRuntimeId() : int{
		return $this->runtimeId;
	}

	public function getAllowOffhand() : bool{
		return $this->allow_off_hand;
	}

	public function getCanDestroyInCreative() : bool{
		return $this->can_destroy_in_creative;
	}

	public function getCreativeCategory() : int{
		return $this->creative_category;
	}

	public function getHandEquipped() : bool{
		return $this->hand_equipped;
	}

	public function getMaxStackSize() : int{
		return $this->max_stack_size;
	}

	public function getMiningSpeed() : float{
		return $this->mining_speed;
	}

	public function isFood() : bool{
		return $this->food;
	}

	public function getNutrition() : ?int{
		return $this->nutrition;
	}

	public function getSaturation() : ?float{
		return $this->saturation;
	}

	public function getCanAlwaysEat() : bool{
		return $this->can_always_eat;
	}

	public function getResidue() : ?Item{
		return $this->residue;
	}

	public function isDurable() : bool{
		return $this->durable;
	}

	public function getMaxDurability() : int{
		return $this->max_durability;
	}

	public function isArmor() : bool{
		return $this->armor;
	}

	public function getDefencePoints() : int{
		return $this->defence_points;
	}

	public function isBlock() : bool{
		return $this->isBlock;
	}

	public function getBlockId() : int{
		return $this->blockId;
	}

	public function getBlockToolType() : int{
		return $this->toolType;
	}

	public function getBlockToolHarvestLevel() : int{
		return $this->toolTier;
	}

	public function isTool() : bool{
		return $this->tool;
	}

	public function getAddCreativeInventory() : bool{
		return $this->add_creative_inventory;
	}

	public function getAttackPoints() : int{
		return $this->attack_points;
	}

	public function getNbt() : ?CompoundTag{
		return $this->nbt;
	}

	public function getArmorSlot() : int{
		return $this->armorSlot;
	}

	public function buildNbt() : CompoundTag{
		static $cache = null;
		if($cache !== null){
			return $cache;
		}
		$cache = CompoundTag::create()
			->setTag("components", CompoundTag::create()
				->setTag("item_properties", CompoundTag::create()
					->setInt("use_duration", 32)
					->setInt("use_animation", ($this->food ? 1 : 0)) // 2 is potion, but not now
					->setByte("allow_off_hand", $this->allow_off_hand ? 1 : 0)
					->setByte("can_destroy_in_creative", $this->can_destroy_in_creative ? 1 : 0)
					->setByte("creative_category", $this->creative_category)
					->setByte("hand_equipped", $this->hand_equipped ? 1 : 0)
					->setInt("max_stack_size", $this->max_stack_size)
					->setFloat("mining_speed", $this->mining_speed)
					->setTag("minecraft:icon", CompoundTag::create()
						->setString("texture", $this->texture)
						->setString("legacy_id", $this->legacy_id)
					)
				)
			)
			->setShort("minecraft:identifier", $this->runtimeId)
			->setTag("minecraft:display_name", CompoundTag::create()
				->setString("value", $this->name)
			)
			->setTag("minecraft:on_use", CompoundTag::create()
				->setByte("on_use", 1)
			)->setTag("minecraft:on_use_on", CompoundTag::create()
				->setByte("on_use_on", 1)
			);
		return $cache;
	}

	public static function withoutData() : CustomItemProperties{
		$class = new ReflectionClass(self::class);
		/** @var CustomItemProperties $newInstance */
		$newInstance = $class->newInstanceWithoutConstructor();
		return $newInstance;
	}

	public static function fromCustomItemData(CustomItemData $data) : CustomItemProperties{
		$newInstance = self::withoutData();
		$newInstance->id = 1000 + self::$itemId++;
		$newInstance->namespace = $data->minecraft_item->description->identifier;
		$newInstance->meta = 0; // TODO
		$newInstance->runtimeId = 5000 + $newInstance->id;
		$newInstance->name = explode(":", $data->minecraft_item->description->identifier)[1];
		$newInstance->texture = $data->minecraft_item->components->minecraft_icon->texture;
		$newInstance->legacy_id = empty($data->minecraft_item->components->minecraft_icon->legacy_id) ? $newInstance->namespace : $data->minecraft_item->components->minecraft_icon->legacy_id;
		if(!empty($data->minecraft_item->components->minecraft_hand_equipped)){
			$newInstance->hand_equipped = $data->minecraft_item->components->minecraft_hand_equipped;
		}
		if(!empty($data->minecraft_item->components->minecraft_can_destroy_in_creative)){
			$newInstance->can_destroy_in_creative = $data->minecraft_item->components->minecraft_can_destroy_in_creative;
		}
//		if(!empty($data->minecraft_item->components->minecraft_creative_category)){
//			$newInstance->creative_category = $data->minecraft_item->components->minecraft_creative_category;
//		}
		if(!empty($data->minecraft_item->components->minecraft_max_stack_size)){
			$newInstance->max_stack_size = $data->minecraft_item->components->minecraft_max_stack_size;
		}
		if(!empty($data->minecraft_item->components->minecraft_mining_speed)){
			$newInstance->mining_speed = $data->minecraft_item->components->minecraft_mining_speed;
		}
		if(!empty($data->minecraft_item->components->minecraft_food)){
			$newInstance->food = true;
//			$newInstance->saturation = $data->minecraft_item->components->minecraft_food->saturation_modifier; // TODO: no saturation on behaviour pack?
			$newInstance->nutrition = empty($data->minecraft_item->components->minecraft_food->nutrition) ? 0 : $data->minecraft_item->components->minecraft_food->nutrition;
			$newInstance->can_always_eat = empty($data->minecraft_item->components->minecraft_food->can_always_eat) ? false : $data->minecraft_item->components->minecraft_food->can_always_eat;
		}
//		if(!empty($data->minecraft_item->components->minecraft_residue)){ // TODO: food residue
//			$newInstance->residue = $data->minecraft_item->components->minecraft_residue;
//		}
//		if(!empty($data->minecraft_item->components->minecraft_durable)){ // TODO: durable
//			$newInstance->durable = $data->minecraft_item->components->minecraft_durable;
//		}
//		if(!empty($data->minecraft_item->components->minecraft_armor)){  // TODO: armor
//			$newInstance->armor = $data->minecraft_item->components->minecraft_armor;
//		}
//		if(!empty($data->minecraft_item->components->minecraft_is_block)){ // TODO: itemblock
//			$newInstance->isBlock = $data->minecraft_item->components->minecraft_is_block;
//		}
//		if(!empty($data->minecraft_item->components->minecraft_is_tool)){ // TODO: tool
//			$newInstance->isTool = $data->minecraft_item->components->minecraft_is_tool;
//		}
		$newInstance->add_creative_inventory = true;
		return $newInstance;
	}
}
