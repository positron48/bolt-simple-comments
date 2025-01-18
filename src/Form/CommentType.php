<?php

namespace Positron48\CommentExtension\Form;

use Positron48\CommentExtension\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('authorName', null, [
                'label' => 'form.author_name'
            ])
            ->add('authorEmail', null, [
                'label' => 'form.author_email'
            ])
            ->add('message', TextareaType::class, [
                'label' => 'form.message'
            ])
            ->add('field', HiddenType::class, ['mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'translation_domain' => 'messages'
        ]);
    }
}