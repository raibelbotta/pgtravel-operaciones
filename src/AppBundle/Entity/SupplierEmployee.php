<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SupplierEmployee
 *
 * @ORM\Table(name="supplier_employee")
 * @ORM\Entity
 */
class SupplierEmployee
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
     * @var Supplier
     *
     * @ORM\ManyToOne(targetEntity="Supplier", inversedBy="employees")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $supplier;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="job_position", nullable=true)
     * @Assert\Length(max=255)
     */
    private $jobPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=true)
     * @Assert\Regex("/|F|M/")
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_address", type="text", nullable=true)
     */
    private $postalAddress;

    /**
     * @var \Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType
     *
     * @ORM\Column(name="mobile_phone", type="phone_number", nullable=true)
     */
    private $mobilePhone;

    /**
     * @var \Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType
     *
     * @ORM\Column(name="fixed_phone", type="phone_number", nullable=true)
     */
    private $fixedPhone;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SupplierEmployeeEmail", mappedBy="employee", cascade={"persist", "remove"})
     */
    private $emails;

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
        return (string) $this->getFullName();
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
     * Set fullName
     *
     * @param string $fullName
     *
     * @return SupplierEmployee
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return SupplierEmployee
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set postalAddress
     *
     * @param string $postalAddress
     *
     * @return SupplierEmployee
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }

    /**
     * Get postalAddress
     *
     * @return string
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * Set mobilePhone
     *
     * @param phone_number $mobilePhone
     *
     * @return SupplierEmployee
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }

    /**
     * Get mobilePhone
     *
     * @return phone_number
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * Set fixedPhone
     *
     * @param phone_number $fixedPhone
     *
     * @return SupplierEmployee
     */
    public function setFixedPhone($fixedPhone)
    {
        $this->fixedPhone = $fixedPhone;

        return $this;
    }

    /**
     * Get fixedPhone
     *
     * @return phone_number
     */
    public function getFixedPhone()
    {
        return $this->fixedPhone;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return SupplierEmployee
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
     * @return SupplierEmployee
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
     * @return SupplierEmployee
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
     * Set jobPosition
     *
     * @param string $jobPosition
     *
     * @return SupplierEmployee
     */
    public function setJobPosition($jobPosition)
    {
        $this->jobPosition = $jobPosition;

        return $this;
    }

    /**
     * Get jobPosition
     *
     * @return string
     */
    public function getJobPosition()
    {
        return $this->jobPosition;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->emails = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add email
     *
     * @param \AppBundle\Entity\SupplierEmployeeEmail $email
     *
     * @return SupplierEmployee
     */
    public function addEmail(\AppBundle\Entity\SupplierEmployeeEmail $email)
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setEmployee($this);
        }

        return $this;
    }

    /**
     * Remove email
     *
     * @param \AppBundle\Entity\SupplierEmployeeEmail $email
     */
    public function removeEmail(\AppBundle\Entity\SupplierEmployeeEmail $email)
    {
        $this->emails->removeElement($email);
    }

    /**
     * Get emails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmails()
    {
        return $this->emails;
    }
}
