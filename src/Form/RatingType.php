<?php
/**
 * Created by PhpStorm.
 * User: aragorn
 * Date: 13/07/18
 * Time: 10:36
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class RatingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', HiddenType::class, [
                'attr' => ['class' => 'rating']
            ]);
    }
}
