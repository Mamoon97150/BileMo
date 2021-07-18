<?php

namespace App\Entity;

use App\Repository\SubUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SubUserRepository::class)
 * @Serializer\XmlRoot("subuser")
 *
 * Hateoas\RelationProvider("expr(service(user.rel_provider).getExtraRelations())")
 * @Hateoas\Relation(
 *     "self",
 *      href =  @Hateoas\Route(
 *          "api_sub_item",
 *           parameters={"id" = "expr(object.getId())"},
 *          absolute= true
 *     ),
 *     embedded = @Hateoas\Embedded(
 *          "expr(object.getUser())",
 *           exclusion= @Hateoas\Exclusion(groups={"sub_details"}, maxDepth=1),
 *      ),
 *     exclusion= @Hateoas\Exclusion(groups={"sub_details"}, maxDepth=1)
 * )
 *
 * @Hateoas\Relation(
 *     "update",
 *      href =  @Hateoas\Route(
 *          "api_sub_update",
 *           parameters={"id" = "expr(object.getId())"},
 *          absolute= true
 *     ),
 *
 *     exclusion= @Hateoas\Exclusion(groups={"sub_details"}, maxDepth=1)
 * )
 *
 * @Hateoas\Relation(
 *     "delete",
 *      href =  @Hateoas\Route(
 *          "api_sub_delete",
 *           parameters={"id" = "expr(object.getId())"},
 *          absolute= true
 *     ),
 *     exclusion= @Hateoas\Exclusion(groups={"sub_details"}, maxDepth=1)
 * )
 */
#[UniqueEntity('username')]
class SubUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Groups("details", "sub_list", "sub_details", "Default")
     * @Serializer\XmlAttribute
     * @Type("int")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(min=5)
     * @Serializer\Groups("details", "sub_list", "sub_details", "Default")
     * @Type("string")
     */
    private ?string $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Email()
     * @Serializer\Groups("details", "sub_list", "sub_details", "Default")
     * @Type("string")
     */
    private ?string $email;

    /**
     * @Serializer\Groups("sub_list", "Default")
     * @Serializer\Exclude
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="subUsers")
     * @ORM\JoinColumn(nullable=false)
     * @Type("App\Entity\User")
     */
    private ?User $user;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
