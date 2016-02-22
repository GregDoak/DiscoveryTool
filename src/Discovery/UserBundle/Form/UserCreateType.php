<?php

namespace Discovery\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserCreateType extends AbstractType
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
          ->add(
            'plain_password',
            RepeatedType::class,
            array(
              'type' => PasswordType::class,
              'invalid_message' => 'The password fields must match.',
              'options' => array('attr' => array('class' => 'password-field')),
              'required' => true,
              'first_options' => array('label' => 'Password'),
              'second_options' => array('label' => 'Repeat Password'),
            )
          )
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
