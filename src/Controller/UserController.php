<?php

declare(strict_types=1);

namespace App\Controller;


use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Controller\AbstractApiController;
use Symfony\Component\Security\Core\User\UserInterface;







class UserController extends AbstractApiController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder )
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function indexAction(Request $request): Response
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->respond($users);
    }


    public function createAction( Request $request ): Response
    {

        $form = $this->buildForm(UserType::class);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->respond($form, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $form->getData();



        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();


        return $this->respond($user);
    }
    public function deleteAction(Request $request): Response
    {
        $userId= $request->get('id');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'id'=>$userId,
        ]);

        if(!$user) {
            throw new NotFoundHttpException('User is not found');
        }
        $this->getDoctrine()->getManager()->remove($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->respond('user is deleted');

    }
    public function updateAction(Request $request): Response
    {
        $userId = $request->get('id');

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $userId]);

        if (!$user) {
            throw new NotFoundHttpException('User is not found');
        }

        $users = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'id' => $userId,
        ]);

        $form = $this->buildForm(UserType::class, $user, [
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->respond($form, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $form->getData();

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->respond($user);
    }

}
