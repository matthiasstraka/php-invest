<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $currency = $data->getAccount()->getCurrency();

        $builder
            ->add('time', DateTimeType::class, ['label' => 'Time', 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => true])
            ->add('transaction_id', NumberType::class, ['html5' => true, 'required' => false, 'help' => 'Transaction ID used by your broker'])
            ->add('cash', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Cash input/output'])
            ->add('consolidation', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Correction if there is a balance mismatch'])
            ->add('interest', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Interest costs (usually negative)'])
            ->add('consolidated', CheckboxType::class, ['required' => false, 'help' => 'Check if this transaction matches with your broker'])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
