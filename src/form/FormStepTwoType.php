<?php

namespace App\Form;

use App\Form\data\StepTwoData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepTwoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('geocode_lat', HiddenType::class)
            ->add('geocode_lng', HiddenType::class)
            ->add('next', SubmitType::class,
                ['attr' =>
                    [
                        'class' => 'btn btn-primary rounded-pill py-3 px-5'
                    ]
                ])
            ->add('notfound', SubmitType::class,
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
            "data_class" => StepTwoData::class,
        ]);
    }
}
