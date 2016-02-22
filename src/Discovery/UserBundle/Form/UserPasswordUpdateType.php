<?php

namespace Discovery\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserPasswordUpdateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add(
            'current_password',
            'Symfony\Component\Form\Extension\Core\Type\PasswordType',
            array(
              'translation_domain' => 'FOSUserBundle',
              'mapped' => false,
              'constraints' => new UserPassword(),
            )
          )
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
