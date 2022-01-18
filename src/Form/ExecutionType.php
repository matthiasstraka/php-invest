<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Execution;
use App\Entity\Instrument;
use App\Form\Model\ExecutionFormModel;
use App\Repository\AccountRepository;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExecutionType extends AbstractType
{
    private $entityManager;
    private $token;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $token)
    {
        $this->entityManager = $entityManager;
        $this->token = $token;
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

        $user = $this->token->getToken()->getUser();

        $builder
            ->add('instrument', TextType::class, ['disabled' =>'true'])
            ->add('account', EntityType::class, ['class' => Account::class,
                'query_builder' => function (AccountRepository $ar) use ($user) {
                    return $ar->createQueryBuilder('a')
                        ->where('a.owner = :user')
                        ->orderBy('a.star', 'DESC')
                        ->addOrderBy('a.name')
                        ->setParameter('user', $user);
                },
            ])
            ->add('time', DateTimeType::class, ['label' => 'Time', 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => true])
            ->add('direction', ChoiceType::class, ['label' => 'Direction',
                'choices'  => ['Open' => 1, 'Close' => -1, 'Dividend' => 0]])
            ->add('volume', NumberType::class, ['html5' => false, 'input' => 'string', 'help' => 'Units bought or sold (use negative volume for short positions)'])
            ->add('price', MoneyType::class, ['html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Price per unit'])
            ->add('type', ChoiceType::class, ['label' => 'Type', 'choices' => [
                'Market' => Execution::TYPE_MARKET,
                'Limit' => Execution::TYPE_LIMIT,
                'Stop' => Execution::TYPE_STOP,
                'Expired' => Execution::TYPE_EXPIRED,
                'Dividend' => Execution::TYPE_DIVIDEND,
                ]])
            ->add('external_id', NumberType::class, ['html5' => true, 'input' => 'string', 'required' => false, 'help' => 'Transaction ID used by your broker'])
            ->add('commission', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Commission cost (negative amount)'])
            ->add('tax', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'paid tax is negative, refunded tax positive'])
            ->add('interest', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Paid interest (negative amount)'])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
