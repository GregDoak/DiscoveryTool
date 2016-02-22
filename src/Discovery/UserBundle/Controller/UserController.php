<?php

namespace Discovery\UserBundle\Controller;

use Discovery\UserBundle\Entity\User;
use Discovery\UserBundle\Form\UserCreateType;
use Discovery\UserBundle\Form\UserDeleteType;
use Discovery\UserBundle\Form\UserUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/admin/users")
     * @Template("@DiscoveryUser/Default/index.html.twig")
     */
    public function usersIndexAction()
    {
        $data = [];

        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        foreach ($users as $user) {
            $data[] = [
              'id' => $user->getId(),
              'username' => $user->getUsername(),
              'email' => $user->getEmail(),
              'firstName' => $user->getFirstName(),
              'lastName' => $user->getLastName(),
              'fullName' => $user->getFullName(),
              'lastLogin' => $user->getLastLogin(),
            ];
        }

        return array(
          'users' => $data,
        );
    }

    /**
     * @Route("/admin/user/create")
     * @Template("@DiscoveryUser/Default/create.html.twig")
     */
    public function userCreateAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(
          'Discovery\UserBundle\Form\UserCreateType',
          $user,
          [
            'action' => '#',
          ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->addRole('ROLE_SUPER_ADMIN');
            $user->setEnabled(true);
            $user->setPasswordExpireAt(new \DateTime());

            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user, true);

            return $this->redirect('/admin/users');
        }

        return array(
          'user' => $user,
          'form' => $form->createView(),
          'message' => (isset($message)) ? $message : '',
        );

    }

    /**
     * @Route("/admin/user/update/{id}")
     * @Template("@DiscoveryUser/Default/update.html.twig")
     */
    public function userUpdateAction($id)
    {
        $request = $this->container->get('request');

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(['id' => $id]);

        if ($user) {
            $form = $this->createForm(
              'Discovery\UserBundle\Form\UserUpdateType',
              $user,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $userManager->updateUser($user, true);
                $message = "Profile successfully updated.";
            }
        }

        return array(
          'user' => $user,
          'form' => $form->createView(),
          'message' => (isset($message)) ? $message : '',
        );
    }

    /**
     * @Route("/admin/user/delete/{id}")
     * @Template("@DiscoveryUser/Default/delete.html.twig")
     */
    public function userDeleteAction($id)
    {
        $request = $this->container->get('request');

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(['id' => $id]);

        if ($user) {
            $form = $this->createForm(
              'Discovery\UserBundle\Form\UserDeleteType',
              $user,
              [
                'action' => '#',
              ]
            );

            $form->handleRequest($request);

            if ($form->isValid()) {
                $userManager->deleteUser($user);

                return $this->redirect('/admin/users');
            }
        }

        return array(
          'user' => $user,
          'form' => $form->createView(),
          'message' => (isset($message)) ? $message : '',
        );
    }
}
