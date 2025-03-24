<?php

namespace App\Controller\Admin;

use App\Entity\Chambres;
use App\Enum\StatusEnum;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class ChambresCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Chambres::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), // Cache l'ID dans le formulaire
            TextField::new('libelle', 'Nom de la chambre'),
            TextField::new('petit_desc', 'Description courte'),
            TextareaField::new('description', 'Description complète'),
            NumberField::new('prix', 'Prix par nuit'),
            NumberField::new('capacite', 'Capacité (nb personnes)'),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Disponible' => StatusEnum::DISPONIBLE,
                    'Occupé' => StatusEnum::OCCUPE,
                ]),
            ImageField::new('image', 'Image de la chambre')
                ->setUploadDir('public/img/chambres')
                ->setBasePath('/img/chambres'),
        ];
    }
    
}
