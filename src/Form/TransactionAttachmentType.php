<?php

namespace App\Form;

use App\Entity\TransactionAttachment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionAttachmentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransactionAttachment::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];

        $builder
            ->add('file', FileType::class, [
                'label' => 'Document',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File(
                        maxSize: '1024k',
                        mimeTypes: [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        mimeTypesMessage: 'Please upload a valid PDF document',
                    )
                ],
            ])
            ->add('upload', SubmitType::class, ['label' => 'Upload', 'attr' => ['class' => 'btn btn-primary']])
        ;
    }
}
