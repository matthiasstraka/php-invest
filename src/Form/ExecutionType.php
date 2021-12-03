<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Execution;
use App\Entity\Instrument;
use App\Repository\InstrumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ExecutionType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('instrument', EntityType::class, [
                'class' => Instrument::class,
                'query_builder' => function (InstrumentRepository $ir) {
                    return $ir->createQueryBuilder('i')
                        ->orderBy('i.name', 'ASC');
                },
                'group_by' => function($val, $key, $index) { return $val->getClassName(); },
                ])
            ->add('account', EntityType::class, ['class' => Account::class])
            ->add('time', DateTimeType::class, ['label' => 'Time', 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => true])
            ->add('direction', ChoiceType::class, ['label' => 'Direction', 'choices'  => ['Buy' => 1, 'Sell' => -1]])
            ->add('amount', NumberType::class, ['html5' => false, 'input' => 'string'])
            ->add('price', MoneyType::class, ['html5' => false, 'currency' => 'EUR', 'scale' => 4]) # TODO
            ->add('type', ChoiceType::class, ['label' => 'Type', 'choices' => [
                'Market' => Execution::TYPE_MARKET,
                'Limit' => Execution::TYPE_LIMIT,
                'Stop' => Execution::TYPE_STOP,
                'Expired' => Execution::TYPE_EXPIRED,
                ]])
            ->add('external_id', NumberType::class, ['html5' => true, 'input' => 'string', 'required' => false])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
        ;
    }
}
