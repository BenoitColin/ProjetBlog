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
     * @Rest\View()
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
     * @Rest\View()
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
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/usersC")
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
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{id}")
     * 
     * @ApiDoc(
     *    description="Supprime un utilisateur dans l'application",
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
            return;
        }

        foreach ($user->getPosts() as $post) {
            $em->remove($post);
        }
        
        $em->remove($user);
        $em->flush();
        
        return \FOS\RestBundle\View\View::create(['message' => 'User deleted'], Response::HTTP_NOT_FOUND);
    }
    
    /**
     * @Rest\View()
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
     * @Rest\View()
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
     * @Rest\View()
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