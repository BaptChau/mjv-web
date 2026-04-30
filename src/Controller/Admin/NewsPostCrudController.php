<?php

namespace App\Controller\Admin;

use App\Entity\NewsPost;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NewsPostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NewsPost::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('author', 'Auteur');
        yield TextareaField::new('content', 'Contenu');
        yield TextField::new('imgPath', 'Image');
        yield DateTimeField::new('createdAt', 'Cree le')->hideOnForm();
        yield DateTimeField::new('updateAt', 'Mis a jour le')->hideOnForm();
    }
}
