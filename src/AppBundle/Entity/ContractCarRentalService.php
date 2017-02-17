<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ContractCarRentalService
 *
 * @ORM\Table(name="contract_car_rental_service")
 * @ORM\Entity
 */
class ContractCarRentalService
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
     * @var Contract
     *
     * @ORM\ManyToOne(targetEntity="Contract", inversedBy="rentalCarServices")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $contract;

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
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=5, scale=2)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var RentCarType
     *
     * @ORM\ManyToOne(targetEntity="RentCarType")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $carType;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ContractCarRentalService
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
     * @return ContractCarRentalService
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
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return ContractCarRentalService
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
     * @return ContractCarRentalService
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
     * Set price
     *
     * @param string $price
     *
     * @return ContractCarRentalService
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
     * Set name
     *
     * @param string $name
     *
     * @return ContractCarRentalService
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
     * Set contract
     *
     * @param \AppBundle\Entity\Contract $contract
     *
     * @return ContractCarRentalService
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
     * Set carType
     *
     * @param \AppBundle\Entity\RentCarType $carType
     *
     * @return ContractCarRentalService
     */
    public function setCarType(\AppBundle\Entity\RentCarType $carType = null)
    {
        $this->carType = $carType;

        return $this;
    }

    /**
     * Get carType
     *
     * @return \AppBundle\Entity\RentCarType
     */
    public function getCarType()
    {
        return $this->carType;
    }
}
