<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ProductsRepository::class)
 */
class Products
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Groups("list")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups("list")
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups("list")
     */
    private ?string $brand;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups("list")
     */
    private ?int $quantity;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @Serializer\Groups("list")
     */
    private Collection $soldBy;

    /**
     * Products constructor.
     */
    public function __construct()
    {
        $this->soldBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSoldBy(): Collection
    {
        return $this->soldBy;
    }

    public function addToClient(User $user): void
    {
        if ($this->soldBy->contains($user))
        {
            return;
        }

        $this->soldBy->add($user);
    }

    public function eraseFromClient(User $user): void
    {
        if (!$this->soldBy->contains($user))
        {
            return;
        }

        $this->soldBy->removeElement($user);
    }
}
