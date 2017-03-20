<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class CommentController extends Controller
{
    /**
     * @Rest\View(serializerGroups={"comments"})
     * @Rest\Get("/comments")
     *
     * @ApiDoc(
     *    description="Recupère les commentaires de l'application",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function getCommentsAction(Request $request)
    {
        $comment = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Comment')
                ->findAll();

        if (empty($comment)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }

        return $comment;
    }
    
    /**
     * @Rest\View(serializerGroups={"comments"})
     * @Rest\Get("/users/{id}/comments")
     *
     * @ApiDoc(
     *    description="Recupère les commentaires de l'utilisateur",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function getCommentUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($request->get('id'));

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $user->getComments();
    }
    
    /**
     * @Rest\View(serializerGroups={"comments"})
     * @Rest\Get("/posts/{id}/comments")
     *
     * @ApiDoc(
     *    description="Recupère les commentaires de l'article",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function getCommentAction(Request $request)
    {
        $post = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Post')
                ->find($request->get('id'));

        if (empty($post)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        return $post->getComments();
    }
    
    /**
     * @Rest\View(serializerGroups={"comments"})
     * @Rest\Post("/users/{id}/posts/{idp}/comments")
     *
     * @ApiDoc(
     *    description="Créé un commentaire dans l'application",
     *    input={"class"=CommentType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * ) 
     * 
     */
    public function postCommentsAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($request->get('id'));

        if (empty($user)) {
            return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $post = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Post')
                ->find($request->get('idp'));

        if (empty($post)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $comment = new Comment();
        $comment->setCreatedAt(new \DateTime('now'));
        $comment->setUser($user);
        $comment->setPosts($post);
        $form = $this->createForm(CommentType::class, $comment);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($comment);
            $em->flush();
            return $comment;
        } else {
            return $form;
        }
    }
    
    /**
     * @Rest\View(serializerGroups={"comments"})
     * @Rest\Delete("/comments/{id}")
     *
     * @ApiDoc(
     *    description="Supprime un commentaire dans l'application",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * ) 
     * 
     */
    public function removeCommentsAction(Request $request)
    { 
        $em = $this->get('doctrine.orm.entity_manager');
        $comment = $em->getRepository('AppBundle:Comment')
                  ->find($request->get('id'));

        if (!$comment) {
            return \FOS\RestBundle\View\View::create(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);;
        }
        
        $em->remove($comment);
        $em->flush();
        
        return \FOS\RestBundle\View\View::create(['message' => 'Comment deleted'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\View(serializerGroups={"comments"})
     * @Rest\Put("/comments/{id}")
     * 
     * @ApiDoc(
     *    description="Modifie complétement un commentaire dans l'application",
     *    input={"class"=CommentType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function updateCommentsAction(Request $request)
    {
        return $this->updateComment($request, true);
    }
    
    /**
     * @Rest\View(serializerGroups={"comments"})
     * @Rest\Patch("/comments/{id}")
     *
     * @ApiDoc(
     *    description="Modifie partiellement un commentaire dans l'application",
     *    input={"class"=CommentType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function patchCommentsAction(Request $request)
    {
        return $this->updateComment($request, false);
    }
    
    /**
     * @Rest\View(serializerGroups={"posts"})
     * @Rest\Put("/posts/{id}")
     */
    public function updateComment(Request $request, $clearMissing)
    {
        $comment = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Comment')
                ->find($request->get('id'));

        if (empty($comment)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        }
        
        if ($clearMissing) {
            $options = ['validation_groups'=>['Default', 'FullUpdate']];
        } else {
            $options = [];
        }

        $form = $this->createForm(CommentType::class, $comment);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->merge($comment);
            $em->flush();
            return $comment;
        } else {
            return $form;
        }
    }

}