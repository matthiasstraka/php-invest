<?php

namespace App\Form\Type;

use App\Entity\Asset;
use App\Entity\Country;
use App\Entity\Currency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AssetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isin', TextType::class, ['label' => 'ISIN'])
            ->add('name', TextType::class)
            ->add('symbol', TextType::class, ['label' => 'Symbol (e.g. Ticker symbol)'])
            ->add('type', ChoiceType::class, ['choices' => [
                'Bond' => Asset::TYPE_BOND,
                'Commodity' => Asset::TYPE_COMMODITY,
                'Fonds' => Asset::TYPE_FONDS,
                'Foreign Exchange' => Asset::TYPE_FX,
                'Index' => Asset::TYPE_INDEX,
                'Stock' => Asset::TYPE_STOCK,
                ]])
            ->add('currency', EntityType::class, ['class' => Currency::class])
            ->add('country', EntityType::class, ['class' => Country::class, 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
        ;
    }
}