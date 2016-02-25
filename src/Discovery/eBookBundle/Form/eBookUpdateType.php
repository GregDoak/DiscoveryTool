<?php

namespace Discovery\eBookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class eBookUpdateType extends AbstractType
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
          ->add('url', 'Symfony\Component\Form\Extension\Core\Type\UrlType')
          ->add(
            'linkType',
            'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            [
              'choices' => ['FULL', 'SAMPLE'],
              'empty_data' => null,
              'required' => false,
            ]
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
            'data_class' => 'Discovery\eBookBundle\Entity\eBook',
          )
        );
    }
}
