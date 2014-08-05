<?php

namespace Munisense\Zigbee\ZDP\Discovery;
use Munisense\Zigbee\AbstractFrame;
use Munisense\Zigbee\Buffer;
use Munisense\Zigbee\Exception\ZigbeeException;
use Munisense\Zigbee\ZDP\Command;
use Munisense\Zigbee\ZDP\IZDPCommandFrame;
use Munisense\Zigbee\ZDP\Status;

/**
 * Class NodeDescRspCommand
 * @package Munisense\Zigbee\ZDP\Discovery
 *
 * The Node_Desc_rsp is generated by a remote device in response to a
 * Node_Desc_req directed to the remote device.
 */
class NodeDescRspCommand extends AbstractFrame implements IZDPCommandFrame
  {
  private static $allowed_statusses = [Status::SUCCESS, Status::DEVICE_NOT_FOUND, Status::INV_REQUESTTYPE, Status::NO_DESCRIPTOR];

  private $status;
  private $nwk_addr_of_interest;

  /**
   * @var NodeDescriptor $node_descriptor
   */
  private $node_descriptor;

  public static function constructSuccess($nwk_addr_of_interest, NodeDescriptor $node_descriptor)
    {
    $frame = new self;
    $frame->setStatus(Status::SUCCESS);
    $frame->setNwkAddrOfInterest($nwk_addr_of_interest);
    $frame->setNodeDescriptor($node_descriptor);
    return $frame;
    }

  public static function constructFailure($status, $nwk_addr_of_interest)
    {
    $frame = new self;
    $frame->setStatus($status);
    $frame->setNwkAddrOfInterest($nwk_addr_of_interest);
    return $frame;
    }

  public function setFrame($frame)
    {
    $this->setStatus(Buffer::unpackInt8u($frame));
    $this->setNwkAddrOfInterest(Buffer::unpackInt16u($frame));

    if($this->getStatus() == Status::SUCCESS)
      $this->setNodeDescriptor(new NodeDescriptor($frame));
    }

  public function getFrame()
    {
    $frame = "";

    Buffer::packInt8u($frame, $this->getStatus());
    Buffer::packInt16u($frame, $this->getNwkAddrOfInterest());

    if($this->getStatus() == Status::SUCCESS)
      {
      $node_descr_frame = $this->getNodeDescriptor()->getFrame();
      Buffer::packInt8u($frame, strlen($node_descr_frame));
      $frame .= $node_descr_frame;
      }
    else
      // If status is not success, simple descriptor length is 0
      Buffer::packInt8u($frame, 0x00);

    return $frame;
    }

  /**
   * @return int
   */
  public function getStatus()
    {
    return $this->status;
    }

  /**
   * @param $status
   * @throws \Munisense\Zigbee\Exception\ZigbeeException
   */
  public function setStatus($status)
    {
    if(in_array($status, self::$allowed_statusses))
      $this->status = $status;
    else
      throw new ZigbeeException("Invalid status supplied");
    }

  public function displayStatus()
    {
    return Status::displayStatus($this->getStatus());
    }

  /**
   * @return array
   */
  public static function getAllowedStatusses()
    {
    return self::$allowed_statusses;
    }

  /**
   * @param array $allowed_statusses
   */
  public static function setAllowedStatusses($allowed_statusses)
    {
    self::$allowed_statusses = $allowed_statusses;
    }

  /**
   * @return int
   */
  public function getNwkAddrOfInterest()
    {
    return $this->nwk_addr_of_interest;
    }

  /**
   * @param $nwk_address
   * @throws \Munisense\Zigbee\Exception\ZigbeeException
   */
  public function setNwkAddrOfInterest($nwk_address)
    {
    if($nwk_address >= 0x0000 && $nwk_address <= 0xffff)
      $this->nwk_addr_of_interest = $nwk_address;
    else
      throw new ZigbeeException("Invalid nwk address");
    }

  public function displayNwkAddrOfInterest()
    {
    return Buffer::displayInt16u($this->getNwkAddrOfInterest());
    }

  /**
   * @return NodeDescriptor
   */
  public function getNodeDescriptor()
    {
    return $this->node_descriptor;
    }

  /**
   * @param NodeDescriptor $node_descriptor
   */
  public function setNodeDescriptor($node_descriptor)
    {
    $this->node_descriptor = $node_descriptor;
    }

  public function __toString()
    {
    $output = __CLASS__." (length: ".strlen($this->getFrame()).")".PHP_EOL;
    $output .= "|- Status    : ".$this->displayStatus().PHP_EOL;
    $output .= "|- NwkAddr     : ".$this->displayNwkAddrOfInterest().PHP_EOL;

    if($this->getStatus() == Status::SUCCESS)
      {
      $output .= preg_replace("/^   /", "`- ", preg_replace("/^/m", "   ", $this->getNodeDescriptor()));
      }

    return $output;
    }

  /**
   * Returns the Cluster ID of this frame
   *
   * @return int
   */
  public function getClusterId()
    {
    return Command::COMMAND_NODE_DESC_RSP;
    }
  }
