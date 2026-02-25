<?php

namespace App\Form;

use App\Entity\RegistroHora;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistroHoraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('data', DateType::class, [
                'label' => 'Data',
                'widget' => 'single_text',
            ])
            ->add('horasTrabalhadas', IntegerType::class, [
                'label' => 'Horas trabalhadas',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0),
                ],
            ])
            ->add('comentarios', TextareaType::class, [
                'label' => 'Comentários',
                'attr' => ['rows' => 3],
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 2000),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistroHora::class,
        ]);
    }
}
