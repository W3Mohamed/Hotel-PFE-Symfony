<?php
namespace App\Controller\Admin;

use App\Entity\Faq;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class FaqCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('question'),
            TextEditorField::new('answer'),
            AssociationField::new('category')
                ->setFormTypeOptions([
                    'class' => Category::class,
                    'choice_label' => 'name', // Remplacez 'name' par le champ que vous voulez afficher
                ]),
            IntegerField::new('displayOrder'),
        ];
    }
}