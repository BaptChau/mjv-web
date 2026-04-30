<?php

namespace App\Controller\Admin;

use App\Entity\NewsComment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NewsCommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NewsComment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('author', 'Auteur');
        yield TextareaField::new('content', 'Contenu');
        yield AssociationField::new('newsId', 'Article');
        yield DateTimeField::new('createdAt', 'Cree le')->hideOnForm();
    }
}
