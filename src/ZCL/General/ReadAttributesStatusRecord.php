<?php

namespace Munisense\Zigbee\ZCL\General;

use Munisense\Zigbee\AbstractFrame;
use Munisense\Zigbee\Buffer;
use Munisense\Zigbee\Exception\ZigbeeException;
use Munisense\Zigbee\ZCL\ZCLStatus;

class ReadAttributesStatusRecord extends AbstractFrame
  {
  private $attribute_id = 0x0000;
  private $status;
  private $datatype_id;
  private $value;

  public static function constructSuccess($attribute_id, $datatype_id, $value)
    {
    $record = new self;
    $record->setAttributeId($attribute_id);
    $record->setStatus(ZCLStatus::SUCCESS);
    $record->setDatatypeId($datatype_id);
    $record->setValue($value);
    return $record;
    }

  public static function constructFailure($attribute_id, $status)
    {
    $record = new self;
    $record->setAttributeId($attribute_id);
    $record->setStatus($status);
    return $record;
    }

  public function consumeFrame(&$frame)
    {
    $this->setAttributeId(Buffer::unpackInt16u($frame));
    $this->setStatus(Buffer::unpackInt8u($frame));

    if($this->getStatus() == ZCLStatus::SUCCESS)
      {
      $this->setDatatypeId(Buffer::unpackInt8u($frame));
      $this->setValue(Buffer::unpackDatatype($frame, $this->getDatatypeId()));
      }
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
    Buffer::packInt8u($frame, $this->getStatus());

    if($this->getStatus() == ZCLStatus::SUCCESS)
      {
      Buffer::packInt8u($frame, $this->getDatatypeId());
      Buffer::packDatatype($frame, $this->getDatatypeId(), $this->getValue());
      }

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

  /**
   * @param int $status
   * @throws ZigbeeException
   */
  public function setStatus($status)
    {
    $status = intval($status);
    if($status < 0x00 || $status > 0xff)
      throw new ZigbeeException("Invalid status");

    $this->status = $status;
    }

  /**
   * @return mixed
   */
  public function getStatus()
    {
    return $this->status;
    }

  public function displayStatus()
    {
    return ZCLStatus::displayStatus($this->getStatus());
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
    return "AttributeId: ".$this->displayAttributeId().", Status: ".$this->displayStatus().($this->getStatus() == ZCLStatus::SUCCESS ? ", DatatypeId: ".$this->displayDatatypeId().", Value: ".$this->displayValue() : "");
    }
  }

