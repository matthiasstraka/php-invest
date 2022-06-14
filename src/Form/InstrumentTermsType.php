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
        $terms = $options['data'];
        $instrument = $terms->getInstrument();
        $builder
            ->add('date', DateType::class, ['required' => true, 'widget' => 'single_text'])
            ->add('ratio', NumberType::class, ['required' => false, 'html5' => false, 'scale' => 4, 'help' => 'Ratio (e.g. 10% is 0.1)'])
            ->add('cap', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !$instrument->hasCap()]])
            ->add('strike', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !$instrument->hasStrike()]])
            ->add('bonus_level', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !$instrument->hasBonusLevel()]])
            ->add('reverse_level', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !$instrument->hasReverseLevel()]])
            ->add('barrier', MoneyType::class, [
                'required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4,
                'attr' => ['readonly' => !$instrument->hasBarrier()]])
            ->add('interest_rate', NumberType::class, [
                'required' => false, 'html5' => false, 'scale' => 4, 'help' => 'Interest rate (e.g. 3% is 0.03)',
                'attr' => ['readonly' => !$instrument->hasInterestRate()]])
            ->add('margin', NumberType::class, [
                  'required' => false, 'html5' => false, 'scale' => 4, 'help' => 'Margin rate (e.g. 20% is 0.2)',
                  'attr' => ['readonly' => !$instrument->hasMargin()]])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
