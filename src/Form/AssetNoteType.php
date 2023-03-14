<?php

namespace App\Form;

use App\Entity\AssetNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetNoteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AssetNote::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, ['choices' => [
                'Note' => AssetNote::TYPE_NOTE,
                'News' => AssetNote::TYPE_NEWS,
                'Event' => AssetNote::TYPE_EVENT,
                ]])
            ->add('date', DateType::class, ['required' => true, 'widget' => 'single_text'])
            ->add('title', TextType::class, ['required' => true])
            ->add('text', TextareaType::class, ['required' => false])
            ->add('url', UrlType::class, ['label' => 'Information Link', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
