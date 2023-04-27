<?php

namespace App\Entity;

use App\Repository\InvitationTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass=InvitationTokenRepository::class)
 */
class InvitationToken implements JsonSerializable
{
    
    public function jsonSerialize()
    {
        $array = [
            'id' => $this->getId(),
            'invitationToken' => $this->getInvitationToken(),
            'email' => $this->getEmail(),
            'active' => $this->getActive(),
        ];

        return $array;
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $invitation_token;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean", options={"default":true})
     */
    private $active = true;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="invitationTokens")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvitationToken(): ?string
    {
        return $this->invitation_token;
    }

    public function setInvitationToken(string $invitation_token): self
    {
        $this->invitation_token = $invitation_token;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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
