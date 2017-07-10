<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Model\AlertableInterface;

/**
 * ReservationService
 *
 * @ORM\Table(name="reservation_service")
 * @ORM\Entity
 */
class ReservationService implements AlertableInterface
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
     * @var string
     *
     * @ORM\Column(name="client_name", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $clientName;

    /**
     * @var string
     * 
     * @ORM\Column(name="facility_name", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $facilityName;

    /**
     * @var string
     * 
     * @ORM\Column(name="facility_address", nullable=true)
     * @Assert\Length(max=255)
     */
    private $facilityAddress;

    /**
     * @var string
     * 
     * @ORM\Column(name="restaurant_menu", nullable=true)
     * @Assert\Length(max=255)
     */
    private $restaurantMenu;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="hosting_plan", nullable=true)
     * @Assert\Length(max=255)
     */
    private $hostingPlan;
    
    /**
     * @var string
     *
     * @ORM\Column(name="pax", type="string", length=25, nullable=true)
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
     * @ORM\Column(name="cost", type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Promotion"})
     */
    private $cost;

    /**
     * @var string
     *
     * @ORM\Column(name="total_price", type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Promotion"})
     */
    private $totalPrice;

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
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $origin;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $destination;
    
    /**
     * @var TransportCarType
     *
     * @ORM\ManyToOne(targetEntity="TransportCarType")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $transportCar;

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

    public function getSerialNumber()
    {
        return sprintf('%s%sv%s-%s', $this->getReservation()->getStartAt()->format('Y'), $this->getReservation()->getId(), $this->getReservation()->getVersion(), $this->getId());
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
     * Set clientName
     *
     * @param string $clientName
     *
     * @return ReservationService
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * Get clientName
     *
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * Set cost
     *
     * @param string $cost
     *
     * @return ReservationService
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set totalPrice
     *
     * @param string $totalPrice
     *
     * @return ReservationService
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set origin
     *
     * @param \AppBundle\Entity\Place $origin
     *
     * @return ReservationService
     */
    public function setOrigin(\AppBundle\Entity\Place $origin = null)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return \AppBundle\Entity\Place
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set destination
     *
     * @param \AppBundle\Entity\Place $destination
     *
     * @return ReservationService
     */
    public function setDestination(\AppBundle\Entity\Place $destination = null)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return \AppBundle\Entity\Place
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set facilityName
     *
     * @param string $facilityName
     *
     * @return ReservationService
     */
    public function setFacilityName($facilityName)
    {
        $this->facilityName = $facilityName;

        return $this;
    }

    /**
     * Get facilityName
     *
     * @return string
     */
    public function getFacilityName()
    {
        return $this->facilityName;
    }

    /**
     * Set hostingPlan
     *
     * @param string $hostingPlan
     *
     * @return ReservationService
     */
    public function setHostingPlan($hostingPlan)
    {
        $this->hostingPlan = $hostingPlan;

        return $this;
    }

    /**
     * Get hostingPlan
     *
     * @return string
     */
    public function getHostingPlan()
    {
        return $this->hostingPlan;
    }

    /**
     * Set facilityAddress
     *
     * @param string $facilityAddress
     *
     * @return ReservationService
     */
    public function setFacilityAddress($facilityAddress)
    {
        $this->facilityAddress = $facilityAddress;

        return $this;
    }

    /**
     * Get facilityAddress
     *
     * @return string
     */
    public function getFacilityAddress()
    {
        return $this->facilityAddress;
    }

    /**
     * Set restaurantMenu
     *
     * @param string $restaurantMenu
     *
     * @return ReservationService
     */
    public function setRestaurantMenu($restaurantMenu)
    {
        $this->restaurantMenu = $restaurantMenu;

        return $this;
    }

    /**
     * Get restaurantMenu
     *
     * @return string
     */
    public function getRestaurantMenu()
    {
        return $this->restaurantMenu;
    }

    /**
     * Set transportCar
     *
     * @param \AppBundle\Entity\TransportCarType $transportCar
     *
     * @return ReservationService
     */
    public function setTransportCar(\AppBundle\Entity\TransportCarType $transportCar = null)
    {
        $this->transportCar = $transportCar;

        return $this;
    }

    /**
     * Get transportCar
     *
     * @return \AppBundle\Entity\TransportCarType
     */
    public function getTransportCar()
    {
        return $this->transportCar;
    }

    /**
     * Set pax
     *
     * @param string $pax
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
     * @return string
     */
    public function getPax()
    {
        return $this->pax;
    }
}
