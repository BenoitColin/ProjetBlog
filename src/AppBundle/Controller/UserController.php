<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class UserController extends Controller
{
    
    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Get("/users")
     *
     * @ApiDoc(
     *    description="Recupère les utilisateurs de l'application",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     */
    public function getUsersAction()
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->findAll();

        return $user;
    }
    
    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Get("/users/{id}")
     * 
     * @ApiDoc(
     *    description="Recupère un utilisateur de l'application",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     */
    public function getUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($request->get('id'));

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $user;
    }
    
    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Post("/users")
     * 
     * @ApiDoc(
     *    description="Créé un utilisateur dans l'application",
     *    input={"class"=UserType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);
            
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }
    
    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Delete("/users/{id}")
     * 
     * @ApiDoc(
     *    description="Supprime un utilisateur ainsi que ses articles et les commentaires associés dans l'application",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     */
    public function removeUserAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('AppBundle:User')
                    ->find($request->get('id'));

        if (!$user) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        foreach ($user->getPosts() as $post) {
            foreach ($post->getComments() as $comment) {
                $em->remove($comment);
            }
            $em->remove($post);
        }
        
        foreach ($user->getComments() as $comment) {
            $em->remove($comment);
        }
        
        $ema = $this->get('doctrine.orm.entity_manager');
        $auth = $ema->getRepository('AppBundle:AuthToken')
                    ->findAll();
        foreach ($auth as $authId) {
            if($authId->getUser()->getId() === $user->getId()){
                $em->remove($authId);
            }
        }

        $em->remove($user);
        $em->flush();
        
        return \FOS\RestBundle\View\View::create(['message' => 'User deleted'], Response::HTTP_NOT_FOUND);
    }
    
    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Put("/users/{id}")
     * 
     * @ApiDoc(
     *    description="Modifie complétement un utilisateur dans l'application",
     *    input={"class"=UserType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     */
    public function updateUserAction(Request $request)
    {
        return $this->updateUser($request, true);
    }
    
    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Patch("/users/{id}")
     * 
     * @ApiDoc(
     *    description="Modifie partiellement un utilisateur dans l'application",
     *    input={"class"=UserType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     */
    public function patchUserAction(Request $request)
    {
        return $this->updateUser($request, false);
    }
    
    /**
     * @Rest\View(serializerGroups={"users"})
     * @Rest\Put("/users/{id}")
     */
    public function updateUser(Request $request, $clearMissing)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($request->get('id'));

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        if ($clearMissing) {
            $options = ['validation_groups'=>['Default', 'FullUpdate']];
        } else {
            $options = [];
        }
        
        if(!($this->get('security.context')->getToken()->getUser()->getId()===$user->getId())){
            return \FOS\RestBundle\View\View::create(['message' => 'User\'s property invalid'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            if (!empty($user->getPlainPassword())) {
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encoded);
            }
            $em = $this->get('doctrine.orm.entity_manager');
            $em->merge($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }
}