<?php
/**
 * Created by PhpStorm.
 * User: aragorn
 * Date: 16/07/18
 * Time: 13:21
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class CommentType extends AbstractType
{
    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', TextareaType::class, ['label' => 'Commentaire']);
    }
}