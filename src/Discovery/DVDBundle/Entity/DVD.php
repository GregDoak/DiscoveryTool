<?php

namespace Discovery\DVDBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="dvds")
 */
class DVD
{
    /**
     * @ORM\Column(type="string", length=15)
     * @ORM\Id
     */
    protected $imdbId;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    protected $opacURL;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdOn;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedOn;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $processed;

    /**
     * @ORM\Column(type="integer", length=11)
     */
    protected $attemptCount;

    /**
     * @return mixed
     */
    public function getImdbId()
    {
        return $this->imdbId;
    }

    /**
     * @param mixed $imdbId
     */
    public function setImdbId($imdbId)
    {
        $this->imdbId = $imdbId;
    }

    /**
     * @return mixed
     */
    public function getOpacURL()
    {
        return $this->opacURL;
    }

    /**
     * @param mixed $opacURL
     */
    public function setOpacURL($opacURL)
    {
        $this->opacURL = $opacURL;
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
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param mixed $updatedOn
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;
    }

    /**
     * @return mixed
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param mixed $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return mixed
     */
    public function getAttemptCount()
    {
        return $this->attemptCount;
    }

    /**
     * @param mixed $attemptCount
     */
    public function setAttemptCount($attemptCount)
    {
        $this->attemptCount = $attemptCount;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedOnValue()
    {
        $this->createdOn = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     */
    public function setProcessedValue()
    {
        $this->processed = false;
    }

    /**
     * @ORM\PrePersist
     */
    public function setAttemptCountValue()
    {
        $this->attemptCount = 0;
    }
}
