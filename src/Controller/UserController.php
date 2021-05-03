<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder)
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

    public function delete(Request $request)
    {
        try {
            $repository = $this->getDoctrine()->getRepository(User::class);
            $email      = $request->request->get('email');
            $userData   = $repository->findOneBy([
                'email' => $email,
            ]);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userData);
            $entityManager->flush();
            return new Response(sprintf('%s successfully removed.', $email));
        } catch (\Exception $e) {
            return new JsonResponse(["error" => "User not found!"], 500);
        }
    }

    public function edite(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        try {
            $data = [];
            $validateData = [];

            if ($request->request->get('userEmail')) {
                $repository = $this->getDoctrine()->getRepository(User::class);
                $email      = $request->request->get('userEmail');
                $user       = $repository->findOneBy([
                    'email' => $email,
                ]);

                if (!$user) {
                    return new JsonResponse(["error" => 'User not exists'], 500);
                }
            } else {
                return new JsonResponse(["error" => 'Please set User Email'], 500);
            }

            if ($request->request->get('firstName')) {
                $data['firstName'] = $request->request->get('firstName');
                $validateData['firstName'] = new Assert\Length(array('min' => 1));
                $user->setFirstName($data['firstName']);
            }

            if ($request->request->get('lastName')) {
                $data['lastName'] = $request->request->get('lastName');
                $validateData['lastName'] = new Assert\Length(array('min' => 1));
                $user->setLastName($data['lastName']);
            }

            if ($request->request->get('userPassword')) {
                $data['userPassword'] = $request->request->get('userPassword');
                $validateData['userPassword'] = new Assert\Length(array('min' => 1));
                $user->setPassword($data['userPassword']);

                $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);
            }

            if (!empty($validateData)) {
                $validator  = Validation::createValidator();
                $constraint = new Assert\Collection($validateData);

                $violations = $validator->validate($data, $constraint);
                if ($violations->count() > 0) {
                    return new JsonResponse(["error" => (string)$violations], 500);
                }
            }

            $userRole = $request->request->get('userRole');

            if ($userRole) {
                $user->setRoles($userRole);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new Response(sprintf('%s already upadeted!', $email));
    }

    public function view(string $email)
    {
        $email = urldecode($email);

        $repository = $this->getDoctrine()->getRepository(User::class);
        $user       = $repository->findOneBy([
            'email' => $email,
        ]);

        if (!$user) {
            return new JsonResponse(["error" => 'User not exists'], 500);
        }

        $data = [
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'Role' => $user->getRoles()
        ];

        return new JsonResponse($data, 200);
    }
}