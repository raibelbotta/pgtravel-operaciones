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
     * @ORM\Column(name="type", type="string", length=20)
     * @AppBundle\Validator\Constraints\ValidServiceType
     */
    private $model;

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
     * @var integer
     *
     * @ORM\Column(name="nights", type="integer", nullable=true)
     */
    private $nights;

    /**
     * @var string
     * 
     * @ORM\Column(name="supplier_unit_price", type="decimal", precision=10, scale=2)
     */
    private $supplierUnitPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="supplier_price", type="decimal", precision=10, scale=2)
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
     * @ORM\Column(name="is_notified", type="boolean", options={"default": false})
     */
    private $isNotified;

    /**
     * @var string
     * 
     * @ORM\Column(name="supplier_reference", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $supplierReference;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_notes", type="text", nullable=true)
     */
    private $payNotes;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ReservationServicePayAttachment",
     *      mappedBy="service", cascade={"persist", "remove"})
     */
    private $payAttachments;

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
        $this->isNotified = false;
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
     * Set isNotified
     *
     * @param boolean $isNotified
     *
     * @return ReservationService
     */
    public function setIsNotified($isNotified)
    {
        $this->isNotified = $isNotified;

        return $this;
    }

    /**
     * Get isNotified
     *
     * @return boolean
     */
    public function getIsNotified()
    {
        return $this->isNotified;
    }

    /**
     * Set supplierReference
     *
     * @param string $supplierReference
     *
     * @return ReservationService
     */
    public function setSupplierReference($supplierReference)
    {
        $this->supplierReference = $supplierReference;

        return $this;
    }

    /**
     * Get supplierReference
     *
     * @return string
     */
    public function getSupplierReference()
    {
        return $this->supplierReference;
    }

    /**
     * Add payAttachment
     *
     * @param \AppBundle\Entity\ReservationServicePayAttachment $payAttachment
     *
     * @return ReservationService
     */
    public function addPayAttachment(\AppBundle\Entity\ReservationServicePayAttachment $payAttachment)
    {
        $this->payAttachments[] = $payAttachment;

        $payAttachment->setService($this);
        
        return $this;
    }

    /**
     * Remove payAttachment
     *
     * @param \AppBundle\Entity\ReservationServicePayAttachment $payAttachment
     */
    public function removePayAttachment(\AppBundle\Entity\ReservationServicePayAttachment $payAttachment)
    {
        $this->payAttachments->removeElement($payAttachment);
    }

    /**
     * Get payAttachments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayAttachments()
    {
        return $this->payAttachments;
    }

    /**
     * Set nights
     *
     * @param integer $nights
     *
     * @return ReservationService
     */
    public function setNights($nights)
    {
        $this->nights = $nights;

        return $this;
    }

    /**
     * Get nights
     *
     * @return integer
     */
    public function getNights()
    {
        return $this->nights;
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
     * Set model
     *
     * @param string $model
     *
     * @return ReservationService
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }


    /**
     * Set supplierUnitPrice
     *
     * @param string $supplierUnitPrice
     *
     * @return ReservationService
     */
    public function setSupplierUnitPrice($supplierUnitPrice)
    {
        $this->supplierUnitPrice = $supplierUnitPrice;

        return $this;
    }

    /**
     * Get supplierUnitPrice
     *
     * @return string
     */
    public function getSupplierUnitPrice()
    {
        return $this->supplierUnitPrice;
    }
}
