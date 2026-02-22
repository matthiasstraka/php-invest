<?php

namespace App\Form;

use App\Entity\Asset;
use App\Entity\AssetNote;
use App\Repository\AssetRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            'asset_editable' => true,
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
            ->add('date', DateType::class, ['required' => true, 'widget' => 'single_text']);
        if ($options['asset_editable'])
        {
            $builder->add('asset', EntityType::class, [
                'class' => Asset::class,
                'choice_label' => function ($asset) {
                    return sprintf("%s [%s] (%s)", $asset->getName(), $asset->getSymbol(), $asset->getIsin());
                },
                'query_builder' => function (AssetRepository $ar) {
                    return $ar->createQueryBuilder('a')
                        ->orderBy('a.name', 'ASC');
                },
                'group_by' => function($val, $key, $index) { return $val->getTypeName(); },
                'required' => false,
            ]);
        } else {
            $builder->add('asset', TextType::class, ['disabled' => true]);
        }
        $builder
            ->add('title', TextType::class, ['required' => true, 'trim' => true])
            ->add('text', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
                ])
            ->add('url', UrlType::class, ['label' => 'Information Link', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
