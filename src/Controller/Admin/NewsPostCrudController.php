<?php

namespace App\Controller\Admin;

use App\Entity\NewsPost;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
        yield TextField::new('author', 'Auteur')->hideOnForm();
        yield TextEditorField::new('content', 'Contenu');
        yield ImageField::new('imgPath', 'Image')
            ->setBasePath('uploads/news')
            ->setUploadDir('public/uploads/news')
            ->setRequired(false);
        yield DateTimeField::new('createdAt', 'Cree le')->hideOnForm();
        yield DateTimeField::new('updateAt', 'Mis a jour le')->hideOnForm();
    }

    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setAuthor($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->setAuthor($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function setAuthor(mixed $entity): void
    {
        if (!$entity instanceof NewsPost) {
            return;
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if ($user instanceof User && $user->getName()) {
            $entity->setAuthor($user->getName());
        }
    }
}
