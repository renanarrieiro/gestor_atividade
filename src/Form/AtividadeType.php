<?php

namespace App\Form;

use App\Entity\Atividade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AtividadeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idExterno', TextType::class, [
                'label' => 'ID Externo',
                'constraints' => [new NotBlank(), new Length(max: 60)],
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'Descrição',
                'constraints' => [new NotBlank()],
                'attr' => ['rows' => 3],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Atividade::class,
        ]);
    }
}
