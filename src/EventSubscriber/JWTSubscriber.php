<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTSubscriber implements EventSubscriberInterface
{
    public function onLexikJwtAuthenticationOnJwtCreated(JWTCreatedEvent $event)
    {   
        $data = $event->getData();
        $user = $event->getUser();

        if($user instanceof User) {
             $data['id'] = $user->getId();
             $data['username'] = $user->getEmail();
             $data['roles'] = $user->getRoles();
             
             $data['verified'] = $user->getIsVerified();
             $data['firstUse'] = $user->getFirstUse();
        } 
        
        $event->setData($data);


    }

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onLexikJwtAuthenticationOnJwtCreated',
        ];
    }
}
