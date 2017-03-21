<?php

namespace AppBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Post;
use AppBundle\Entity\Comment;


class LoadFixtures implements FixtureInterface, ContainerAwareInterface

{

    public function load(ObjectManager $manager){

    $encoder = $this->container->get('security.password_encoder');  
        
    $user = new User();
        $user->setFirstname('first');
        $user->setLastname('first');
        $user->setEmail('first@first.local');
        $password = $encoder->encodePassword($user, 'first');
        $user->setPassword($password);
    
    $user2 = new User();
        $user2->setFirstname('second');
        $user2->setLastname('second');
        $user2->setEmail('second@second.local');
        $password2 = $encoder->encodePassword($user2, 'second');
        $user2->setPassword($password2);
    
    $user3 = new User();
        $user3->setFirstname('third');
        $user3->setLastname('third');
        $user3->setEmail('third@third.local');
        $password3 = $encoder->encodePassword($user3, 'third');
        $user3->setPassword($password3);

        $manager->persist($user);
        $manager->persist($user2);
        $manager->persist($user3);
        
    $post = new Post();
        $post->setUser($user);
        $post->setTitle('La tour sombre');
        $post->setContent('Stephen King');
        $post->setCreatedAt(new \DateTime('now'));
    
    $post2 = new Post();
        $post2->setUser($user2);
        $post2->setTitle('Carrie');
        $post2->setContent('Stephen King');
        $post2->setCreatedAt(new \DateTime('now'));
    
    $post3 = new Post();
        $post3->setUser($user2);
        $post3->setTitle('Marche ou creve');
        $post3->setContent('Stephen King');
        $post3->setCreatedAt(new \DateTime('now'));
    
    $post4 = new Post();
        $post4->setUser($user3);
        $post4->setTitle('Le fleau');
        $post4->setContent('Stephen King');
        $post4->setCreatedAt(new \DateTime('now'));
        
    $post5 = new Post();
        $post5->setUser($user3);
        $post5->setTitle('Shining');
        $post5->setContent('Stephen King');
        $post5->setCreatedAt(new \DateTime('now'));
    
    $post6 = new Post();
        $post6->setUser($user);
        $post6->setTitle('Docteur sleep');
        $post6->setContent('Stephen King');
        $post6->setCreatedAt(new \DateTime('now'));
        
        $manager->persist($post);
        $manager->persist($post2);
        $manager->persist($post3);
        $manager->persist($post4);
        $manager->persist($post5);
        $manager->persist($post6);
    
    $comment = new Comment();
        $comment->setUser($user);
        $comment->setPosts($post);
        $comment->setContent('C\'est génial.');
        $comment->setCreatedAt(new \DateTime('now'));
    
    $comment2 = new Comment();
        $comment2->setUser($user);
        $comment2->setPosts($post2);
        $comment2->setContent('C\'est super.');
        $comment2->setCreatedAt(new \DateTime('now'));
        
    $comment3 = new Comment();
        $comment3->setUser($user2);
        $comment3->setPosts($post3);
        $comment3->setContent('C\'est fou.');
        $comment3->setCreatedAt(new \DateTime('now'));
    
    $comment4 = new Comment();
        $comment4->setUser($user2);
        $comment4->setPosts($post4);
        $comment4->setContent('C\'est pas mal.');
        $comment4->setCreatedAt(new \DateTime('now'));
        
    $comment5 = new Comment();
        $comment5->setUser($user3);
        $comment5->setPosts($post5);
        $comment5->setContent('C\'est dingue.');
        $comment5->setCreatedAt(new \DateTime('now'));
    
    $comment6 = new Comment();
        $comment6->setUser($user3);
        $comment6->setPosts($post6);
        $comment6->setContent('C\'est encore mieux.');
        $comment6->setCreatedAt(new \DateTime('now'));   
        
    $comment7 = new Comment();
        $comment7->setUser($user3);
        $comment7->setPosts($post3);
        $comment7->setContent('Nul.');
        $comment7->setCreatedAt(new \DateTime('now'));
        
    $comment8 = new Comment();
        $comment8->setUser($user2);
        $comment8->setPosts($post5);
        $comment8->setContent('Pourri.');
        $comment8->setCreatedAt(new \DateTime('now'));
    
    $comment9 = new Comment();
        $comment9->setUser($user);
        $comment9->setPosts($post6);
        $comment9->setContent('Affreux.');
        $comment9->setCreatedAt(new \DateTime('now'));    
        
        $manager->persist($comment);
        $manager->persist($comment2);
        $manager->persist($comment3);
        $manager->persist($comment4);
        $manager->persist($comment5);
        $manager->persist($comment6);
        $manager->persist($comment7);
        $manager->persist($comment8);
        $manager->persist($comment9);
        
        $manager->flush();

    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

}


