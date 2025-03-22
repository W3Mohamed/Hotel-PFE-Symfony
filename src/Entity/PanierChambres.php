<?php

namespace App\Entity;

use App\Repository\PanierChambresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierChambresRepository::class)]
class PanierChambres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'panierChambres')]
    private ?Panier $panier = null;

    #[ORM\ManyToOne(inversedBy: 'panierChambres')]
    private ?Chambres $chambre = null;

    #[ORM\Column]
    private ?int $nb_nuit = null;

    /**
     * @var Collection<int, PanierService>
     */
    #[ORM\OneToMany(targetEntity: PanierService::class, mappedBy: 'panierChambre')]
    private Collection $panierServices;

    public function __construct()
    {
        $this->panierServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;

        return $this;
    }

    public function getChambre(): ?Chambres
    {
        return $this->chambre;
    }

    public function setChambre(?Chambres $chambre): static
    {
        $this->chambre = $chambre;

        return $this;
    }

    public function getNbNuit(): ?int
    {
        return $this->nb_nuit;
    }

    public function setNbNuit(int $nb_nuit): static
    {
        $this->nb_nuit = $nb_nuit;

        return $this;
    }

    /**
     * @return Collection<int, PanierService>
     */
    public function getPanierServices(): Collection
    {
        return $this->panierServices;
    }

    public function addPanierService(PanierService $panierService): static
    {
        if (!$this->panierServices->contains($panierService)) {
            $this->panierServices->add($panierService);
            $panierService->setPanierChambre($this);
        }

        return $this;
    }

    public function removePanierService(PanierService $panierService): static
    {
        if ($this->panierServices->removeElement($panierService)) {
            // set the owning side to null (unless already changed)
            if ($panierService->getPanierChambre() === $this) {
                $panierService->setPanierChambre(null);
            }
        }

        return $this;
    }
}
