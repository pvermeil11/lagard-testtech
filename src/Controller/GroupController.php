<?php

namespace App\Controller;

use App\Entity\Group;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends AbstractController
{
    /**
     * @Route("/group/all", name="groups_list", methods={"GET"})
     */
    public function getAllGroups(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('groups_list');
        $calledUri = $request->getRequestUri();
        $groups = $em->getRepository('App\Entity\Group')->findAll();

        if ($groups === null) {
            return new JsonResponse('Could not find any group', Response::HTTP_NOT_FOUND);
        } else {
            $arrayCollection = array();
            foreach ($groups as $group) {
                $arrayCollection[] = array(
                    'id' => $group->getId(),
                    'name' => $group->getName()
                );
            }

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'groups_list', $em);

            return new JsonResponse(
                $arrayCollection,
                200,
                [
                    'Cache-Control' => 's-maxage=60'
                ]
            );
        }
    }

    /**
     * @Route("/group/{name}", name="group_detail", methods={"GET"}, requirements={"name"="[A-Za-z0-9\-]+"})
     */
    public function getGroup(Request $request, $name, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('group_detail');
        $calledUri = $request->getRequestUri();
        $group = $em->getRepository('App\Entity\Group')->findOneBy([
            'name' => $name
        ]);

        $arrayCollection = array();
        foreach ($group->getUsers() as $user) {
            $arrayCollection[] = array(
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail()
            );
        }

        if ($group === null) {
            return new JsonResponse('Could not find group of name ' . $name, Response::HTTP_NOT_FOUND);
        } else {
            $response = array(
                'id' => $group->getId(),
                'name' => $group->getName(),
                'users' => $arrayCollection
            );

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'group_detail', $em);

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
     * @Route("/group", name="create_group", methods={"POST"})
     */
    public function createGroup(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('create_group');
        $calledUri = $request->getRequestUri();

        $post = json_decode($request->getContent(), true);

        $group = new Group();
        $group->setName($post['name']);

        try {
            $em->persist($group);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse($e->getMessage(), 409);
        }

        RequestController::logRequest($calledUri, Response::HTTP_CREATED, $stopwatch, 'create_group', $em);

        return new JsonResponse(array(
            'message' => 'Group of name ' . $post['name'] . ' has been created !'
        ),
            Response::HTTP_CREATED,
            [

            ]
        );
    }

    /**
     * @Route("/group/{name}", name="edit_group", methods={"PATCH"}, requirements={"name"="[A-Za-z0-9\-]+"})
     */
    public function editGroup(Request $request, $name, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('edit_group');
        $calledUri = $request->getRequestUri();

        $edit = json_decode($request->getContent(), true);
        $group = $em->getRepository('App\Entity\Group')->findOneBy([
            'name' => $name
        ]);

        if ($group === null) {
            return new JsonResponse('Could not find group of name ' . $name . ' for edition !',
                Response::HTTP_NOT_FOUND);
        } else {
            $group->setName($edit['name']);
            $em->persist($group);
            $em->flush();

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'edit_group', $em);

            return new JsonResponse(
                array(
                    'message' => 'The group has been renamed to ' . $edit['name']
                ),
                200,
                []
            );
        }
    }

    /**
     * @Route("/group/{name}", name="delete_group", methods={"DELETE"}, requirements={"name"="[A-Za-z0-9\-]+"})
     */
    public function deleteGroup(Request $request, $name, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('delete_group');
        $calledUri = $request->getRequestUri();
        $group = $em->getRepository('App\Entity\Group')->findOneBy(array(
            'name' => $name
        ));

        if ($group === null) {
            return new JsonResponse('Could not find group ' . $name . ' for deletion !', Response::HTTP_NOT_FOUND);
        } else {
            $em->remove($group);
            $em->flush();

            RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'delete_group', $em);

            return new JsonResponse(
                'Group of name ' . $name . ' has been deleted.',
                Response::HTTP_OK,
                []
            );
        }
    }

    /**
     * @Route("/group/useradd", name="useradd_group", methods={"POST"})
     */
    public function addUser(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('useradd_group');
        $calledUri = $request->getRequestUri();

        $post = json_decode($request->getContent(), true);

        // retrieve matching user
        $user = $em->getRepository('App\Entity\User')->findOneBy([
            'username' => $post['username']
        ]);

        if ($user === null) {
            RequestController::logRequest($calledUri, Response::HTTP_NOT_FOUND, $stopwatch, 'useradd_group', $em);
            return new JsonResponse('User ' . $post['username'] . ' could not be found and has not been added to group ' . $post['groupname'],
                404, []);
        } else {
            // retrieve matching group
            $group = $em->getRepository('App\Entity\Group')->findOneBy([
                'name' => $post['groupname']
            ]);

            if ($group === null) {
                RequestController::logRequest($calledUri, Response::HTTP_NOT_FOUND, $stopwatch, 'useradd_group', $em);
                return new JsonResponse('Group of name ' . $post['groupname'] . ' not found : user ' . $post['username'] . ' could not be added to it');
            } else {
                $group->addUser($user);
                $em->persist($group);
                $em->flush();

                RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'useradd_group', $em);

                return new JsonResponse('User ' . $post['username'] . ' has been added in group ' . $post['groupname'],
                    Response::HTTP_OK, []);
            }
        }
    }

    /**
     * @Route("/group/userrm", name="userrm_group", methods={"POST"})
     */
    public function removeUser(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $stopwatch = RequestController::startStopWatch('userrm_group');
        $calledUri = $request->getRequestUri();

        $post = json_decode($request->getContent(), true);

        // retrieve matching user
        $user = $em->getRepository('App\Entity\User')->findOneBy([
            'username' => $post['username']
        ]);

        if ($user === null) {
            RequestController::logRequest($calledUri, Response::HTTP_NOT_FOUND, $stopwatch, 'userrm_group', $em);
            return new JsonResponse('User ' . $post['username'] . ' could not be found and has not been removed from group ' . $post['groupname'],
                404, []);
        } else {
            // retrieve matching group
            $group = $em->getRepository('App\Entity\Group')->findOneBy([
                'name' => $post['groupname']
            ]);

            if ($group === null) {
                RequestController::logRequest($calledUri, Response::HTTP_NOT_FOUND, $stopwatch, 'userrm_group', $em);
                return new JsonResponse('Group of name ' . $post['groupname'] . ' not found : user ' . $post['username'] . ' could not be removed from it');
            } else {
                $group->removeUser($user);
                $em->persist($group);
                $em->flush();

                RequestController::logRequest($calledUri, Response::HTTP_OK, $stopwatch, 'userrm_group', $em);

                return new JsonResponse('User ' . $post['username'] . ' has been removed from group ' . $post['groupname'],
                    Response::HTTP_OK, []);
            }
        }
    }
}
