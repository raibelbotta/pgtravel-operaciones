<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ContractHotelPrice
 *
 * @ORM\Table(name="contract_hotel_price")
 * @ORM\Entity
 */
class ContractHotelPrice
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
     * @ORM\ManyToOne(targetEntity="Contract")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $contract;

    /**
     * @var ContractFacilitySeason
     *
     * @ORM\ManyToOne(targetEntity="ContractFacilitySeason")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $season;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     * @AppBundle\Validator\Constraints\ValidCupo
     */
    private $cupo;

    /**
     * @var ContractFacilityRoom
     *
     * @ORM\ManyToOne(targetEntity="ContractFacilityRoom")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $room;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     * @AppBundle\Validator\Constraints\ValidPlan
     */
    private $plan;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $value;

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
     * Set cupo
     *
     * @param string $cupo
     *
     * @return ContractHotelPrice
     */
    public function setCupo($cupo)
    {
        $this->cupo = $cupo;

        return $this;
    }

    /**
     * Get cupo
     *
     * @return string
     */
    public function getCupo()
    {
        return $this->cupo;
    }

    /**
     * Set plan
     *
     * @param string $plan
     *
     * @return ContractHotelPrice
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return string
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set contract
     *
     * @param \AppBundle\Entity\Contract $contract
     *
     * @return ContractHotelPrice
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
     * Set season
     *
     * @param \AppBundle\Entity\ContractFacilitySeason $season
     *
     * @return ContractHotelPrice
     */
    public function setSeason(\AppBundle\Entity\ContractFacilitySeason $season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return \AppBundle\Entity\ContractFacilitySeason
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set room
     *
     * @param \AppBundle\Entity\ContractFacilityRoom $room
     *
     * @return ContractHotelPrice
     */
    public function setRoom(\AppBundle\Entity\ContractFacilityRoom $room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \AppBundle\Entity\ContractFacilityRoom
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return ContractHotelPrice
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
