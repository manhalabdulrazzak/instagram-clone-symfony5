<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile()
    {
        if ($this->getUser()) {
            $posts = $this->getUser()->getPosts();
            return $this->render('user/profile.html.twig', [
                'user' => $this->getUser(),
                'following' => count($this->getUser()->getFollowing()),
                'followers' => $this->getCountFollowers($this->getUser()->getId()),
                'postNumber' => count($this->getUser()->getPosts()),
                'posts' => $posts
            ]);
        }
    }

    /**
     * @Route("/profile/{id}", name="target-profile")
     */
    public function targetProfile($id)
    {
        $current_user = $this->getUser();
        $followers = $this->getFollowers($current_user->getId());

        $target_user = $this->repo->find($id);

        $alreadyFollowed = false;
        foreach ($followers as $us) {
            if ($us->getId() === $target_user->getId()) {
                $alreadyFollowed = true;
            }
        }

        return $this->render('user/target-profile.html.twig', [
            'user' => $target_user,
            'alreadyFollowed' => $alreadyFollowed,
            'following' => count($target_user->getFollowing()),
            'followers' => $this->getCountFollowers($target_user->getId()),
            'postNumber' => count($target_user->getPosts()),
        ]);
    }

    /**
     * @Route("/search", name="search", methods={"POST", "GET"})
     */
    public function search(Request $request)
    {
        $string = $request->request->all();
        $user = $this->getUser();

        $follow = $user->getFollowing();
        $all = $this->repo->findAll();

        $notFollowing = [];
        $following = [];

        foreach ($follow as $u) {
            if (in_array($u, $all)) {
                $following[] = $u;
            }
        }
        foreach ($all as $us) {
            if (!in_array($us, $following)) {
                $notFollowing[] = $us;
            }
        }
        return $this->render('user/search.html.twig', [
            'users' => $string ? $this->repo->search($string['search']) : $all,
            'current_user' => $user,
            'following' => $following,
            'notFollowing' => $notFollowing
        ]);
    }

    /**
     * @Route("/follow/{id}", name="follow")
     */
    public function follow(User $user)
    {
        $currentUser = $this->getUser();
        $currentUser->addFollowing($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($currentUser);
        $em->flush();
        return $this->redirectToRoute('search');
    }

    /**
     * @Route("/unfollow/{id}", name="unfollow", methods={"GET","POST"})
     */
    public function unfollow(User $user)
    {
        $currentUser = $this->getUser();
        $currentUser->removeFollowing($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($currentUser);
        $em->flush();
        return $this->redirectToRoute('search');
    }

    private function getCountFollowers($id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = ('SELECT * FROM user_user WHERE user_target=' . $id);
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $count = 0;
        for ($i = 0; $i < count($result); $i++) {
            $count += 1;
        }
        return $count;
    }

    private function getFollowers($id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = ('SELECT * FROM user_user WHERE user_source=' . $id);
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();

        $followers = [];
        foreach ($result as $key => $value) {
            $followers [] = $this->repo->find(intval($value['user_target']));
        }
        return $followers;
    }
}
