<?php

namespace Positron48\CommentExtension\Form;

use Positron48\CommentExtension\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('authorName')
            ->add('authorEmail')
            ->add('message')
//            ->add('recaptcha', HiddenType::class, ['mapped' => false])
            ->add('field', HiddenType::class, ['mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}