<?php

namespace App\Form;

use App\Entity\InstrumentTerms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstrumentTermsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InstrumentTerms::class,
            'currency' => '',
        ]);

        $resolver->setAllowedTypes('currency', 'string');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currency = $options['currency'];
        $builder
            ->add('date', DateType::class, ['required' => true, 'widget' => 'single_text'])
            ->add('cap', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('strike', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('bonus_level', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('reverse_level', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('barrier', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('financing_costs', NumberType::class, ['required' => false, 'html5' => false, 'scale' => 4])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
