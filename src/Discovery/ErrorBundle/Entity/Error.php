<?php

namespace Discovery\ErrorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="errors")
 */
class Error
{
    /**
     * @ORM\Column(type="integer", length=15)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    protected $baseTable;

    /**
     * @ORM\Column(type="string", length=30)
     */
    protected $baseTableID;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdOn;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    protected $message;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getBaseTable()
    {
        return $this->baseTable;
    }

    /**
     * @param mixed $baseTable
     */
    public function setBaseTable($baseTable)
    {
        $this->baseTable = $baseTable;
    }

    /**
     * @return mixed
     */
    public function getBaseTableID()
    {
        return $this->baseTableID;
    }

    /**
     * @param mixed $baseTableID
     */
    public function setBaseTableID($baseTableID)
    {
        $this->baseTableID = $baseTableID;
    }

    /**
     * @return mixed
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param mixed $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedOnValue()
    {
        $this->createdOn = new \DateTime();
    }
}
