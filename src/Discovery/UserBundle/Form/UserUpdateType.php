<?php

namespace Discovery\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('firstName')
          ->add('lastName')
          ->add('username')
          ->add('email')
          ->add('enabled')
          ->add('locked')
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
            'data_class' => 'Discovery\UserBundle\Entity\User',
          )
        );
    }
}
