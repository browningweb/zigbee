<?php

namespace Munisense\Zigbee\ZCL\General;
use Munisense\Zigbee\AbstractFrame;
use Munisense\Zigbee\Buffer;
use Munisense\Zigbee\Exception\ZigbeeException;

class WriteAttributeRecord extends AbstractFrame
  {
  private $attribute_id;
  private $datatype_id;
  private $value;
  
  public static function construct($attribute_id, $datatype_id, $value)
    {
    $element = new self;
    $element->setAttributeId($attribute_id);
    $element->setDatatypeId($datatype_id);
    $element->setValue($value);
    return $element;
    }

  public function consumeFrame(&$frame)
    {
    $this->setAttributeId(Buffer::unpackInt16u($frame));
    $this->setDatatypeId(Buffer::unpackInt8u($frame));
    $this->setValue(Buffer::unpackDatatype($frame, $this->getDatatypeId()));
    }

  public function setFrame($frame)
    {
    $this->consumeFrame($frame);

    if(strlen($frame) > 0)
      throw new ZigbeeException("Still data left in frame buffer");
    }

  public function getFrame()
    {
    $frame = "";

    Buffer::packInt16u($frame, $this->getAttributeId());
    Buffer::packInt8u($frame, $this->getDatatypeId());
    Buffer::packDatatype($frame, $this->getDatatypeId(), $this->getValue());

    return $frame;
    }

  public function setAttributeId($attribute_id)
    {
    $attribute_id = intval($attribute_id);
    if($attribute_id < 0x0000 || $attribute_id > 0xffff)
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

  public function setDatatypeId($datatype_id)
    {
    $datatype_id = intval($datatype_id);
    if($datatype_id < 0x00 || $datatype_id > 0xff)
      throw new ZigbeeException("Invalid datatype id");

    $this->datatype_id = $datatype_id;
    }

  public function getDatatypeId()
    {
    return $this->datatype_id;
    }

  public function displayDatatypeId()
    {
    return sprintf("0x%02x", $this->getDatatypeId());
    }

  public function setValue($value)
    {
    $this->value = $value;
    }

  public function getValue()
    {
    return $this->value;
    }

  public function displayValue()
    {
    return Buffer::displayDatatype($this->getDatatypeId(), $this->getValue());
    }

  public function __toString()
    {
    return "AttributeId: ".$this->displayAttributeId().", DatatypeId: ".$this->displayDatatypeId().", Value: ".$this->displayValue();
    }
  }

