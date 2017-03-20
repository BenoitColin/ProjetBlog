<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use AppBundle\Form\CredentialsType;
use AppBundle\Entity\AuthToken;
use AppBundle\Entity\Credentials;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class AuthTokenController extends Controller
{
    /**
     * @Rest\View(serializerGroups={"auth_tokens"})
     * @Rest\Post("/auth-tokens")
     * 
     * @ApiDoc(
     *    description="Créé un token dans l'application",
     *    input={"class"=CredentialsType::class, "name"=""},
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function postAuthTokensAction(Request $request)
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('AppBundle:User')
            ->findOneByEmail($credentials->getLogin());

        if(!$user){
            return $this->invalidCredentials();
        }

        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());

        if (!$isPasswordValid) {
            return $this->invalidCredentials();
        }

        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));
        $authToken->setUser($user);

        $em->persist($authToken);
        $em->flush();
        
        return $authToken;
    }

    /**
     * @Rest\View(serializerGroups={"auth_tokens"})
     * @Rest\Delete("/auth-tokens/{id}")
     * 
     * @ApiDoc(
     *    description="Supprime un token dans l'application",
     *    statusCodes = {
     *        201 = "ok",
     *        400 = "Formulaire invalide"
     *    }
     * )
     * 
     */
    public function removeAuthTokensAction(Request $request)
    {
        $ema = $this->get('doctrine.orm.entity_manager');
        $token = $ema->getRepository('AppBundle:AuthToken')
                  ->find($request->get('id'));

        if (!$token) {
            return \FOS\RestBundle\View\View::create(['message' => 'Token not found'], Response::HTTP_NOT_FOUND);;
        }
        
        $ema->remove($token);
        $ema->flush();
        
        return \FOS\RestBundle\View\View::create(['message' => 'Token deleted'], Response::HTTP_NOT_FOUND);
        
    }

    private function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
    }
}