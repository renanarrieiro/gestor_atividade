<?php

namespace App\Form;

use App\Entity\SapRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SapRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo', ChoiceType::class, [
                'label' => 'Tipo de Request',
                'choices' => [
                    'Workbench' => 'Workbench',
                    'Customizing' => 'Customizing',
                    'Transport of Copies' => 'Transport of Copies',
                ],
            ])
            ->add('numero', TextType::class, [
                'label' => 'Número',
                'constraints' => [new NotBlank(), new Length(max: 30)],
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'Descrição',
                'attr' => ['rows' => 2],
                'constraints' => [new NotBlank()],
            ])
            ->add('modulo', TextType::class, [
                'label' => 'Módulo',
                'constraints' => [new NotBlank(), new Length(max: 30)],
            ])
            ->add('usuario', TextType::class, [
                'label' => 'Usuário',
                'constraints' => [new NotBlank(), new Length(max: 60)],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Modificável' => 'Modificável',
                    'Liberada' => 'Liberada',
                    'Importada QA' => 'Importada QA',
                    'Importada PRD' => 'Importada PRD',
                ],
            ])
            ->add('dataRequest', DateType::class, [
                'label' => 'Data',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SapRequest::class,
        ]);
    }
}
