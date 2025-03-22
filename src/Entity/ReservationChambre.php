<?php

namespace App\Entity;

use App\Repository\ReservationChambreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationChambreRepository::class)]
class ReservationChambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservationChambres')]
    private ?Reservations $reservation_id = null;

    #[ORM\ManyToOne(inversedBy: 'nb_nuit')]
    private ?Chambres $chambre_id = null;

    #[ORM\Column]
    private ?int $nb_nuit = null;

    /**
     * @var Collection<int, ReservationsService>
     */
    #[ORM\OneToMany(targetEntity: ReservationsService::class, mappedBy: 'reservation_chambre')]
    private Collection $reservationsServices;

    public function __construct()
    {
        $this->reservationsServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservationId(): ?Reservations
    {
        return $this->reservation_id;
    }

    public function setReservationId(?Reservations $reservation_id): static
    {
        $this->reservation_id = $reservation_id;

        return $this;
    }

    public function getChambreId(): ?Chambres
    {
        return $this->chambre_id;
    }

    public function setChambreId(?Chambres $chambre_id): static
    {
        $this->chambre_id = $chambre_id;

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
            $reservationsService->setReservationChambre($this);
        }

        return $this;
    }

    public function removeReservationsService(ReservationsService $reservationsService): static
    {
        if ($this->reservationsServices->removeElement($reservationsService)) {
            // set the owning side to null (unless already changed)
            if ($reservationsService->getReservationChambre() === $this) {
                $reservationsService->setReservationChambre(null);
            }
        }

        return $this;
    }
}
