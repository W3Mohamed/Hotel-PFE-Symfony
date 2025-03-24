<?php

namespace App\Entity;

use App\Repository\ServicesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServicesRepository::class)]
class Services
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $prix = null;

    /**
     * @var Collection<int, PanierService>
     */
    #[ORM\OneToMany(targetEntity: PanierService::class, mappedBy: 'service_id')]
    private Collection $panierServices;

    /**
     * @var Collection<int, ReservationsService>
     */
    #[ORM\OneToMany(targetEntity: ReservationsService::class, mappedBy: 'service_id')]
    private Collection $reservationsServices;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    public function __construct()
    {
        $this->panierServices = new ArrayCollection();
        $this->reservationsServices = new ArrayCollection();
    }

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
            $panierService->setServiceId($this);
        }

        return $this;
    }

    public function removePanierService(PanierService $panierService): static
    {
        if ($this->panierServices->removeElement($panierService)) {
            // set the owning side to null (unless already changed)
            if ($panierService->getServiceId() === $this) {
                $panierService->setServiceId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReservationsService>
     */
    public function getReservationsServices(): Collection
    {
        return $this->reservationsServices;
    }

    public function addReservationsService(ReservationsService $reservationsService): static
    {
        if (!$this->reservationsServices->contains($reservationsService)) {
            $this->reservationsServices->add($reservationsService);
            $reservationsService->setServiceId($this);
        }

        return $this;
    }

    public function removeReservationsService(ReservationsService $reservationsService): static
    {
        if ($this->reservationsServices->removeElement($reservationsService)) {
            // set the owning side to null (unless already changed)
            if ($reservationsService->getServiceId() === $this) {
                $reservationsService->setServiceId(null);
            }
        }

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
}
