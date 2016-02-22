<?php

namespace Discovery\AccountBundle\Controller;

use Discovery\UserBundle\Form\UserPasswordUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Discovery\UserBundle\Form\UserProfileUpdateType;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template("@DiscoveryAccount/Default/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $this->getUser();

        $profile = $this->createForm(
          'Discovery\UserBundle\Form\UserProfileUpdateType',
          $user,
          [
            'action' => '#',
          ]
        );

        $password = $this->createForm(
          'Discovery\UserBundle\Form\UserPasswordUpdateType',
          $user,
          [
            'action' => '?action=password',
          ]
        );

        $profile->handleRequest($request);

        if ($profile->isValid()) {
            $userManager->updateUser($user, true);
            $message = "Profile successfully updated.";
        }

        $password->handleRequest($request);

        if ($password->isValid()) {
            $passwordExpires = new \DateTime();
            $passwordExpires->add(new \DateInterval(('P90D')));
            $user->setPasswordExpireAt($passwordExpires);
            $userManager->updateUser($user, true);
            $message = "Password successfully updated.";
        }

        $activeTabs = ['profile', 'password'];

        $activeTab = (!(empty($request->get('action')))) ? $request->get(
          'action'
        ) : 'profile';
        if (!in_array($activeTab, $activeTabs)) {
            $activeTab = $activeTabs[0];
        }

        return array(
          'entity' => $user,
          'profile' => $profile->createView(),
          'password' => $password->createView(),
          'activeTab' => $activeTab,
          'message' => (isset($message)) ? $message : '',
        );
    }
}
