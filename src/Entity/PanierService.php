<?php

namespace App\Entity;

use App\Repository\PanierServiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierServiceRepository::class)]
class PanierService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'panierServices')]
    private ?PanierChambres $panierChambre = null;

    #[ORM\ManyToOne(inversedBy: 'panierServices')]
    private ?Services $service_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanierChambre(): ?PanierChambres
    {
        return $this->panierChambre;
    }

    public function setPanierChambre(?PanierChambres $panierChambre): static
    {
        $this->panierChambre = $panierChambre;

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
