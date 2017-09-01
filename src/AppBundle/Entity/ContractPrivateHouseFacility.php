<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ContractPrivateHouseFacility
 *
 * @ORM\Table(name="contract_private_house_facility")
 * @ORM\Entity
 */
class ContractPrivateHouseFacility
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
     * @ORM\ManyToOne(targetEntity="Contract", inversedBy="privateHouseFacilities")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $contract;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

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
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $notes;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractPrivateHousePrice", mappedBy="facility", cascade={"persist", "remove"})
     */
    private $prices;

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
     * @return ContractPrivateHouseFacility
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
     * Set mealPlan
     *
     * @param string $mealPlan
     *
     * @return ContractPrivateHouseFacility
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
     * Set notes
     *
     * @param string $notes
     *
     * @return ContractPrivateHouseFacility
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set contract
     *
     * @param \AppBundle\Entity\Contract $contract
     *
     * @return ContractPrivateHouseFacility
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
     * Constructor
     */
    public function __construct()
    {
        $this->prices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add price
     *
     * @param \AppBundle\Entity\ContractPrivateHousePrice $price
     *
     * @return ContractPrivateHouseFacility
     */
    public function addPrice(\AppBundle\Entity\ContractPrivateHousePrice $price)
    {
        if (!$this->prices->contains($price)) {
            $this->prices[] = $price;
            $price->setFacility($this);
        }

        return $this;
    }

    /**
     * Remove price
     *
     * @param \AppBundle\Entity\ContractPrivateHousePrice $price
     */
    public function removePrice(\AppBundle\Entity\ContractPrivateHousePrice $price)
    {
        $this->prices->removeElement($price);
    }

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrices()
    {
        return $this->prices;
    }
}
