<?php

namespace App\Controller\Admin;

use App\Entity\ForumPost;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ForumPostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ForumPost::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('author', 'Auteur');
        yield TextareaField::new('body', 'Contenu');
        yield IntegerField::new('parentId', 'Parent ID');
        yield BooleanField::new('flag', 'Signale');
    }
}
