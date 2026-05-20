<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    public const STATUT_PANIER     = 'panier';
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_VALIDEE    = 'validee';
    public const STATUT_EXPEDIEE   = 'expediee';
    public const STATUT_LIVREE     = 'livree';
    public const STATUT_ANNULEE    = 'annulee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_PANIER;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $total = '0.00';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\OneToOne(mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private ?Facture $facture = null;

    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private Collection $lignes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->numero    = 'CMD-' . strtoupper(uniqid());
        $this->lignes    = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(Facture $facture): static
    {
        // set the owning side of the relation if necessary
        if ($facture->getCommande() !== $this) {
            $facture->setCommande($this);
        }

        $this->facture = $facture;

        return $this;
    }

    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneCommande $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setCommande($this);
        }

        return $this;
    }

    public function removeLigne(LigneCommande $ligne): static
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getCommande() === $this) {
                $ligne->setCommande(null);
            }
        }

        return $this;
    }
}
