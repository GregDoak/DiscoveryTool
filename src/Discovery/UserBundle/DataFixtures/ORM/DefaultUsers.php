<?php

namespace Helios\InVue\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultUsers implements FixtureInterface, ContainerAwareInterface
{
    /**
     * {@inheritDoc}
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        // Create our user and set details
        $userAdmin = $userManager->createUser();
        $userAdmin->setUsername('admin');
        $userAdmin->setEmail('admin@localhost');
        $userAdmin->setPlainPassword('admin');
        $userAdmin->setFirstName('Admin');
        $userAdmin->setLastName('User');
        $userAdmin->setEnabled(true);
        $userAdmin->setRoles(array('ROLE_SUPER_ADMIN'));
        $userAdmin->setPasswordExpireAt(new \DateTime());

        // Update the user
        $userManager->updateUser($userAdmin, true);
    }
}