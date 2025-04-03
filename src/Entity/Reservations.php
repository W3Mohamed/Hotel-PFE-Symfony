<?php

namespace App\Entity;

use App\Repository\ReservationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationsRepository::class)]
class Reservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?User $user_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_arrive = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_depart = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column]
    private ?float $prix_total = null;

    /**
     * @var Collection<int, ReservationChambre>
     */
    #[ORM\OneToMany(targetEntity: ReservationChambre::class, mappedBy: 'reservation_id')]
    private Collection $reservationChambres;

    public function __construct()
    {
        $this->reservationChambres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }

    public function setDateDepart(\DateTimeInterface $date_depart): static
    {
        $this->date_depart = $date_depart;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPrixTotal(): ?float
    {
        return $this->prix_total;
    }

    public function setPrixTotal(float $prix_total): static
    {
        $this->prix_total = $prix_total;

        return $this;
    }

    /**
     * @return Collection<int, ReservationChambre>
     */
    public function getReservationChambres(): Collection
    {
        return $this->reservationChambres;
    }

    public function addReservationChambre(ReservationChambre $reservationChambre): static
    {
        if (!$this->reservationChambres->contains($reservationChambre)) {
            $this->reservationChambres->add($reservationChambre);
            $reservationChambre->setReservationId($this);
        }

        return $this;
    }

    public function removeReservationChambre(ReservationChambre $reservationChambre): static
    {
        if ($this->reservationChambres->removeElement($reservationChambre)) {
            // set the owning side to null (unless already changed)
            if ($reservationChambre->getReservationId() === $this) {
                $reservationChambre->setReservationId(null);
            }
        }

        return $this;
    }

    public function getDateArrive(): ?\DateTimeInterface
    {
        return $this->date_arrive;
    }

    public function setDateArrive(\DateTimeInterface $date_arrive): static
    {
        $this->date_arrive = $date_arrive;

        return $this;
    }
}
