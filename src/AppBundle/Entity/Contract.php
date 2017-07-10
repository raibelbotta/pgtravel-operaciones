<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Contract
 *
 * @ORM\Table(name="contract")
 * @ORM\Entity
 */
class Contract
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
     * @var string
     *
     * @ORM\Column(name="model", type="string", length=100)
     * @AppBundle\Validator\Constraints\ValidServiceType
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="extra_conditions", type="text", nullable=true)
     */
    private $extraConditions;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="signed_at", type="date", nullable=true)
     */
    private $signedAt;

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
     * @var Contract
     *
     * @ORM\ManyToOne(targetEntity="Supplier", inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $supplier;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractTopService", mappedBy="contract", cascade={"persist", "remove"})
     */
    private $topServices;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractPrivateHouseService", mappedBy="contract", cascade={"persist", "remove"})
     */
    private $privateHouseServices;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractCarRentalCategory", mappedBy="contract", cascade={"persist", "remove"})
     */
    private $carRentalCategories;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractCarRentalSeasson", mappedBy="contract", cascade={"persist", "remove"})
     */
    private $carRentalSeassons;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractAttachment", mappedBy="contract", cascade={"persist", "remove"})
     */
    private $attachments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ContractFacility", mappedBy="contract", cascade={"persist", "remove"})
     */
    private $facilities;

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
     * Constructor
     */
    public function __construct()
    {
        $this->topServices = new ArrayCollection();
        $this->privateHouseServices = new ArrayCollection();
        $this->carRentalCategories = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->facilities = new ArrayCollection();
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
     * Set model
     *
     * @param string $model
     *
     * @return Contract
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
     * Set name
     *
     * @param string $name
     *
     * @return Contract
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
     * @return Contract
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
     * Set notes
     *
     * @param string $notes
     *
     * @return Contract
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
     * Set signedAt
     *
     * @param \DateTime $signedAt
     *
     * @return Contract
     */
    public function setSignedAt($signedAt)
    {
        $this->signedAt = $signedAt;

        return $this;
    }

    /**
     * Get signedAt
     *
     * @return \DateTime
     */
    public function getSignedAt()
    {
        return $this->signedAt;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return Contract
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
     * @return Contract
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
     * @return Contract
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
     * @return Contract
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
     * Set supplier
     *
     * @param \AppBundle\Entity\Supplier $supplier
     *
     * @return Contract
     */
    public function setSupplier(\AppBundle\Entity\Supplier $supplier)
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
     * Add topService
     *
     * @param \AppBundle\Entity\ContractTopService $topService
     *
     * @return Contract
     */
    public function addTopService(\AppBundle\Entity\ContractTopService $topService)
    {
        $this->topServices[] = $topService;

        $topService->setContract($this);

        return $this;
    }

    /**
     * Remove topService
     *
     * @param \AppBundle\Entity\ContractTopService $topService
     */
    public function removeTopService(\AppBundle\Entity\ContractTopService $topService)
    {
        $this->topServices->removeElement($topService);
    }

    /**
     * Get topServices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTopServices()
    {
        return $this->topServices;
    }

    /**
     * Add attachment
     *
     * @param \AppBundle\Entity\ContractAttachment $attachment
     *
     * @return Contract
     */
    public function addAttachment(\AppBundle\Entity\ContractAttachment $attachment)
    {
        $this->attachments[] = $attachment;

        $attachment->setContract($this);

        return $this;
    }

    /**
     * Remove attachment
     *
     * @param \AppBundle\Entity\ContractAttachment $attachment
     */
    public function removeAttachment(\AppBundle\Entity\ContractAttachment $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * Get attachments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Add facility
     *
     * @param \AppBundle\Entity\ContractFacility $facility
     *
     * @return Contract
     */
    public function addFacility(\AppBundle\Entity\ContractFacility $facility)
    {
        $this->facilities[] = $facility;

        $facility->setContract($this);

        return $this;
    }

    /**
     * Remove facility
     *
     * @param \AppBundle\Entity\ContractFacility $facility
     */
    public function removeFacility(\AppBundle\Entity\ContractFacility $facility)
    {
        $this->facilities->removeElement($facility);
    }

    /**
     * Get facilities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * Set extraConditions
     *
     * @param string $extraConditions
     *
     * @return Contract
     */
    public function setExtraConditions($extraConditions)
    {
        $this->extraConditions = $extraConditions;

        return $this;
    }

    /**
     * Get extraConditions
     *
     * @return string
     */
    public function getExtraConditions()
    {
        return $this->extraConditions;
    }

    /**
     * Add privateHouseService
     *
     * @param \AppBundle\Entity\ContractPrivateHouseService $privateHouseService
     *
     * @return Contract
     */
    public function addPrivateHouseService(\AppBundle\Entity\ContractPrivateHouseService $privateHouseService)
    {
        if (!$this->privateHouseServices->contains($privateHouseService)) {
            $this->privateHouseServices[] = $privateHouseService;
            $privateHouseService->setContract($this);
        }

        return $this;
    }

    /**
     * Remove privateHouseService
     *
     * @param \AppBundle\Entity\ContractPrivateHouseService $privateHouseService
     */
    public function removePrivateHouseService(\AppBundle\Entity\ContractPrivateHouseService $privateHouseService)
    {
        $this->privateHouseServices->removeElement($privateHouseService);
    }

    /**
     * Get privateHouseServices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrivateHouseServices()
    {
        return $this->privateHouseServices;
    }

    /**
     * Add carRentalCategory
     *
     * @param \AppBundle\Entity\ContractCarRentalCategory $carRentalCategory
     *
     * @return Contract
     */
    public function addCarRentalCategory(\AppBundle\Entity\ContractCarRentalCategory $carRentalCategory)
    {
        if (!$this->carRentalCategories->contains($carRentalCategory)) {
            $this->carRentalCategories[] = $carRentalCategory;
            $carRentalCategory->setContract($this);
        }

        return $this;
    }

    /**
     * Remove carRentalCategory
     *
     * @param \AppBundle\Entity\ContractCarRentalCategory $carRentalCategory
     */
    public function removeCarRentalCategory(\AppBundle\Entity\ContractCarRentalCategory $carRentalCategory)
    {
        $this->carRentalCategories->removeElement($carRentalCategory);
    }

    /**
     * Get carRentalCategories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarRentalCategories()
    {
        return $this->carRentalCategories;
    }

    /**
     * Add carRentalSeasson
     *
     * @param \AppBundle\Entity\ContractCarRentalSeasson $carRentalSeasson
     *
     * @return Contract
     */
    public function addCarRentalSeasson(\AppBundle\Entity\ContractCarRentalSeasson $carRentalSeasson)
    {
        if (!$this->carRentalSeassons->contains($carRentalSeasson)) {
            $this->carRentalSeassons[] = $carRentalSeasson;
            $carRentalSeasson->setContract($this);
        }

        return $this;
    }

    /**
     * Remove carRentalSeasson
     *
     * @param \AppBundle\Entity\ContractCarRentalSeasson $carRentalSeasson
     */
    public function removeCarRentalSeasson(\AppBundle\Entity\ContractCarRentalSeasson $carRentalSeasson)
    {
        $this->carRentalSeassons->removeElement($carRentalSeasson);
    }

    /**
     * Get carRentalSeassons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarRentalSeassons()
    {
        return $this->carRentalSeassons;
    }
}
