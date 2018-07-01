<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ContractPrivateHousePrice
 *
 * @ORM\Table(name="contract_private_house_price")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractPrivateHousePriceRepository")
 */
class ContractPrivateHousePrice
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
     * @var ContractPrivateHouseFacility
     *
     * @ORM\ManyToOne(targetEntity="ContractPrivateHouseFacility", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $facility;

    /**
     * @var ContractPrivateHouseSeason
     *
     * @ORM\ManyToOne(targetEntity="ContractPrivateHouseSeason", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $seasson;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=6, scale=2)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

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
        return (string) $this->getValue();
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
     * Set value
     *
     * @param string $value
     *
     * @return ContractPrivateHousePrice
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
     * @return ContractPrivateHousePrice
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
     * @return ContractPrivateHousePrice
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
     * Set facility
     *
     * @param \AppBundle\Entity\ContractPrivateHouseFacility $facility
     *
     * @return ContractPrivateHousePrice
     */
    public function setFacility(\AppBundle\Entity\ContractPrivateHouseFacility $facility)
    {
        $this->facility = $facility;

        return $this;
    }

    /**
     * Get facility
     *
     * @return \AppBundle\Entity\ContractPrivateHouseFacility
     */
    public function getFacility()
    {
        return $this->facility;
    }

    /**
     * Set seasson
     *
     * @param \AppBundle\Entity\ContractPrivateHouseSeason $seasson
     *
     * @return ContractPrivateHousePrice
     */
    public function setSeasson(\AppBundle\Entity\ContractPrivateHouseSeason $seasson)
    {
        $this->seasson = $seasson;

        return $this;
    }

    /**
     * Get seasson
     *
     * @return \AppBundle\Entity\ContractPrivateHouseSeason
     */
    public function getSeasson()
    {
        return $this->seasson;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return ContractPrivateHousePrice
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }
}
