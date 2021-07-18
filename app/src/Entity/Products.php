<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductsRepository::class)
 * @Serializer\XmlRoot("products")
 *
 * @Hateoas\Relation(
 *     "self",
 *      href =  @Hateoas\Route(
 *          "api_product_item",
 *           parameters={"id" = "expr(object.getId())"},
 *          absolute= true
 *     ),
 *     embedded = "expr(object.getSoldBy())",
 *     exclusion= @Hateoas\Exclusion(groups={"list","details"})
 * )
 */
class Products
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Groups("list", "Default")
     * @Serializer\XmlAttribute
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Unique
     * @Serializer\Groups("list", "Default")
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups("list", "Default")
     */
    private ?string $brand;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups("list", "Default")
     */
    private ?int $quantity;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @Serializer\Groups("details")
     * @Serializer\Exclude
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
