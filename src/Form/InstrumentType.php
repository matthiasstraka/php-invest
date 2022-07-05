<?php

namespace App\Form;

use App\Entity\Asset;
use App\Entity\Instrument;
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

class InstrumentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Instrument::class,
            'underlying_editable' => true,
        ]);

        $resolver->setAllowedTypes('underlying_editable', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('isin', TextType::class, ['label' => 'ISIN', 'required' => false])
            // for map of products see https://eusipa.org/wp-content/uploads/European_map_20200213_web.pdf
            ->add('eusipa', ChoiceType::class, ['label' => 'EUSIPA', 'choices' => [
                'Direct' => [
                    'Underlying' => Instrument::EUSIPA_UNDERLYING,
                    'CFD' => Instrument::EUSIPA_CFD,
                ],
                'Investment' => [
                    'Discount Certificate' => Instrument::EUSIPA_DISCOUNT_CERTIFICATE,
                    'Capped Bonus Certificate' => Instrument::EUSIPA_CAPPED_BONUS_CERTIFICATE,
                    'Bonus Certificate' => Instrument::EUSIPA_BONUS_CERTIFICATE,
                    'Tracker Certificate' => Instrument::EUSIPA_TRACKER,
                ],
                'Leverage' => [
                    'Knock-Out' => Instrument::EUSIPA_KNOCKOUT,
                    'Mini Future' => Instrument::EUSIPA_MINIFUTURE,
                    'Warrant' => Instrument::EUSIPA_WARRANT,
                    'Spread Warrant' => Instrument::EUSIPA_SPREAD_WARRANT,
                    'Constant leverage' => Instrument::EUSIPA_CONSTANT_LEVERAGE,
                ],
                ]]);
        if ($options['underlying_editable'])
        {
            $builder->add('underlying', EntityType::class, [
                'class' => Asset::class,
                'choice_label' => function ($asset) {
                    return sprintf("%s [%s] (%s)", $asset->getName(), $asset->getSymbol(), $asset->getIsin());
                },
                'query_builder' => function (AssetRepository $ar) {
                    return $ar->createQueryBuilder('a')
                        ->orderBy('a.name', 'ASC');
                },
                'group_by' => function($val, $key, $index) { return $val->getTypeName(); },
            ]);
        } else {
            $builder->add('underlying', TextType::class, ['disabled' =>'true']);
        }
        $builder->add('status', ChoiceType::class, ['label' => 'Status', 'choices' => [
                'Active' => Instrument::STATUS_ACTIVE,
                'Expired' => Instrument::STATUS_EXPIRED,
                'Knocked out' => Instrument::STATUS_KNOCKED_OUT,
                'Barrier breached' => Instrument::STATUS_BARRIER_BREACHED,
                'Hidden' => Instrument::STATUS_HIDDEN,
                ]])
            ->add('direction', ChoiceType::class, ['label' => 'Direction', 'choices' => [
                    'Long' => Instrument::DIRECTION_LONG,
                    'Short' => Instrument::DIRECTION_SHORT,
                    ]])
            ->add('currency', CurrencyType::class)
            ->add('issuer', TextType::class, ['required' => false])
            ->add('emissiondate', DateType::class, ['required' => false, 'label'=>'Emission date', 'widget' => 'single_text'])
            ->add('terminationdate', DateType::class, ['required' => false, 'label'=>'Termination date', 'widget' => 'single_text'])
            ->add('url', UrlType::class, ['required' => false, 'label' => 'Instrument website'])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
