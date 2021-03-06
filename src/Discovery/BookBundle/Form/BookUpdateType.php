<?php

namespace Discovery\BookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookUpdateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('googleUID')
          ->add(
            'opacURL',
            'Symfony\Component\Form\Extension\Core\Type\UrlType',
            [
              'required' => false,
            ]
          )
          ->add('processed')
          ->add(
            'submit',
            'Symfony\Component\Form\Extension\Core\Type\SubmitType'
          );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
          array(
            'data_class' => 'Discovery\BookBundle\Entity\Book',
          )
        );
    }
}
