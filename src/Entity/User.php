<?php

namespace App\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    
    public function jsonSerialize()
    {
        $array = [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
        ];

        return $array;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=TipoMovimento::class, mappedBy="usuario")
     */
    private $tipoMovimentos;

    /**
     * @ORM\OneToMany(targetEntity=Conta::class, mappedBy="usuario")
     */
    private $contas;

    /**
     * @ORM\OneToMany(targetEntity=ClasseMovimento::class, mappedBy="usuario")
     */
    private $classeMovimentos;

    /**
     * @ORM\OneToMany(targetEntity=Movimento::class, mappedBy="usuario")
     */
    private $movimentos;

    public function __construct()
    {
        $this->tipoMovimentos = new ArrayCollection();
        $this->contas = new ArrayCollection();
        $this->classeMovimentos = new ArrayCollection();
        $this->movimentos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
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
     * @return Collection<int, TipoMovimento>
     */
    public function getTipoMovimentos(): Collection
    {
        return $this->tipoMovimentos;
    }

    public function addTipoMovimento(TipoMovimento $tipoMovimento): self
    {
        if (!$this->tipoMovimentos->contains($tipoMovimento)) {
            $this->tipoMovimentos[] = $tipoMovimento;
            $tipoMovimento->setUsuario($this);
        }

        return $this;
    }

    public function removeTipoMovimento(TipoMovimento $tipoMovimento): self
    {
        if ($this->tipoMovimentos->removeElement($tipoMovimento)) {
            // set the owning side to null (unless already changed)
            if ($tipoMovimento->getUsuario() === $this) {
                $tipoMovimento->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Conta>
     */
    public function getContas(): Collection
    {
        return $this->contas;
    }

    public function addConta(Conta $conta): self
    {
        if (!$this->contas->contains($conta)) {
            $this->contas[] = $conta;
            $conta->setUsuario($this);
        }

        return $this;
    }

    public function removeConta(Conta $conta): self
    {
        if ($this->contas->removeElement($conta)) {
            // set the owning side to null (unless already changed)
            if ($conta->getUsuario() === $this) {
                $conta->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClasseMovimento>
     */
    public function getClasseMovimentos(): Collection
    {
        return $this->classeMovimentos;
    }

    public function addClasseMovimento(ClasseMovimento $classeMovimento): self
    {
        if (!$this->classeMovimentos->contains($classeMovimento)) {
            $this->classeMovimentos[] = $classeMovimento;
            $classeMovimento->setUsuario($this);
        }

        return $this;
    }

    public function removeClasseMovimento(ClasseMovimento $classeMovimento): self
    {
        if ($this->classeMovimentos->removeElement($classeMovimento)) {
            // set the owning side to null (unless already changed)
            if ($classeMovimento->getUsuario() === $this) {
                $classeMovimento->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Movimento>
     */
    public function getMovimentos(): Collection
    {
        return $this->movimentos;
    }

    public function addMovimento(Movimento $movimento): self
    {
        if (!$this->movimentos->contains($movimento)) {
            $this->movimentos[] = $movimento;
            $movimento->setUsuario($this);
        }

        return $this;
    }

    public function removeMovimento(Movimento $movimento): self
    {
        if ($this->movimentos->removeElement($movimento)) {
            // set the owning side to null (unless already changed)
            if ($movimento->getUsuario() === $this) {
                $movimento->setUsuario(null);
            }
        }

        return $this;
    }
}
