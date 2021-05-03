<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Auth;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;

class AuthController extends AbstractController
{
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $data = [
            'firstName' => $request->request->get('firstName'),
            'lastName' => $request->request->get('lastName'),
            'userPassword' => $request->request->get('userPassword'),
            'userEmail' => $request->request->get('userEmail')
        ];

        $validator = Validation::createValidator();
        $constraint = new Assert\Collection(array(
            // the keys correspond to the keys in the input array
            'firstName' => new Assert\Length(array('min' => 1)),
            'lastName' => new Assert\Length(array('min' => 1)),
            'userPassword' => new Assert\Length(array('min' => 1)),
            'userEmail' => new Assert\Email()
        ));
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            return new JsonResponse(["error" => (string)$violations], 500);
        }
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $userPassword = $data['userPassword'];
        $userEmail = $data['userEmail'];
        $userRole = $request->request->get('userRole');

        $user = new User();
        $user
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setPassword($userPassword)
            ->setEmail($userEmail)
            ->setRoles($userRole)
            ->onPrePersist()
        ;

        $password = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new JsonResponse(["success" => $user->getUsername(). " has been registered!"], 200);
    }

    public function login(Request $request, JWTTokenManagerInterface $JWTManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        try {
            $repository = $this->getDoctrine()->getRepository(User::class);
            $userData = $repository->findOneBy([
                'email' => $request->request->get('username'),
            ]);

            $validePassword = $passwordEncoder->isPasswordValid($userData, $request->request->get('password'));

            if (!$validePassword) {
                return new JsonResponse(["error" => 'Wrong creadentials.'], 500);
            }

            $token =  $JWTManager->create($userData);

            $userToken = new JWTUserToken($userData->getRoles(), $userData, $token);

            $userInformations = array(
                'id'         => $userData->getId(),
                'username'   => $userData->getUsername(),
                'email'      => $userData->getEmail(),
                'roles'      => $userData->getRoles(),
                'token'      => $token
            );

            $auth = new Auth();

            $repository = $this->getDoctrine()->getRepository(Auth::class);
            $existAuth = $repository->findOneBy([
                'user' => $userData
            ]);

            $entityManager = $this->getDoctrine()->getManager();

            if ($existAuth) {
                $existAuth
                    ->setToken($token)
                    ->onPreUpdate();
            } else {
                $auth
                    ->setUser($userData)
                    ->setToken($token)
                    ->onPrePersist();

                $entityManager->persist($auth);
            }

            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }

        return $this->json($userInformations);
    }

    public function api()
    {
        return new Response(sprintf('Logged in as %s', $this->getUser()->getUsername()));
    }
}