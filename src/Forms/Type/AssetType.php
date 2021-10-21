<?php

namespace App\Forms\Type;

use App\Entity\Asset;
use App\Entity\Country;
use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

class AssetType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $curr_choices = [];
        foreach ($this->entityManager->getRepository(Currency::class)->findAll() as $curr)
        {
            $curr_choices[Currencies::getName($curr->getCode())] = $curr->getCode();
        }

        $country_choices = [];
        foreach ($this->entityManager->getRepository(Country::class)->findAll() as $curr)
        {
            $country_choices[Countries::getName($curr->getCode())] = $curr->getCode();
        }

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
            ->add('currency', ChoiceType::class, ['choices' => $curr_choices])
            ->add('country', ChoiceType::class, ['choices' => $country_choices, 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
        ;
    }
}