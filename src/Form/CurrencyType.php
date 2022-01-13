<?php

namespace App\Form;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Currencies;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [];
        foreach ($this->entityManager->getRepository(Currency::class)->findAll() as $item)
        {
            $code = $item->getCode();
            $name = Currencies::getName($item->getCode());
            $choices["$name ($code)"] = $code;
        }
        $resolver->setDefaults([
            'choices' => $choices,
            //'data_class' => Currency::class,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
