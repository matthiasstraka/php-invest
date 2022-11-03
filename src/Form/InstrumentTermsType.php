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
            'available_terms' => [],
        ]);

        $resolver->setAllowedTypes('currency', 'string');
        $resolver->setAllowedTypes('available_terms', 'array');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currency = $options['currency'];
        $available_terms = $options['available_terms'];
        $builder
            ->add('date', DateType::class, ['required' => true, 'widget' => 'single_text'])
            ->add('ratio', NumberType::class, ['required' => false, 'html5' => false, 'scale' => 4, 'help' => 'Ratio (e.g. 10% is 0.1)'])
            ->add('cap', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !in_array('cap', $available_terms)]])
            ->add('strike', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !in_array('strike', $available_terms)]])
            ->add('bonus_level', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !in_array('bonus_level', $available_terms)]])
            ->add('reverse_level', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !in_array('reverse_level', $available_terms)]])
            ->add('barrier', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !in_array('barrier', $available_terms)]])
            ->add('interest_rate', NumberType::class, [
                'required' => false, 'html5' => false, 'scale' => 4, 'help' => 'Interest rate (e.g. 3% is 0.03)',
                'attr' => ['readonly' => !in_array('interest_rate', $available_terms)]])
            ->add('margin', NumberType::class, [
                  'required' => false, 'html5' => false, 'scale' => 4, 'help' => 'Margin rate (e.g. 20% is 0.2)',
                  'attr' => ['readonly' => !in_array('margin', $available_terms)]])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
