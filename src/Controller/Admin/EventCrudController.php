<?php
// src/Controller/Admin/EventCrudController.php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),
            TextareaField::new('description', 'Description'),
            DateTimeField::new('date', 'Date et heure'),
            TextField::new('location', 'Lieu'),
            ImageField::new('image', 'Image')
                ->setUploadDir('public/img/events')
                ->setBasePath('img/events')
                ->setRequired(false),
            BooleanField::new('isFeatured', 'Mise en avant'),
        ];
    }
}