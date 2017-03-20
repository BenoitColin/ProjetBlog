<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class PostController extends Controller
{
    
    /**
     * @Rest\View()
     * @Rest\Get("/posts")
     *
     * @ApiDoc(
     *    description="Recupère les articles de l'application",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function getPostAction(Request $request)
    {
        $post = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Post')
                ->findAll();

        if (empty($post)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        return $post;
    }
    
    /**
     * @Rest\View()
     * @Rest\Get("/users/{id}/posts")
     *
     * @ApiDoc(
     *    description="Recupère les articles de l'utilisateur",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function getPostsAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($request->get('id'));

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $user->getPosts();
    }
    
    
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/users/{id}/posts")
     *
     * @ApiDoc(
     *    description="Créé un article dans l'application",
     *    input={"class"=PostType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * ) 
     * 
     */
    public function postPostsAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($request->get('id'));

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $post = new Post();
        $post->setCreatedAt(new \DateTime('now'));
        $post->setUser($user);
        $form = $this->createForm(PostType::class, $post);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($post);
            $em->flush();
            return $post;
        } else {
            return $form;
        }
    }
    
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Delete("/posts/{id}")
     *
     */
    public function removePostsAction(Request $request)
    { 
        $em = $this->get('doctrine.orm.entity_manager');
        $post = $em->getRepository('AppBundle:Post')
                  ->find($request->get('id'));

        if ($post) {
            $em->remove($post);
            $em->flush();
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/posts/{id}")
     */
    public function updatePostAction(Request $request)
    {
        return $this->updatePost($request, true);
    }
    
    /**
     * @Rest\View()
     * @Rest\Patch("/posts/{id}")
     */
    public function patchPostAction(Request $request)
    {
        return $this->updatePost($request, false);
    }
    
    /**
     * @Rest\View()
     * @Rest\Put("/posts/{id}")
     */
    public function updatePost(Request $request, $clearMissing)
    {
        $post = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Post')
                ->find($request->get('id'));

        if (empty($post)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }
        
        if ($clearMissing) {
            $options = ['validation_groups'=>['Default', 'FullUpdate']];
        } else {
            $options = [];
        }

        $form = $this->createForm(PostType::class, $post);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->merge($post);
            $em->flush();
            return $post;
        } else {
            return $form;
        }
    }
}