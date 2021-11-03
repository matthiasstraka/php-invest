<?php

namespace App\Form;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Countries;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [];
        foreach ($this->entityManager->getRepository(Country::class)->findAll() as $item)
        {
            $code = $item->getCode();
            $name = Countries::getName($item->getCode());
            $choices["$name ($code)"] = $code;
        }
        $resolver->setDefaults(['choices' => $choices]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
