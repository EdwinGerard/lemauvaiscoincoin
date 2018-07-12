<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Department;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', SearchType::class)
            ->add('filter', ChoiceType::class, [
                'choices' => [
                    'Filtres...' => 'empty',
                    'Catégorie' => 'category',
                    'Département' => 'department'
                ]
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
            ])
            ->add('departements', EntityType::class, [
                'class' => Department::class
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
