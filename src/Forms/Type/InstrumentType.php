<?php

namespace App\Forms\Type;

use App\Entity\Asset;
use App\Entity\Currency;
use App\Entity\Instrument;
use App\Forms\Type\CurrencyType;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InstrumentType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isin', TextType::class, ['label' => 'ISIN', 'required' => false])
            ->add('name', TextType::class)
            ->add('instrumentclass', ChoiceType::class, ['label' => 'Class', 'choices' => [
                'Direct' => [
                    'Underlying' => Instrument::CLASS_UNDERLYING,
                    'CFD' => Instrument::CLASS_CFD,
                ],
                'Investment' => [
                    'Capital protection' => Instrument::CLASS_CAPITAL_PROTECTION,
                    'Yield enhancement' => Instrument::CLASS_YIELD_ENHANCEMENT,
                    'Participation' => Instrument::CLASS_PARTICIPATION,
                ],
                'Leverage' => [
                    'Knock-Out' => Instrument::CLASS_KNOCKOUT,
                    'Warrant' => Instrument::CLASS_WARRANT,
                    'Constant leverage' => Instrument::CLASS_CONST_LEVERAGE,
                ],
                ]])
            ->add('underlying', EntityType::class, [
                'class' => Asset::class,
                'query_builder' => function (AssetRepository $ar) {
                    return $ar->createQueryBuilder('a')
                        ->orderBy('a.Name', 'ASC');
                },
                'group_by' => function($val, $key, $index) { return $val->getTypeName(); },
                ])
            ->add('currency', CurrencyType::class)
            ->add('issuer', TextType::class, ['required' => false])
            ->add('emissiondate', DateType::class, ['required' => false, 'label'=>'Emission date', 'widget' => 'single_text'])
            ->add('terminationdate', DateType::class, ['required' => false, 'label'=>'Termination date', 'widget' => 'single_text'])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
        ;
    }
}
