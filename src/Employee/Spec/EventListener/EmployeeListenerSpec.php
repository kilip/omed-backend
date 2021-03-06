<?php

declare(strict_types=1);

namespace Spec\Omed\Employee\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use Omed\Employee\EventListener\EmployeeListener;
use Omed\Resource\Entity\Address;
use Omed\Security\Model\SecurityUserInterface;
use PhpSpec\ObjectBehavior;

class EmployeeListenerSpec extends ObjectBehavior
{
    public function let(
        CanonicalFieldsUpdater $canonicalFieldsUpdater,
        LifecycleEventArgs $args,
        SecurityUserInterface $entity,
        UserInterface $user,
        ObjectManager $manager
    ) {
        $args->getEntityManager()->willReturn($manager);
        $args->getEntity()->willReturn($entity);
        $entity->getLogin()->willReturn($user);
        $this->beConstructedWith($canonicalFieldsUpdater);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(EmployeeListener::class);
    }

    public function it_should_process_only_security_user_interface_entity(
        LifecycleEventArgs $args
    ) {
        $entity = new Address();
        $args
            ->getEntity()
            ->shouldBeCalled()
            ->willReturn($entity)
        ;
        $args->getEntityManager()->shouldNotBeCalled();
        $this->postUpdate($args);
    }

    public function it_should_email_with_security_user(
        LifecycleEventArgs $args,
        ObjectManager $manager,
        SecurityUserInterface $entity,
        UserInterface $user
    ) {
        $entity->getEmail()
            ->shouldBeCalled()
            ->willReturn('some@example.com')
        ;
        $user
            ->getEmail()
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $user
            ->setEmail('some@example.com')
            ->shouldBeCalled()
        ;

        $manager->persist($user)
            ->shouldBeCalled()
        ;

        $manager->flush($user)
            ->shouldBeCalled()
        ;
        $this->postUpdate($args);
    }
}
