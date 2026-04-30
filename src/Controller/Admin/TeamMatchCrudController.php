<?php

namespace App\Controller\Admin;

use App\Entity\TeamMatch;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TeamMatchCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TeamMatch::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['matchDate' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('team', 'Equipe');
        yield TextField::new('matchDay', 'Journee');
        yield DateTimeField::new('matchDate', 'Date');
        yield TextField::new('homeTeam', 'Domicile');
        yield TextField::new('awayTeam', 'Exterieur');
        yield IntegerField::new('homeScore', 'Score dom.');
        yield IntegerField::new('awayScore', 'Score ext.');
        yield TextField::new('status', 'Statut');
        yield TextField::new('venue', 'Lieu');
    }
}
