<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ContractCarRentalSeasson
 *
 * @ORM\Table(name="contract_car_rental_seasson")
 * @ORM\Entity
 */
class ContractCarRentalSeasson
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
     * @var Contract
     *
     * @ORM\ManyToOne(targetEntity="Contract", inversedBy="carRentalSeassons")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $contract;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name")
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractCarRentalSeassonDate", mappedBy="seasson", cascade={"persist", "remove"})
     */
    private $dates;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractCarRentalSeassonDayRange", mappedBy="seasson", cascade={"persist", "remove"})
     */
    private $dayRanges;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dates = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dayRanges = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }
    
    /**
     * Get id
     *
     * @return int
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
     * @return ContractCarRentalSeasson
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ContractCarRentalSeasson
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
     * Set contract
     *
     * @param \AppBundle\Entity\Contract $contract
     *
     * @return ContractCarRentalSeasson
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
     * Add date
     *
     * @param \AppBundle\Entity\ContractCarRentalSeassonDate $date
     *
     * @return ContractCarRentalSeasson
     */
    public function addDate(\AppBundle\Entity\ContractCarRentalSeassonDate $date)
    {
        if (!$this->dates->contains($date)) {
            $this->dates[] = $date;
            $date->setSeasson($this);
        }

        return $this;
    }

    /**
     * Remove date
     *
     * @param \AppBundle\Entity\ContractCarRentalSeassonDate $date
     */
    public function removeDate(\AppBundle\Entity\ContractCarRentalSeassonDate $date)
    {
        $this->dates->removeElement($date);
    }

    /**
     * Get dates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * Add dayRange
     *
     * @param \AppBundle\Entity\ContractCarRentalSeassonDayRange $dayRange
     *
     * @return ContractCarRentalSeasson
     */
    public function addDayRange(\AppBundle\Entity\ContractCarRentalSeassonDayRange $dayRange)
    {
        if (!$this->dayRanges->contains($dayRange)) {
            $this->dayRanges[] = $dayRange;
            $dayRange->setSeasson($this);
        }

        return $this;
    }

    /**
     * Remove dayRange
     *
     * @param \AppBundle\Entity\ContractCarRentalSeassonDayRange $dayRange
     */
    public function removeDayRange(\AppBundle\Entity\ContractCarRentalSeassonDayRange $dayRange)
    {
        $this->dayRanges->removeElement($dayRange);
    }

    /**
     * Get dayRanges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDayRanges()
    {
        return $this->dayRanges;
    }
}
