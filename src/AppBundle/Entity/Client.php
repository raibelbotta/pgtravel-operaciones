<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Client
 *
 * @ORM\Table(name="client")
 * @ORM\Entity
 */
class Client
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(name="postal_address", type="string", length=255, nullable=true)
     */
    private $postalAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="fixed_phone", type="phone_number", nullable=true)
     */
    private $fixedPhone;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ClientContact", mappedBy="client", cascade={"persist", "remove"})
     */
    private $contacts;

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
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Client
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Client
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
     * @return Client
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
     * Add contact
     *
     * @param \AppBundle\Entity\ClientContact $contact
     *
     * @return Client
     */
    public function addContact(\AppBundle\Entity\ClientContact $contact)
    {
        $this->contacts[] = $contact;

        $contact->setClient($this);
        
        return $this;
    }

    /**
     * Remove contact
     *
     * @param \AppBundle\Entity\ClientContact $contact
     */
    public function removeContact(\AppBundle\Entity\ClientContact $contact)
    {
        $this->contacts->removeElement($contact);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set postalAddress
     *
     * @param string $postalAddress
     *
     * @return Client
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
     * Set fixedPhone
     *
     * @param phone_number $fixedPhone
     *
     * @return Client
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
}
