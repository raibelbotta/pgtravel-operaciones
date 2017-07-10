<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * ContractCarRentalSeassonDate
 *
 * @ORM\Table(name="contract_car_rental_seasson_date")
 * @ORM\Entity
 */
class ContractCarRentalSeassonDate
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
     * @ORM\ManyToOne(targetEntity="ContractCarRentalSeasson", inversedBy="dates")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $seasson;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime")
     */
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="datetime")
     */
    private $endAt;

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
    public function validateDates(ExecutionContextInterface $context)
    {
        if ($this->startAt && $this->endAt && ($this->startAt > $this->endAt)) {
            $context->buildViolation('Dates are incorrect')
                    ->atPath('startAt')
                    ->addViolation()
                    ;
        }
    }

    public function __toString()
    {
        return sprintf('%s to %s', $this->getStartAt()->format('F j'), $this->getEndAt()->format('F j'));
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
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return ContractCarRentalSeassonDate
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
     * @return ContractCarRentalSeassonDate
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ContractCarRentalSeassonDate
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
     * @return ContractCarRentalSeassonDate
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
