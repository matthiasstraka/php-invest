<?php

namespace App\Form;

use App\Entity\Account;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('number', TextType::class, ['required' => false])
            ->add('currency', CurrencyType::class)
            ->add('timezone', TimezoneType::class)
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
