<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Reservation
 *
 * @ORM\Table(name="reservation")
 * @ORM\Entity
 * @Vich\Uploadable
 * @AppBundle\Validator\Constraints\OfferHasOneServiceAtLeast
 */
class Reservation
{
    const STATE_OFFER = 'offer';
    const STATE_RESERVATION = 'reservation';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=true, onDelete="set null")
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="direct_client_full_name", type="string", length=255, nullable=true)
     */
    private $directClientFullName;

    /**
     * @var string
     *
     * @ORM\Column(name="direct_client_email", type="string", length=255, nullable=true)
     * @Assert\Email
     */
    private $directClientEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="direct_client_mobile_phone", type="phone_number", nullable=true)
     */
    private $directClientMobilePhone;

    /**
     * @var string
     *
     * @ORM\Column(name="direct_client_postal_address", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $directClientPostalAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="notification_line", type="string", length=20, nullable=true)
     * @Assert\Regex("/|sms|email|phone-call/")
     */
    private $notificationLine;

    /**
     * @var ClientContact
     *
     * @ORM\ManyToOne(targetEntity="ClientContact")
     * @ORM\JoinColumn(nullable=true, onDelete="set null")
     */
    private $notificationContact;
    
    /**
     * @var string
     *
     * @ORM\Column(name="state", length=11)
     * @Assert\Regex("/offer|reservation/")
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="traveler_names", type="text", nullable=true)
     */
    private $travelerNames;

    /**
     * @var string
     *
     * @ORM\Column(name="flies_data", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $fliesData;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ReservationService", mappedBy="reservation", cascade={"persist", "remove"})
     */
    private $services;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ReservationAdministrativeCharge", mappedBy="reservation", cascade={"persist", "remove"})
     */
    private $administrativeCharges;

    /**
     * @var string
     *
     * @ORM\Column(name="offer_summary_filename", type="string", length=255, nullable=true)
     */
    private $offerSummaryFilename;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="offer_summaries", fileNameProperty="offerSummaryFilename")
     * @Assert\File()
     */
    private $offerSummaryFile;

    /**
     * @var string
     *
     * @ORM\Column(name="offer_summary_original_filename", type="string", length=255, nullable=true)
     */
    private $offerSummaryOriginalFilename;

    /**
     * @var string
     *
     * @ORM\Column(name="percent_applied", type="string", length=10)
     */
    private $percentApplied;

    /**
     * @var string
     *
     * @ORM\Column(name="client_charge", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $clientCharge;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_cancelled", type="boolean", options={"default": false})
     */
    private $isCancelled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paid_at", type="datetime", nullable=true)
     */
    private $paidAt;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_notes", type="text", nullable=true)
     */
    private $payNotes;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ReservationPayAttachment", mappedBy="reservation", cascade={"persist", "remove"})
     */
    private $payAttachments;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $operator;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
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
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();
        $this->administrativeAharges = new \Doctrine\Common\Collections\ArrayCollection();
        $this->payAttachments = new \Doctrine\Common\Collections\ArrayCollection();
        
        $this->state = 'offer';
        $this->isCancelled = false;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * @param File $file
     * @return Reservation
     */
    public function setOfferSummaryFile(File $file)
    {
        $this->offerSummaryFile = $file;

        if (null !== $file ) {
            $this->updatedAt = new \DateTime('now');

            if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $this->offerSummaryOriginalFilename = $file->getClientOriginalName();
            }
        }

        return $this;
    }

    /**
     * @return File
     */
    public function getOfferSummaryFile()
    {
        return $this->offerSummaryFile;
    }

    public function getClientType()
    {
        return null === $this->getId() ? 'registered' : (null !== $this->client ? 'registered' : 'direct');
    }

    public function setClientType($value)
    {
    }

    public function getStartAt()
    {
        if (0 === $this->services->count()) {
            return null;
        }

        $startAt = $this->services[0]->getStartAt();
        for ($i = 1; $i < $this->services->count(); $i++) {
            if ($startAt > $this->services[$i]->getStartAt()) {
                $startAt = $this->services[$i]->getStartAt();
            }
        }

        return $startAt;
    }

    public function getEndAt()
    {
        if (0 === $this->services->count()) {
            return null;
        }

        $endAt = $this->services[0]->getEndAt();
        for ($i = 1; $i < $this->services->count(); $i++) {
            if ($endAt < $this->services[$i]->getEndAt()) {
                $endAt = $this->services[$i]->getEndAt();
            }
        }

        return $endAt;
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
     * @return Reservation
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
     * Set state
     *
     * @param string $state
     *
     * @return Reservation
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set travelerNames
     *
     * @param string $travelerNames
     *
     * @return Reservation
     */
    public function setTravelerNames($travelerNames)
    {
        $this->travelerNames = $travelerNames;

        return $this;
    }

    /**
     * Get travelerNames
     *
     * @return string
     */
    public function getTravelerNames()
    {
        return $this->travelerNames;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Reservation
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
     * @return Reservation
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
     * Set directClientFullName
     *
     * @param string $directClientFullName
     *
     * @return Reservation
     */
    public function setDirectClientFullName($directClientFullName)
    {
        $this->directClientFullName = $directClientFullName;

        return $this;
    }

    /**
     * Get directClientFullName
     *
     * @return string
     */
    public function getDirectClientFullName()
    {
        return $this->directClientFullName;
    }

    /**
     * Set client
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return Reservation
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set notificationLine
     *
     * @param string $notificationLine
     *
     * @return Reservation
     */
    public function setNotificationLine($notificationLine)
    {
        $this->notificationLine = $notificationLine;

        return $this;
    }

    /**
     * Get notificationLine
     *
     * @return string
     */
    public function getNotificationLine()
    {
        return $this->notificationLine;
    }

    /**
     * Set notificationContact
     *
     * @param \AppBundle\Entity\ClientContact $notificationContact
     *
     * @return Reservation
     */
    public function setNotificationContact(\AppBundle\Entity\ClientContact $notificationContact = null)
    {
        $this->notificationContact = $notificationContact;

        return $this;
    }

    /**
     * Get notificationContact
     *
     * @return \AppBundle\Entity\ClientContact
     */
    public function getNotificationContact()
    {
        return $this->notificationContact;
    }

    /**
     * Add service
     *
     * @param \AppBundle\Entity\ReservationService $service
     *
     * @return Reservation
     */
    public function addService(\AppBundle\Entity\ReservationService $service)
    {
        $this->services[] = $service;

        $service->setReservation($this);

        return $this;
    }

    /**
     * Remove service
     *
     * @param \AppBundle\Entity\ReservationService $service
     */
    public function removeService(\AppBundle\Entity\ReservationService $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Add administrativeCharge
     *
     * @param \AppBundle\Entity\ReservationAdministrativeCharge $administrativeCharge
     *
     * @return Reservation
     */
    public function addAdministrativeCharge(\AppBundle\Entity\ReservationAdministrativeCharge $administrativeCharge)
    {
        $this->administrativeCharges[] = $administrativeCharge;

        $administrativeCharge->setReservation($this);

        return $this;
    }

    /**
     * Remove administrativeCharge
     *
     * @param \AppBundle\Entity\ReservationAdministrativeCharge $administrativeCharge
     */
    public function removeAdministrativeCharge(\AppBundle\Entity\ReservationAdministrativeCharge $administrativeCharge)
    {
        $this->administrativeCharges->removeElement($administrativeCharge);
    }

    /**
     * Get administrativeCharges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdministrativeCharges()
    {
        return $this->administrativeCharges;
    }

    /**
     * Set offerSummaryFilename
     *
     * @param string $offerSummaryFilename
     *
     * @return Reservation
     */
    public function setOfferSummaryFilename($offerSummaryFilename)
    {
        $this->offerSummaryFilename = $offerSummaryFilename;

        return $this;
    }

    /**
     * Get offerSummaryFilename
     *
     * @return string
     */
    public function getOfferSummaryFilename()
    {
        return $this->offerSummaryFilename;
    }

    /**
     * Set clientCharge
     *
     * @param string $clientCharge
     *
     * @return Reservation
     */
    public function setClientCharge($clientCharge)
    {
        $this->clientCharge = $clientCharge;

        return $this;
    }

    /**
     * Get clientCharge
     *
     * @return string
     */
    public function getClientCharge()
    {
        return $this->clientCharge;
    }

    /**
     * Set isCancelled
     *
     * @param boolean $isCancelled
     *
     * @return Reservation
     */
    public function setIsCancelled($isCancelled)
    {
        $this->isCancelled = $isCancelled;

        return $this;
    }

    /**
     * Get isCancelled
     *
     * @return boolean
     */
    public function getIsCancelled()
    {
        return $this->isCancelled;
    }

    /**
     * Set payNotes
     *
     * @param string $payNotes
     *
     * @return Reservation
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
     * @return Reservation
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
     * Add payAttachment
     *
     * @param \AppBundle\Entity\ReservationPayAttachment $payAttachment
     *
     * @return Reservation
     */
    public function addPayAttachment(\AppBundle\Entity\ReservationPayAttachment $payAttachment)
    {
        $this->payAttachments[] = $payAttachment;

        $payAttachment->setReservation($this);

        return $this;
    }

    /**
     * Remove payAttachment
     *
     * @param \AppBundle\Entity\ReservationPayAttachment $payAttachment
     */
    public function removePayAttachment(\AppBundle\Entity\ReservationPayAttachment $payAttachment)
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
     * Set operator
     *
     * @param \AppBundle\Entity\User $operator
     *
     * @return Reservation
     */
    public function setOperator(\AppBundle\Entity\User $operator = null)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator
     *
     * @return \AppBundle\Entity\User
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set fliesData
     *
     * @param string $fliesData
     *
     * @return Reservation
     */
    public function setFliesData($fliesData)
    {
        $this->fliesData = $fliesData;

        return $this;
    }

    /**
     * Get fliesData
     *
     * @return string
     */
    public function getFliesData()
    {
        return $this->fliesData;
    }

    /**
     * Set directClientEmail
     *
     * @param string $directClientEmail
     *
     * @return Reservation
     */
    public function setDirectClientEmail($directClientEmail)
    {
        $this->directClientEmail = $directClientEmail;

        return $this;
    }

    /**
     * Get directClientEmail
     *
     * @return string
     */
    public function getDirectClientEmail()
    {
        return $this->directClientEmail;
    }

    /**
     * Set directClientMobilePhone
     *
     * @param phone_number $directClientMobilePhone
     *
     * @return Reservation
     */
    public function setDirectClientMobilePhone($directClientMobilePhone)
    {
        $this->directClientMobilePhone = $directClientMobilePhone;

        return $this;
    }

    /**
     * Get directClientMobilePhone
     *
     * @return phone_number
     */
    public function getDirectClientMobilePhone()
    {
        return $this->directClientMobilePhone;
    }

    /**
     * Set directClientPostalAddress
     *
     * @param string $directClientPostalAddress
     *
     * @return Reservation
     */
    public function setDirectClientPostalAddress($directClientPostalAddress)
    {
        $this->directClientPostalAddress = $directClientPostalAddress;

        return $this;
    }

    /**
     * Get directClientPostalAddress
     *
     * @return string
     */
    public function getDirectClientPostalAddress()
    {
        return $this->directClientPostalAddress;
    }

    /**
     * Set offerSummaryOriginalFilename
     *
     * @param string $offerSummaryOriginalFilename
     *
     * @return Reservation
     */
    public function setOfferSummaryOriginalFilename($offerSummaryOriginalFilename)
    {
        $this->offerSummaryOriginalFilename = $offerSummaryOriginalFilename;

        return $this;
    }

    /**
     * Get offerSummaryOriginalFilename
     *
     * @return string
     */
    public function getOfferSummaryOriginalFilename()
    {
        return $this->offerSummaryOriginalFilename;
    }

    /**
     * Set percentApplied
     *
     * @param string $percentApplied
     *
     * @return Reservation
     */
    public function setPercentApplied($percentApplied)
    {
        $this->percentApplied = $percentApplied;

        return $this;
    }

    /**
     * Get percentApplied
     *
     * @return string
     */
    public function getPercentApplied()
    {
        return $this->percentApplied;
    }
}
