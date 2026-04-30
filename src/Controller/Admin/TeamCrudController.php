<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class TeamCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Team::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('label', 'Nom');
        yield BooleanField::new('gender', 'Masculin');
        yield TextField::new('coach', 'Entraineur');
        yield TextField::new('secondCoach', 'Second entraineur');
        yield ImageField::new('photoPath', 'Photo')
            ->setBasePath('uploads/teams')
            ->setUploadDir('public/uploads/teams')
            ->setRequired(false);
        yield IntegerField::new('category', 'Categorie');
        yield UrlField::new('championshipUrl', 'Lien championnat')->hideOnIndex();
    }
}
