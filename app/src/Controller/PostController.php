<?php

/**
 * PostController file
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Factory\Form\CommentForm;
use App\Factory\Form\FormException;
use App\Factory\Manager\Manager;
use App\Factory\Router\Response;
use App\Factory\Router\Route;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Service\Container\Container;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * PostController class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class PostController extends AbstractController
{

    private PostRepository $postRepository;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->postRepository = new PostRepository();
    }


    /**
     * Posts list of Post controller
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    #[Route("/posts", "app_posts")]
    public function showPosts(): Response
    {
        $posts = $this->postRepository->getPostsPublishedByOrderDate("DESC");

        return $this->render("post/show-posts.html.twig", [
            "posts" => $posts
        ]);
    }


    /**
     * Post details by Id of Post controller
     *
     * @throws SyntaxError|RuntimeError|LoaderError
     * @throws FormException
     * @throws Exception
     */
    #[Route(
        "/post/:id",
        "app_post_details",
        regexs: ["id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        methods: ["GET", "POST"]
    )]
    public function showPost(string $postId): Response
    {
        $post = $this->postRepository->getPostByIdWithUser($postId);

        if (empty($post)) {
            return $this->responseHttpNotFound();
        }


        $comments = (new CommentRepository())->getCommentsByPostId($postId);

        $form = null;

        if ($this->user()) {
            $comment = new Comment();

            $form = new CommentForm($comment);
            $form->handleRequest();

            if ($form->isSubmitted() && $form->isValid()) {
                /**
                 * @var Manager $manager
                 */
                $manager = Container::getService("manager");
                /**
                 * @var string $userId
                 */
                $userId = $this->user()->getId();

                if ($this->user()->getRole() === "ROLE_ADMIN") {
                    $comment->setValidByUserId($userId);
                    $comment->setIsValid(true);
                }
                $comment->setUserId($userId);
                $comment->setPostId($postId);

                $manager->flush($comment);

                return $this->redirectTo("app_post_details", ["id" => $postId]);
            }

            $form = [
                "errors" => $form->getErrors(),
                "data" => $form->getData(),
                "isSubmitted" => $form->isSubmitted(),
            ];
        }

        return $this->render("post/show-post.html.twig", [
            "post" => $post,
            "comments" => $comments,
            "form" => $form
        ]);
    }
}
