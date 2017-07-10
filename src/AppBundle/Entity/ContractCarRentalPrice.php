<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ContractCarRentalPrice
 *
 * @ORM\Table(name="contract_car_rental_price")
 * @ORM\Entity
 */
class ContractCarRentalPrice
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
     * @var ContractCarRentalCategory
     *
     * @ORM\ManyToOne(targetEntity="ContractCarRentalCategory")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $category;

    /**
     * @var ContractCarRentalSeassonDayRange
     *
     * @ORM\ManyToOne(targetEntity="ContractCarRentalSeassonDayRange")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $dayRange;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=5, scale=2)
     */
    private $value;

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
     * Set value
     *
     * @param string $value
     *
     * @return ContractCarRentalPrice
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

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ContractCarRentalPrice
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
     * @return ContractCarRentalPrice
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
     * Set contract
     *
     * @param \AppBundle\Entity\Contract $contract
     *
     * @return ContractCarRentalPrice
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
     * Set category
     *
     * @param \AppBundle\Entity\ContractCarRentalCategory $category
     *
     * @return ContractCarRentalPrice
     */
    public function setCategory(\AppBundle\Entity\ContractCarRentalCategory $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\ContractCarRentalCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set dayRange
     *
     * @param \AppBundle\Entity\ContractCarRentalSeassonDayRange $dayRange
     *
     * @return ContractCarRentalPrice
     */
    public function setDayRange(\AppBundle\Entity\ContractCarRentalSeassonDayRange $dayRange)
    {
        $this->dayRange = $dayRange;

        return $this;
    }

    /**
     * Get dayRange
     *
     * @return \AppBundle\Entity\ContractCarRentalSeassonDayRange
     */
    public function getDayRange()
    {
        return $this->dayRange;
    }
}
