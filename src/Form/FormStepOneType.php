<?php

namespace App\Form;

use App\Form\data\StepOneData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\data\UserInfoData;

class StepOneFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextareaType::class,
                ['attr' =>
                    ['class' =>'form-control border-0',
                     'placeholder' => 'Votre adresse'
                    ]
                ])
            ->add('search', SubmitType::class,
                ['attr' =>
                    [
                        'class' => 'btn btn-primary rounded-pill py-3 px-5'
                    ]
                ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            "data_class" => StepOneData::class,
        ]);
    }
}
