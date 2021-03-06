<?php

namespace Munisense\Zigbee\ZCL\General;
use Munisense\Zigbee\AbstractFrame;
use Munisense\Zigbee\Buffer;
use Munisense\Zigbee\Exception\ZigbeeException;

class AttributeRecord extends AbstractFrame
  {
  const DIRECTION_SERVER_TO_CLIENT = 0x00;
  const DIRECTION_CLIENT_TO_SERVER = 0x01;

  private $direction = self::DIRECTION_SERVER_TO_CLIENT;
  private $attribute_id;

  public static function construct($direction, $attribute_id)
    {
    $element = new self;
    $element->setDirection($direction);
    $element->setAttributeId($attribute_id);
    return $element;
    }

  public function consumeFrame(&$frame)
    {
    $this->setDirection(Buffer::unpackInt8u($frame));
    $this->setAttributeId(Buffer::unpackInt16u($frame));
    }

  public function setFrame($frame)
    {
    $this->consumeFrame($frame);
    }

  public function getFrame()
    {
    $frame = "";

    Buffer::packInt8u($frame, $this->getDirection());
    Buffer::packInt16u($frame, $this->getAttributeId());

    return $frame;
    }

  public function setDirection($direction)
    {
    $direction = intval($direction);
    if($direction < 0x00 || $direction > 0x01)
      throw new ZigbeeException("Invalid direction");

    $this->direction = $direction;
    }

  public function getDirection()
    {
    return $this->direction;
    }

  public function displayDirection()
    {
    return sprintf("0x%02x", $this->getDirection());
    }

  /**
   * @param $attribute_id
   * @throws \Munisense\Zigbee\Exception\ZigbeeException
   */
  public function setAttributeId($attribute_id)
    {
    $attribute_id = intval($attribute_id);
    if($attribute_id < 0x00 || $attribute_id > 0xffff)
      throw new ZigbeeException("Invalid attribute id");

    $this->attribute_id = $attribute_id;
    }

  public function getAttributeId()
    {
    return $this->attribute_id;
    }

  public function displayAttributeId()
    {
    return sprintf("0x%04x", $this->getAttributeId());
    }

  public function __toString()
    {
    return "Direction: ".$this->displayDirection().", AttributeId: ".$this->displayAttributeId();
    }
  }

