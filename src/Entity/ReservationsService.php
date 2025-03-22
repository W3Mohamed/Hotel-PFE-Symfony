<?php

namespace App\Entity;

use App\Repository\ReservationsServiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationsServiceRepository::class)]
class ReservationsService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservationsServices')]
    private ?ReservationChambre $reservation_chambre = null;

    #[ORM\ManyToOne(inversedBy: 'reservationsServices')]
    private ?Services $service_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservationChambre(): ?ReservationChambre
    {
        return $this->reservation_chambre;
    }

    public function setReservationChambre(?ReservationChambre $reservation_chambre): static
    {
        $this->reservation_chambre = $reservation_chambre;

        return $this;
    }

    public function getServiceId(): ?Services
    {
        return $this->service_id;
    }

    public function setServiceId(?Services $service_id): static
    {
        $this->service_id = $service_id;

        return $this;
    }
}
