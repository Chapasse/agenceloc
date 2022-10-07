<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Membre;
use App\Entity\Vehicule;
use App\Repository\MembreRepository;
use App\Repository\VehiculeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CommandePostAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // $repoVehicule = new VehiculeRepository();
        // $repoMembre = new MembreRepository();       
        
        $builder
            ->add('date_heure_depart', DateTimeType::class, [
                'years' => range(2022,2100)
            ])
            ->add('date_heure_fin', DateTimeType::class,[
                'years' => range(2022,2100)
            ])
            ->add('prix_total')
            // ->add('date_enregistrement')
            ->add('id_membre', EntityType::class, [
                'class' => Membre::class,
                'choice_label' => 'pseudo'
            ])
            ->add('id_vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => 'title'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
