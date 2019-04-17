<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UserController extends AbstractController
{
    /**
     * @Route("/user/all", name="users_list", methods={"GET"})
     */
    public function getAllUsers(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('users_list');
        $calledUri = $request->getRequestUri();
        $users = $em->getRepository('App\Entity\User')->findAll();

        if ($users === null) {
            RequestController::logRequest($calledUri, Response::HTTP_NOT_FOUND, $stopwatch, 'user_detail', $em);
            return new JsonResponse('Could not find any user', Response::HTTP_NOT_FOUND);
        } else {
            $arrayCollection = array();
            foreach ($users as $user) {
                $arrayCollection[] = array(
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'username' => $user->getUsername()
                );
            }

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'users_list', $em);

            return new JsonResponse($arrayCollection, Response::HTTP_OK, [
                'Cache-Control' => 's-maxage=60'
            ]);
        }
    }

    /**
     * @Route("/user/{username}", name="user_detail", methods={"GET"}, requirements={"username"="[A-Za-z0-9\-]+"})
     */
    public function getUserDetail(Request $request, $username, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('user_detail');
        $calledUri = $request->getRequestUri();
        $user = $em->getRepository('App\Entity\User')->findOneBy([
            'username' => $username
        ]);

        $arrayCollection = array();
        foreach ($user->getGroups() as $group) {
            $arrayCollection[] = array(
                'id' => $group->getId(),
                'name' => $group->getName()
            );
        }

        if ($user === null) {
            RequestController::logRequest($calledUri, Response::HTTP_NOT_FOUND, $stopwatch, 'user_detail', $em);

            return new JsonResponse('Could not find user of name ' . $username, Response::HTTP_NOT_FOUND);
        } else {
            $response = array(
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'groups' => $arrayCollection
            );

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'user_detail', $em);

            return new JsonResponse(
                $response,
                Response::HTTP_OK,
                [
                    'Cache-Control' => 's-maxage=60'
                ]
            );
        }
    }

    /**
     * @Route("/user", name="create_user", methods={"POST"})
     */
    public function createUser(Request $request, EntityManagerInterface $em): Response
    {
        $stopwatch = RequestController::startStopWatch('create_user');
        $calledUri = $request->getRequestUri();

        $post = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($post['email']);
        $user->setUsername($post['username']);
        $user->setFirstName($post['firstName']);
        $user->setLastName($post['lastName']);

        try {
            $em->persist($user);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }


        RequestController::logRequest($calledUri, Response::HTTP_CREATED, $stopwatch, 'create_user', $em);

        return new JsonResponse(array(
            'message' => 'User ' . $post['username'] . ' has been created !'
        ),
            Response::HTTP_CREATED,
            []);
    }

    /**
     * @Route("/user/{username}", name="edit_user", methods={"PATCH"}, requirements={"username"="[A-Za-z0-9\-]+"})
     */
    public function editUser(Request $request, $username, EntityManagerInterface $em): Response
    {
        $stopwatch = RequestController::startStopWatch('edit_user');
        $calledUri = $request->getRequestUri();

        $edit = json_decode($request->getContent(), true);
        $user = $em->getRepository('App\Entity\User')->findOneBy([
            'username' => $username
        ]);

        if ($user === null) {
            return new JsonResponse('Could not user ' . $username . ' for edition.', Response::HTTP_NOT_FOUND);
        } else {
            $user->setEmail($edit['email']);
            $user->setUsername($edit['username']);
            $user->setFirstName($edit['firstName']);
            $user->setLastName($edit['lastName']);
            $em->persist($user);
            $em->flush();

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'edit_user', $em);

            return new JsonResponse(
                array(
                    'user of name ' . $edit['username'] . ' has been modified'
                ),
                200,
                []
            );
        }
    }

    /**
     * @Route("/user/{username}", name="delete_user", methods={"DELETE"}, requirements={"username"="[A-Za-z0-9\-]+"})
     */
    public function deleteUser(Request $request, $username, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('delete_user');
        $calledUri = $request->getRequestUri();
        $user = $em->getRepository('App\Entity\User')->findOneBy(array(
            'username' => $username
        ));

        if ($user === null) {
            return new JsonResponse('Could not find user ' . $username . ' for deletion !', Response::HTTP_NOT_FOUND);
        } else {
            $em->remove($user);
            $em->flush();

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'delete_user', $em);

            return new JsonResponse(
                'User of name ' . $username . ' has been deleted.',
                Response::HTTP_OK,
                []
            );
        }
    }
}
