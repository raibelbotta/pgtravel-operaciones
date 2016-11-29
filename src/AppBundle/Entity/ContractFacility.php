<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ContractFacility
 *
 * @ORM\Table(name="contract_facility")
 * @ORM\Entity
 */
class ContractFacility
{
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
     * @var string
     *
     * @ORM\Column(name="postal_address", type="text", nullable=true)
     */
    private $postalAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=20, nullable=true)
     */
    private $category;

    /**
     * @var Contract
     *
     * @ORM\ManyToOne(targetEntity="Contract", inversedBy="facilities")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $contract;

    /**
     * @var \Dcotrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractFacilityRoom", mappedBy="facility", cascade={"persist", "remove"})
     */
    private $rooms;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractFacilitySeason", mappedBy="facility", cascade={"persist", "remove"})
     */
    private $seasons;

    /**
     * @var array
     *
     * @ORM\Column(name="active_plans", type="array")
     */
    private $activePlans;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __toString()
    {
        return (string) $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->seasons = new \Doctrine\Common\Collections\ArrayCollection();

        $this->activePlans = array();
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
     * @return ContractFacility
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
     * Set postalAddress
     *
     * @param string $postalAddress
     *
     * @return ContractFacility
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }

    /**
     * Get postalAddress
     *
     * @return string
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return ContractFacility
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set contract
     *
     * @param \AppBundle\Entity\Contract $contract
     *
     * @return ContractFacility
     */
    public function setContract(\AppBundle\Entity\Contract $contract)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \AppBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Add room
     *
     * @param \AppBundle\Entity\ContractFacilityRoom $room
     *
     * @return ContractFacility
     */
    public function addRoom(\AppBundle\Entity\ContractFacilityRoom $room)
    {
        $this->rooms[] = $room;

        $room->setFacility($this);

        return $this;
    }

    /**
     * Remove room
     *
     * @param \AppBundle\Entity\ContractFacilityRoom $room
     */
    public function removeRoom(\AppBundle\Entity\ContractFacilityRoom $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * Get rooms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Add season
     *
     * @param \AppBundle\Entity\ContractFacilitySeason $season
     *
     * @return ContractFacility
     */
    public function addSeason(\AppBundle\Entity\ContractFacilitySeason $season)
    {
        $this->seasons[] = $season;

        $season->setFacility($this);

        return $this;
    }

    /**
     * Remove season
     *
     * @param \AppBundle\Entity\ContractFacilitySeason $season
     */
    public function removeSeason(\AppBundle\Entity\ContractFacilitySeason $season)
    {
        $this->seasons->removeElement($season);
    }

    /**
     * Get seasons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSeasons()
    {
        return $this->seasons;
    }

    /**
     * Set activePlans
     *
     * @param array $activePlans
     *
     * @return ContractFacility
     */
    public function setActivePlans($activePlans)
    {
        $this->activePlans = $activePlans;

        return $this;
    }

    /**
     * Get activePlans
     *
     * @return array
     */
    public function getActivePlans()
    {
        return $this->activePlans;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ContractFacility
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
     * @return ContractFacility
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
}
