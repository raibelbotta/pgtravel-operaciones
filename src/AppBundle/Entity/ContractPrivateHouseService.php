<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ContractPrivateHouseService
 *
 * @ORM\Table(name="contract_private_house_service")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractPrivateHouseServiceRepository")
 */
class ContractPrivateHouseService
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
     * @ORM\ManyToOne(targetEntity="Contract", inversedBy="privateHouseServices")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $contract;
    
    /**
     * @var string
     *
     * @ORM\Column(name="room_name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $roomName;

    /**
     * @var string
     *
     * @ORM\Column(name="meal_plan", type="string", length=20)
     * @Assert\NotBlank
     * @AppBundle\Validator\Constraints\ValidPlan
     */
    private $mealPlan;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=5, scale=2)
     */
    private $price;

    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="start_at", type="date", nullable=true)
     */
    private $startAt;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="end_at", type="date", nullable=true)
     */
    private $endAt;

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
     * Set roomName
     *
     * @param string $roomName
     *
     * @return ContractPrivateHouseService
     */
    public function setRoomName($roomName)
    {
        $this->roomName = $roomName;

        return $this;
    }

    /**
     * Get roomName
     *
     * @return string
     */
    public function getRoomName()
    {
        return $this->roomName;
    }

    /**
     * Set mealPlan
     *
     * @param string $mealPlan
     *
     * @return ContractPrivateHouseService
     */
    public function setMealPlan($mealPlan)
    {
        $this->mealPlan = $mealPlan;

        return $this;
    }

    /**
     * Get mealPlan
     *
     * @return string
     */
    public function getMealPlan()
    {
        return $this->mealPlan;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return ContractPrivateHouseService
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return ContractPrivateHouseService
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set endAt
     *
     * @param \DateTime $endAt
     *
     * @return ContractPrivateHouseService
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Set contract
     *
     * @param \AppBundle\Entity\Contract $contract
     *
     * @return ContractPrivateHouseService
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
}
