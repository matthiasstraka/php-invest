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
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExecutionFormModel::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        //var_dump($data);

        if ($data->account)
        {
            $currency = $data->account->getCurrency();
        }
        else if ($data->instrument)
        {
            $currency = $data->instrument->getCurrency();
        }
        else
        {
            throw new \Exception("Instrument not set");
        }

        $user = $this->token->getToken()->getUser();

        if ($data->instrument) {
            $taxRate = $data->instrument->getTaxRate();
            $builder->add('instrument', TextType::class, ['disabled' =>'true']);
        } else {
            $builder->add('instrument', EntityType::class, ['class' => Instrument::class]);
        }
        
        $builder->add('account', EntityType::class, [
            'class' => Account::class,
            'query_builder' => function (AccountRepository $ar) use ($user, $data) {
                $q = $ar->createQueryBuilder('a')
                    ->where('a.owner = :user');
                if ($data->instrument)
                {
                    $q->andWhere('a.type IN (:accounttypes)');
                    $q->setParameter('accounttypes', $data->instrument->getSupportedAccountTypes());
                }

                $q->orderBy('a.star', 'DESC')
                    ->addOrderBy('a.name')
                    ->setParameter('user', $user);

                return $q;                    
            },
        ]);

        $builder
            ->add('time', DateTimeType::class, ['label' => 'Time', 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => true])
            ->add('direction', ChoiceType::class, ['label' => 'Direction', 'choices'  => [
                'Open' => 1,
                'Close' => -1,
                'Neutral' => 0,
                ]])
            ->add('volume', NumberType::class, ['html5' => false, 'scale' => 6, 'input' => 'string', 'help' => 'Units bought or sold (use negative volume for short positions)'])
            ->add('currency', CurrencyType::class, ['help' => 'Currency of the price'])
            ->add('price', NumberType::class, ['html5' => false, 'scale' => 4, 'input' => 'string', 'help' => 'Price per unit'])
            ->add('exchange_rate', MoneyType::class, ['html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Conversion rate to account currency'])
            ->add('type', ChoiceType::class, ['label' => 'Type', 'choices' => [
                'Market' => Execution::TYPE_MARKET,
                'Limit' => Execution::TYPE_LIMIT,
                'Stop' => Execution::TYPE_STOP,
                'Expired' => Execution::TYPE_EXPIRED,
                'Dividend' => Execution::TYPE_DIVIDEND,
                'Accumulation' => Execution::TYPE_ACCUMULATION,
                ]])
            ->add('transaction_id', NumberType::class, ['label' => 'Transaction ID', 'html5' => true, 'required' => false, 'help' => 'Transaction ID used by the broker'])
            ->add('execution_id', NumberType::class, ['label' => 'Execution ID', 'html5' => true, 'required' => false, 'help' => 'Execution ID used by the broker'])
            ->add('marketplace', TextType::class, ['required' => false, 'help' => 'Location of the exchange'])
            ->add('commission', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Commission cost (negative amount)'])
            ->add('tax', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Paid tax is negative, refunded tax positive' . (!empty($taxRate) ? ' (applying -' . $taxRate . '% of total if not provided)'  : '')])
            ->add('interest', MoneyType::class, ['required' => false, 'html5' => false, 'currency' => $currency, 'scale' => 4, 'help' => 'Paid interest (negative amount)'])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('consolidated', CheckboxType::class, ['required' => false, 'help' => 'Check if this transaction matches with your broker'])
            ->add('save', SubmitType::class, ['label' => 'Submit', 'attr' => ['class' => 'btn btn-primary']])
            ->add('reset', ResetType::class, ['label' => 'Reset', 'attr' => ['class' => 'btn btn-secondary']])
            ->add('back', ButtonType::class, ['label' => 'Back', 'attr' => ['class' => 'btn btn-secondary']])
        ;
    }
}
