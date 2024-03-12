<?php

namespace App\Form;

use App\Entity\Asset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Asset::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isin', TextType::class, ['label' => 'ISIN'])
            ->add('name', TextType::class)
            ->add('symbol', TextType::class, ['label' => 'Asset symbol'])
            ->add('type', ChoiceType::class, ['choices' => [
                'Bond' => Asset::TYPE_BOND,
                'Commodity' => Asset::TYPE_COMMODITY,
                'Crypto' => Asset::TYPE_CRYPTO,
                'Fund' => Asset::TYPE_FUND,
                'Foreign Exchange' => Asset::TYPE_FX,
                'Index' => Asset::TYPE_INDEX,
                'Stock' => Asset::TYPE_STOCK,
                ]])
            ->add('currency', CurrencyType::class)
            ->add('country', CountryType::class, ['required' => false])
            ->add('url', UrlType::class, ['label' => 'Information Link', 'required' => false])
            ->add('irurl', UrlType::class, ['label' => 'Investor Relations Link', 'required' => false])
            ->add('newsurl', UrlType::class, ['label' => 'News Link', 'required' => false])
            ->add('pricedatasource', TextType::class, ['label' => 'Price datasource expression (e.g. AV/AAPL, OV/86627)', 'required' => false])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
