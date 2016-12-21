<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ReservationService
 *
 * @ORM\Table(name="reservation_service")
 * @ORM\Entity
 */
class ReservationService
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Reservation
     *
     * @ORM\ManyToOne(targetEntity="Reservation", inversedBy="services")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $reservation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="pax", type="integer", nullable=true)
     */
    private $pax;

    /**
     * @var string
     *
     * @ORM\Column(name="provider_price", type="decimal", precision=10, scale=2)
     */
    private $supplierPrice;

    /**
     * @var Supplier
     *
     * @ORM\ManyToOne(targetEntity="Supplier")
     * @ORM\JoinColumn(nullable=true, onDelete="set null")
     */
    private $supplier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $endAt;

    /**
     * @var string
     *
     * @ORM\Column(name="internal_notes", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $internalNotes;

    /**
     * @var string
     *
     * @ORM\Column(name="supplier_notes", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $supplierNotes;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_paid", type="boolean", options={"default": false})
     */
    private $isPaid;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_notes", type="text", nullable=true)
     */
    private $payNotes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paid_at", type="datetime", nullable=true)
     */
    private $paidAt;

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

    public function __construct()
    {
        $this->isPaid = false;
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
     * @return ReservationService
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
     * Set description
     *
     * @param string $description
     *
     * @return ReservationService
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set pax
     *
     * @param integer $pax
     *
     * @return ReservationService
     */
    public function setPax($pax)
    {
        $this->pax = $pax;

        return $this;
    }

    /**
     * Get pax
     *
     * @return integer
     */
    public function getPax()
    {
        return $this->pax;
    }

    /**
     * Set supplierPrice
     *
     * @param string $supplierPrice
     *
     * @return ReservationService
     */
    public function setSupplierPrice($supplierPrice)
    {
        $this->supplierPrice = $supplierPrice;

        return $this;
    }

    /**
     * Get supplierPrice
     *
     * @return string
     */
    public function getSupplierPrice()
    {
        return $this->supplierPrice;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return ReservationService
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
     * @return ReservationService
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
     * Set internalNotes
     *
     * @param string $internalNotes
     *
     * @return ReservationService
     */
    public function setInternalNotes($internalNotes)
    {
        $this->internalNotes = $internalNotes;

        return $this;
    }

    /**
     * Get internalNotes
     *
     * @return string
     */
    public function getInternalNotes()
    {
        return $this->internalNotes;
    }

    /**
     * Set supplierNotes
     *
     * @param string $supplierNotes
     *
     * @return ReservationService
     */
    public function setSupplierNotes($supplierNotes)
    {
        $this->supplierNotes = $supplierNotes;

        return $this;
    }

    /**
     * Get supplierNotes
     *
     * @return string
     */
    public function getSupplierNotes()
    {
        return $this->supplierNotes;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ReservationService
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
     * @return ReservationService
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
     * Set reservation
     *
     * @param \AppBundle\Entity\Reservation $reservation
     *
     * @return ReservationService
     */
    public function setReservation(\AppBundle\Entity\Reservation $reservation)
    {
        $this->reservation = $reservation;

        return $this;
    }

    /**
     * Get reservation
     *
     * @return \AppBundle\Entity\Reservation
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * Set supplier
     *
     * @param \AppBundle\Entity\Supplier $supplier
     *
     * @return ReservationService
     */
    public function setSupplier(\AppBundle\Entity\Supplier $supplier = null)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get supplier
     *
     * @return \AppBundle\Entity\Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     *
     * @return ReservationService
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Set payNotes
     *
     * @param string $payNotes
     *
     * @return ReservationService
     */
    public function setPayNotes($payNotes)
    {
        $this->payNotes = $payNotes;

        return $this;
    }

    /**
     * Get payNotes
     *
     * @return string
     */
    public function getPayNotes()
    {
        return $this->payNotes;
    }

    /**
     * Set paidAt
     *
     * @param \DateTime $paidAt
     *
     * @return ReservationService
     */
    public function setPaidAt($paidAt)
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    /**
     * Get paidAt
     *
     * @return \DateTime
     */
    public function getPaidAt()
    {
        return $this->paidAt;
    }
}
