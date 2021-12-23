<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Execution;
use App\Entity\Instrument;
use App\Form\Model\ExecutionFormModel;
use App\Repository\InstrumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExecutionType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExecutionFormModel::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        //var_dump($data);

        if ($options['data']->instrument)
        {
            $currency = $options['data']->instrument->getCurrency();
        }
        else
        {
            throw new \Exception("Instrument not set");
        }

        // TODO: Disable field if they are set via options

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
            ->add('direction', ChoiceType::class, ['label' => 'Direction',
                'choices'  => ['Open' => 1, 'Close' => -1, 'Dividend' => 0]])
            ->add('amount', NumberType::class, ['html5' => false, 'input' => 'string'])
            ->add('price', MoneyType::class, ['html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('type', ChoiceType::class, ['label' => 'Type', 'choices' => [
                'Market' => Execution::TYPE_MARKET,
                'Limit' => Execution::TYPE_LIMIT,
                'Stop' => Execution::TYPE_STOP,
                'Expired' => Execution::TYPE_EXPIRED,
                'Dividend' => Execution::TYPE_DIVIDEND,
                ]])
            ->add('external_id', NumberType::class, ['html5' => true, 'input' => 'string', 'required' => false])
            ->add('commission', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('tax', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('interest', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
