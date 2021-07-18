<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @Serializer\XmlRoot("user")
 *
 * @Hateoas\Relation(
 *     "self",
 *      href =  @Hateoas\Route(
 *          "api_user_item",
 *           parameters={"id" = "expr(object.getId())"},
 *          absolute= true
 *     ),
 *     embedded = @Hateoas\Embedded(
 *          "expr(object.getSubUsers())",
 *           exclusion= @Hateoas\Exclusion(groups={"sub_list"}),
 *      ),
 *     exclusion= @Hateoas\Exclusion(groups={"sub_list", "sub_details"})
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Groups("details", "sub_list", "sub_details", "Default")
     * @Serializer\XmlAttribute
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     * @Serializer\Groups("details", "sub_list", "sub_details", "Default")
     */
    private ?string $name;

    /**
     * @ORM\Column(type="json")
     * @Serializer\Exclude
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @Assert\NotBlank
     * @Assert\Length(min=5)
     * @ORM\Column(type="string")
     * @Serializer\Exclude
     */
    private string $password;

    /**
     * @ORM\ManyToMany(targetEntity=SubUser::class, mappedBy="users")
     * @Serializer\Groups("sub_list", "Default")
     * @Serializer\Exclude
     */
    private Collection $subUsers;

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->name;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->name;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection
     */
    public function getSubUsers(): Collection
    {
        return $this->subUsers;
    }

    public function addSubUser(SubUser $subUser): self
    {
        if (!$this->subUsers->contains($subUser)) {
            $this->subUsers[] = $subUser;
            $subUser->addUser($this);
        }

        return $this;
    }

    public function removeSubUser(SubUser $subUser): self
    {
        if ($this->subUsers->removeElement($subUser)) {
            $subUser->removeUser($this);
        }

        return $this;
    }
}
