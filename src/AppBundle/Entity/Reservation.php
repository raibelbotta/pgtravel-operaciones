<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reservation
 *
 * @ORM\Table(name="reservation")
 * @ORM\Entity
 */
class Reservation
{
    const STATE_OFFER = 'offer';
    const STATE_RESERVATION = 'reservation';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=true, onDelete="set null")
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="direct_client_full_name", type="string", length=255, nullable=true)
     */
    private $directClientFullName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="state", length=11)
     * @Assert\Regex("/offer|reservation/")
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="traveler_names", type="text", nullable=true)
     */
    private $travelerNames;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->state = 'offer';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Reservation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Reservation
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set travelerNames
     *
     * @param string $travelerNames
     *
     * @return Reservation
     */
    public function setTravelerNames($travelerNames)
    {
        $this->travelerNames = $travelerNames;

        return $this;
    }

    /**
     * Get travelerNames
     *
     * @return string
     */
    public function getTravelerNames()
    {
        return $this->travelerNames;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Reservation
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Reservation
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set directClientFullName
     *
     * @param string $directClientFullName
     *
     * @return Reservation
     */
    public function setDirectClientFullName($directClientFullName)
    {
        $this->directClientFullName = $directClientFullName;

        return $this;
    }

    /**
     * Get directClientFullName
     *
     * @return string
     */
    public function getDirectClientFullName()
    {
        return $this->directClientFullName;
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return Reservation
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
