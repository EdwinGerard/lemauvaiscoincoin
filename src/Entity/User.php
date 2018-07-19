<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $userName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string|null
     */
    protected $confirmationToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="creator", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $averageNote;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="seller", orphanRemoval=true)
     */
    private $reviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="customer", orphanRemoval=true)
     */
    private $reviewsDone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @Assert\File(
     *     mimeTypes = {"image/jpeg", "image/png", "image/jpg"},
     *     mimeTypesMessage = "Wrong file type (jpeg,png,jpg)"
     * )
     * @var UploadedFile|null
     */
    private $uploadedPic;

    /**
     * @var bool
     */
    private $isDelete;

    public function __construct()
    {
        $this->roles = array('ROLE_USER');
        $this->products = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->reviewsDone = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->userName,
            $this->password,
        ));
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->userName,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized, array('allowed_classes' => false));
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @param null|string $confirmationToken
     */
    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCreator($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getCreator() === $this) {
                $product->setCreator(null);
            }
        }

        return $this;
    }

    public function getAverageNote(): ?float
    {
        return $this->averageNote;
    }

    public function setAverageNote(?float $averageNote): self
    {
        $this->averageNote = $averageNote;

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setSeller($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getSeller() === $this) {
                $review->setSeller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviewsDone(): Collection
    {
        return $this->reviewsDone;
    }

    public function addReviewsDone(Review $reviewsDone): self
    {
        if (!$this->reviewsDone->contains($reviewsDone)) {
            $this->reviewsDone[] = $reviewsDone;
            $reviewsDone->setCustomer($this);
        }

        return $this;
    }

    public function removeReviewsDone(Review $reviewsDone): self
    {
        if ($this->reviewsDone->contains($reviewsDone)) {
            $this->reviewsDone->removeElement($reviewsDone);
            // set the owning side to null (unless already changed)
            if ($reviewsDone->getCustomer() === $this) {
                $reviewsDone->setCustomer(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedPic(): ?UploadedFile
    {
        return $this->uploadedPic;
    }

    /**
     * @param UploadedFile $uploadedPic
     */
    public function setUploadedPic(UploadedFile $uploadedPic): void
    {
        $this->uploadedPic = $uploadedPic;
    }

    /**
     * @return bool
     */
    public function isDelete (): ?bool
    {
        return $this->isDelete;
    }

    /**
     * @param bool $isDelete
     */
    public function setIsDelete (bool $isDelete): void
    {
        $this->isDelete = $isDelete;
    }


}
