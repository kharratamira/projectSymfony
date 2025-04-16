<?php

namespace App\Form;

use App\Entity\Technicien;
use App\Entity\AffecterDemande;
use Doctrine\DBAL\Types\DateTimeType;
use App\Repository\TechnicienRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffectationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('technicien', EntityType::class, [
            'class' => Technicien::class,
            'query_builder' => function (TechnicienRepository $repo) use ($options) {
                return $repo->createQueryBuilder('t')
                    ->where('t.disponible = true');
            },
            'choice_label' => 'nomComplet'
        ])
        ->add('datePrevu', DateTimeType::class, [
            'widget' => 'single_text',
            'html5' => true
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'data_class' => AffecterDemande::class,
            'demande_id' => null
        ]);
    }
}
