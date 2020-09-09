<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /* INJECTION DE DEPENDANCE*/
    private $repo;

    public function __construct(PostRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @Route("/", name="posts")
     */
    public function index()
    {
        $following = $this->getUser()->getFollowing();
        $actuality = [];
        foreach ($following as $user) {
            $posts = $user->getPosts();
            foreach ($posts as $post) {
                array_push($actuality, $post);
            }
        }
        return $this->render('post/index.html.twig', [
            'actuality' => $actuality,
        ]);
    }

    /**
     * @Route("/create", name="create-post")
     */
    public function create(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSource($form->get('source')->getData());
            $post->setLegend($form->get('legend')->getData());
            $post->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('profile');
        }
        return $this->render('post/create.html.twig', [
            'postForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/like", name="like", methods={"POST"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function like(Request $request)
    {
        $id = $request->request->all();
        $post = $this->repo->find(intval($id['id']));
        $user = $this->getUser();
        $user->addLike($post);
        $post->setLikes($post->getLikes() + 1);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($post);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('posts');
    }
}
