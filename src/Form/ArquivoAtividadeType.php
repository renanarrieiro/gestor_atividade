<?php

namespace App\Form;

use App\Entity\ArquivoAtividade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArquivoAtividadeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo', HiddenType::class)
            ->add('arquivo', FileType::class, [
                'mapped' => false,
                'label' => 'Selecionar arquivo',
                'constraints' => [
                    new NotBlank(),
                    new File(maxSize: '20M'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArquivoAtividade::class,
        ]);
    }
}
