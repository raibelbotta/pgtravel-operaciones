<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * ContractCarRentalSeassonDayRange
 *
 * @ORM\Table(name="contract_car_rental_seasson_day_range")
 * @ORM\Entity
 */
class ContractCarRentalSeassonDayRange
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
     * @var ContractCarRentalSeasson
     *
     * @ORM\ManyToOne(targetEntity="ContractCarRentalSeasson", inversedBy="dayRanges")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $seasson;
    
    /**
     * @var int
     *
     * @ORM\Column(name="begin_day", type="integer")
     * @Assert\GreaterThanOrEqual(1)
     */
    private $beginDay;

    /**
     * @var int
     *
     * @ORM\Column(name="end_day", type="integer")
     */
    private $endDay;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validateDays(ExecutionContextInterface $context)
    {
        if ($this->beginDay && $this->endDay && $this->beginDay <= $this->endDay) {
            $context->buildViolation('Incorrect value')
                    ->atPath('endDay')
                    ->addViolation()
                    ;
        }
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
     * Set beginDay
     *
     * @param integer $beginDay
     *
     * @return ContractCarRentalSeassonDayRange
     */
    public function setBeginDay($beginDay)
    {
        $this->beginDay = $beginDay;

        return $this;
    }

    /**
     * Get beginDay
     *
     * @return int
     */
    public function getBeginDay()
    {
        return $this->beginDay;
    }

    /**
     * Set endDay
     *
     * @param integer $endDay
     *
     * @return ContractCarRentalSeassonDayRange
     */
    public function setEndDay($endDay)
    {
        $this->endDay = $endDay;

        return $this;
    }

    /**
     * Get endDay
     *
     * @return int
     */
    public function getEndDay()
    {
        return $this->endDay;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ContractCarRentalSeassonDayRange
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
     * Set seasson
     *
     * @param \AppBundle\Entity\ContractCarRentalSeasson $seasson
     *
     * @return ContractCarRentalSeassonDayRange
     */
    public function setSeasson(\AppBundle\Entity\ContractCarRentalSeasson $seasson)
    {
        $this->seasson = $seasson;

        return $this;
    }

    /**
     * Get seasson
     *
     * @return \AppBundle\Entity\ContractCarRentalSeasson
     */
    public function getSeasson()
    {
        return $this->seasson;
    }
}
