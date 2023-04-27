<?php

namespace App\Entity;

use JsonSerializable;
use App\Enum\DiaSemanaEnum;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MovimentoRepository;

/**
 * @ORM\Entity(repositoryClass=MovimentoRepository::class)
 */
class Movimento implements JsonSerializable
{
    public function jsonSerialize()
    {
        $array = [
            'id' => $this->getId(),
            'descricao' => $this->getDescricao(),
            'valor' => $this->getValor(),
            'dataMovimento' => $this->getDataMovimento(),
            'dataMovimentoReadable' => $this->getDataMovimento()->format("d/m/Y"),
            'dataMovimentoDiaSemana' => DiaSemanaEnum::ABREVIADO[$this->getDataMovimento()->format("N")],
            'conta' => $this->getConta(),
            'classeMovimento' => $this->getClasse(),
            'tipoMovimento' => $this->getTipomovimento(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'deletedAt' => $this->getDeletedAt(),
        ];

        return $array;
    }

    public function isValorDirty() {
        return $this->isValorDirty;
    }

    protected $isValorDirty;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descricao;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $valor;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dataMovimento;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $deleted_at;

    /**
     * @ORM\ManyToOne(targetEntity=ClasseMovimento::class, inversedBy="movimentos")
     */
    private $classe;

    /**
     * @ORM\ManyToOne(targetEntity=TipoMovimento::class, inversedBy="movimentos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipomovimento;

    /**
     * @ORM\ManyToOne(targetEntity=Conta::class, inversedBy="movimentos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conta;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="movimentos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        if($this->valor != $valor) {
            $this->isValorDirty = true;
        }
        $this->valor = $valor;

        return $this;
    }

    public function getDataMovimento(): ?\DateTimeInterface
    {
        return $this->dataMovimento;
    }

    public function setDataMovimento(\DateTimeInterface $dataMovimento): self
    {
        $this->dataMovimento = $dataMovimento;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): self
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    public function getClasse(): ?ClasseMovimento
    {
        return $this->classe;
    }

    public function setClasse(?ClasseMovimento $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getTipomovimento(): ?TipoMovimento
    {
        return $this->tipomovimento;
    }

    public function setTipomovimento(?TipoMovimento $tipomovimento): self
    {
        $this->tipomovimento = $tipomovimento;

        return $this;
    }

    public function getConta(): ?Conta
    {
        return $this->conta;
    }

    public function setConta(?Conta $conta): self
    {
        $this->conta = $conta;

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }
}
