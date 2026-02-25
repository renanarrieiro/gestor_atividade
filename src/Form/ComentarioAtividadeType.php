<?php

namespace App\Form;

use App\Entity\ComentarioAtividade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ComentarioAtividadeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('texto', TextareaType::class, [
            'label' => false,
            'constraints' => [new NotBlank()],
            'attr' => [
                'rows' => 2,
                'placeholder' => 'Ex.: Esperando cenário de teste do consultor.',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ComentarioAtividade::class,
        ]);
    }
}
