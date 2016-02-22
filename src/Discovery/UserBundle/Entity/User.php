<?php

namespace Discovery\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $firstName;
    /**
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $lastName;
    /**
     * @Assert\Length(min=8)
     */
    protected $plainPassword;
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $passwordExpireAt;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordExpireAt()
    {
        return $this->passwordExpireAt;
    }

    /**
     * @param \DateTime $passwordExpireAt
     */
    public function setPasswordExpireAt($passwordExpireAt)
    {
        $this->passwordExpireAt = $passwordExpireAt;
    }

    public function isPasswordExpired()
    {
        return (($this->passwordExpireAt !== null) && ($this->passwordExpireAt->getTimestamp(
            ) < time()));
    }

    public function getFullName()
    {
        return $this->getFirstName()." ".$this->getLastName();
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
}
