<?php

namespace Discovery\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{

    private $authorizationChecker;
    private $dispatcher;
    private $loggerInterface;

    public function __construct(
      AuthorizationChecker $authorizationChecker,
      EventDispatcherInterface $dispatcher
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->dispatcher = $dispatcher;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $event->getAuthenticationToken()->getUser();

            if ($user->isPasswordExpired()) {
                $this->dispatcher->addListener(
                  KernelEvents::RESPONSE,
                  array(
                    $this,
                    'onKernelResponse',
                  )
                );
            }
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = new RedirectResponse('/my-account?action=password');
        $event->setResponse($response);
    }
}