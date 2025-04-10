<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $session_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    /**
     * @var Collection<int, PanierChambres>
     */
    #[ORM\OneToMany(targetEntity: PanierChambres::class, mappedBy: 'panier', cascade:["persist", "remove"])]
    private Collection $panierChambres;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateArrive = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column]
    private ?int $nbAdulte = null;

    #[ORM\Column]
    private ?int $nbEnfant = null;

    #[ORM\Column]
    private ?bool $status = null;

    public function __construct()
    {
        $this->panierChambres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function setSessionId(string $session_id): static
    {
        $this->session_id = $session_id;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    /**
     * @return Collection<int, PanierChambres>
     */
    public function getPanierChambres(): Collection
    {
        return $this->panierChambres;
    }

    public function addPanierChambre(PanierChambres $panierChambre): static
    {
        if (!$this->panierChambres->contains($panierChambre)) {
            $this->panierChambres->add($panierChambre);
            $panierChambre->setPanier($this);
        }

        return $this;
    }

    public function removePanierChambre(PanierChambres $panierChambre): static
    {
        if ($this->panierChambres->removeElement($panierChambre)) {
            // set the owning side to null (unless already changed)
            if ($panierChambre->getPanier() === $this) {
                $panierChambre->setPanier(null);
            }
        }

        return $this;
    }

    public function getDateArrive(): ?\DateTimeInterface
    {
        return $this->dateArrive;
    }

    public function setDateArrive(\DateTimeInterface $dateArrive): static
    {
        $this->dateArrive = $dateArrive;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getNbAdulte(): ?int
    {
        return $this->nbAdulte;
    }

    public function setNbAdulte(int $nbAdulte): static
    {
        $this->nbAdulte = $nbAdulte;

        return $this;
    }

    public function getNbEnfant(): ?int
    {
        return $this->nbEnfant;
    }

    public function setNbEnfant(int $nbEnfant): static
    {
        $this->nbEnfant = $nbEnfant;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }
}
