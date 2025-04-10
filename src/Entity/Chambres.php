<?php

namespace App\Entity;
use App\Enum\StatusEnum;
use App\Repository\ChambresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChambresRepository::class)]
class Chambres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $petit_desc = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column(type: 'string', enumType: StatusEnum::class, length: 10)]
    private StatusEnum  $status;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    /**
     * @var Collection<int, PanierChambres>
     */
    #[ORM\OneToMany(targetEntity: PanierChambres::class, mappedBy: 'chambre')]
    private Collection $panierChambres;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getPetitDesc(): ?string
    {
        return $this->petit_desc;
    }

    public function setPetitDesc(string $petit_desc): static
    {
        $this->petit_desc = $petit_desc;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getStatus(): ?StatusEnum
    {
        return $this->status;
    }

    public function setStatus(StatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

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
            $panierChambre->setChambre($this);
        }

        return $this;
    }

    public function removePanierChambre(PanierChambres $panierChambre): static
    {
        if ($this->panierChambres->removeElement($panierChambre)) {
            // set the owning side to null (unless already changed)
            if ($panierChambre->getChambre() === $this) {
                $panierChambre->setChambre(null);
            }
        }

        return $this;
    }
}
