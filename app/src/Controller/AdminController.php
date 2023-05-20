<?php

/**
 * AdminController file
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
use App\Entity\Post;
use App\Entity\User;
use App\Factory\Form\FormException;
use App\Factory\Form\PostForm;
use App\Factory\Manager\Manager;
use App\Factory\Router\Response;
use App\Factory\Router\Route;
use App\Factory\Router\RouterException;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\Container\Container;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * AdminController class
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
class AdminController extends AbstractController
{

    const PAGES_DASHBOARD = [
        "users",
        "posts",
        "comments"
    ];

    private Manager $manager;


    public function __construct()
    {
        parent::__construct();
        /**
         * @var Manager $manager
         */
        $manager = Container::getService("manager");
        $this->manager = $manager;
    }


    /**
     * Index page of admin controller
     *
     * @return Response
     *
     * @throws RouterException
     */
    #[Route(
        "/admin/dashboard",
        "app_admin_dashboard",
        granted: "ROLE_ADMIN"
    )]
    public function index(): Response
    {
        return $this->redirectTo("app_admin_page", [
            "page" => "users"
        ]);

    }


    /**
     * Index page of admin controller
     *
     * @param string $page
     *
     * @return Response
     *
     * @throws LoaderError|RuntimeError|SyntaxError
     * @throws Exception
     */
    #[Route(
        "/admin/dashboard/:page",
        "app_admin_page",
        regexs: ["page" => "(\w)+"],
        granted: "ROLE_ADMIN"
    )]
    public function page(string $page): Response
    {
        if (!in_array($page, self::PAGES_DASHBOARD)) {
            return $this->responseHttpNotFound();

        }

        return $this->render("admin/dashboard.html.twig", [
            "page" => $page
        ]);

    }

    /**
     * Get entities with name page
     */
    #[Route(
        "/admin/dashboard/entities/:page",
        "app_admin_entities_page",
        regexs: ["page" => "(\w)+"],
        granted: "ROLE_ADMIN"
    )]
    public function getEntities(string $page): Response
    {
        if (!in_array($page, self::PAGES_DASHBOARD)) {
            return $this->responseHttpNotFound();

        }

        /**
         * @var array<int, object> $entities
         */
        $entities = [];

        switch ($page) {
            case "users":
                $entities = (new UserRepository())->getUsersForDashboard();
                break;
            case "posts":
                $entities = (new PostRepository())->getPostsForDashboard();
                break;
            case "comments":
                $entities = (new CommentRepository())->getCommentsForDashboard();
                break;
        }

        return $this->json([
            "success" => $entities !== false,
            "entities" => $entities
        ]);
    }


    /**
     * Update status user
     *
     * @throws Exception
     */
    #[Route(
        "/admin/user/:id/toggle-status",
        "admin_user_toggle_status",
        regexs: ["id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        granted: "ROLE_ADMIN"
    )]
    public function toggleStatusUser(string $id): Response
    {
        /**
         * @var User|false $user
         */
        $user = (new UserRepository())
            ->findByOne(
                ["id" => $id],
                classObject: User::class
            );

        if (!$user) {
            return $this->json(
                [
                "success" => false,
                "message" => "Impossible de modifier le status de cet utilisateur, celui-ci n'a pas été trouvé dans la base de données."
                ]
            );
        }

        if ($user->getId() === $this->user()?->getId()) {
            return $this->json(
                [
                    "success" => false,
                    "message" => "Impossible de modifier le status de votre compte."
                ]
            );
        }

        $status = $user->getStatus();
        $user->setStatus(
            $status === User::STATUS_CODE_REGISTERED_WAITING ||
            $status === User::STATUS_CODE_DEACTIVATED ?
                User::STATUS_CODE_REGISTERED :
                User::STATUS_CODE_DEACTIVATED
        );

        $this->manager->flush($user);

        return $this->json(
            [
                "success" => true,
                "message" => "Le status de l'utilisateur a été ".
                    (
                        $user->getStatus() === User::STATUS_CODE_REGISTERED ?
                        "activé" :
                        "désactivé"
                    ).
                    " avec succès.",
                "action" => "update-status"
            ]
        );
    }


    /**
     * Update status user
     *
     * @throws Exception
     */
    #[Route(
        "/admin/user/:id/toggle-role",
        "admin_user_toggle_role",
        regexs: ["id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        granted: "ROLE_ADMIN"
    )]
    public function toggleRoleUser(string $id): Response
    {
        /**
         * @var User|false $user
         */
        $user = (new UserRepository())
            ->findByOne(
                ["id" => $id],
                classObject: User::class
            );

        if (!$user) {
            return $this->json(
                [
                "success" => false,
                "message" => "Impossible de modifier le role de cet utilisateur, celui-ci n'a pas été trouvé dans la base de données."
                ]
            );
        }

        if ($user->getId() === $this->user()?->getId()) {
            return $this->json(
                [
                    "success" => false,
                    "message" => "Impossible de modifier le role de votre compte."
                ]
            );
        }

        $user->setRole(
            $user->getRole() === "ROLE_ADMIN" ?
                "ROLE_USER" :
                "ROLE_ADMIN"
        );

        $this->manager->flush($user);

        return $this->json(
            [
                "success" => true,
                "message" => "Le rôle de l'utilisateur a été modifié avec succès.",
                "action" => "update-role"
            ]
        );
    }


    /**
     * Update status published post
     *
     * @throws Exception
     */
    #[Route(
        "/admin/post/:id/toggle-published",
        "admin_post_toggle_published",
        regexs: ["id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        granted: "ROLE_ADMIN"
    )]
    public function togglePublishedPost(string $id): Response
    {
        /**
         * @var Post|false $post
         */
        $post = (new PostRepository())
            ->findByOne(
                ["id" => $id],
                classObject: Post::class
            );

        if (!$post) {
            return $this->json(
                [
                "success" => false,
                "message" => "Impossible de modifier le status de publication de ce post, celui-ci n'a pas été trouvé dans la base de données."
                ]
            );
        }

        if ($post->getIsFeatured() && $post->getIsPublished()) {
            return $this->json(
                [
                "success" => false,
                "message" => "Il n'est pas possible de désactiver la publication d'un post une fois qu'il est mis en avant."
                ]
            );
        }

        $post->setIsPublished(!$post->getIsPublished());
        $this->manager->flush($post);

        return $this->json(
            [
                "success" => true,
                "message" => "Le status de publication du post a été modifié avec succès.",
                "action" => "update-published"
            ]
        );
    }


    /**
     * Update status featured post
     *
     * @throws Exception
     */
    #[Route(
        "/admin/post/:id/toggle-featured",
        "admin_post_toggle_featured",
        regexs: ["id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        granted: "ROLE_ADMIN"
    )]
    public function toggleFeaturedPost(string $id): Response
    {
        $postRepo = new PostRepository();

        /**
         * @var Post|false $post
         */
        $post = $postRepo
            ->findByOne(
                ["id" => $id],
                classObject: Post::class
            );

        if (!$post) {
            return $this->json(
                [
                "success" => false,
                "message" => "Impossible de modifier le status de la mise en avant de ce post, celui-ci n'a pas été trouvé dans la base de données."
                ]
            );
        }

        $countPostsIsFeatured = $postRepo->getCountFeaturedPosts();

        if (!$post->getIsFeatured() && $countPostsIsFeatured === 5) {
            return $this->json(
                [
                    "success" => false,
                    "message" => "Le nombre maximum de posts pour la mise en avant a été atteint."
                ]
            );
        }

        if (!$post->getIsPublished()) {
            return $this->json(
                [
                    "success" => false,
                    "message" => "Le post doit être au statut publié pour qu'il soit mis en avant."
                ]
            );
        }

        $post->setIsFeatured(!$post->getIsFeatured());
        $this->manager->flush($post);

        return $this->json(
            [
                "success" => true,
                "message" => "Le status de la mise en avant du post a été modifié avec succès.",
                "action" => "update-featured"
            ]
        );
    }


    /**
     * Update status featured post
     *
     * @throws Exception
     */
    #[Route(
        "/admin/comment/:status/:id",
        "admin_comment_update_status",
        regexs: [
            "status" => "(valid|decline)",
            "id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"
        ],
        granted: "ROLE_ADMIN"
    )]
    public function updateStatusComment(string $status, string $id): Response
    {
        $commentRepo = new CommentRepository();

        /**
         * @var Comment|false $comment
         */
        $comment = $commentRepo
            ->findByOne(
                ["id" => $id],
                classObject: Comment::class
            );

        if (!$comment) {
            return $this->json(
                [
                "success" => false,
                "message" => "Impossible de modifier le status du commentaire, celui-ci n'a pas été trouvé dans la base de données."
                ]
            );
        }

        $comment->setIsValid($status === "valid");
        $comment->setValidByUserId($this->user() ?? "");

        $this->manager->flush($comment);

        return $this->json(
            [
                "success" => true,
                "message" => "Le commentaire a été ".
                    ($comment->getIsValid() ? "validé" : "refusé").
                    " avec succès.",
                "action" => "update-status-comment",
                "updated-details" => [
                    "isValid" => $comment->getIsValid(),
                    "validBy" => $comment->getValidByUserId()?->getPseudo(),
                    "validAt" => $comment->getValidAt()?->format("Y/m/d H:i:s"),
                    "updatedAt" => $comment->getUpdatedAt()?->format("Y/m/d H:i:s")
                ]
            ]
        );
    }


    /**
     * Add post
     *
     * @throws Exception
     */
    #[Route(
        "/admin/post/add",
        "admin_post_add",
        methods: ["POST"],
        granted: "ROLE_ADMIN"
    )]
    public function addPost(): Response
    {
        return $this->postForm();
    }


    /**
     * Edit post
     *
     * @throws Exception
     */
    #[Route(
        "/admin/post/edit/:id",
        "admin_post_edit",
        regexs: ["id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        methods: ["POST"],
        granted: "ROLE_ADMIN"
    )]
    public function editPost(string $id): Response
    {
        return $this->postForm($id);
    }


    /**
     * Edit post
     *
     * @throws Exception
     */
    #[Route(
        "/admin/post/delete/:id",
        "admin_post_edit",
        regexs: ["id" => "(\w){8}((\-){1}(\w){4}){3}(\-){1}(\w){12}"],
        methods: ["DELETE"],
        granted: "ROLE_ADMIN"
    )]
    public function deletePost(string $id): Response
    {
        /**
         * @var Post|false $post
         */
        $post = (new PostRepository())->findByOne([
            "id" => $id
        ], classObject: Post::class);

        if (!$post) {
            return $this->responseHttpNotFound();
        }

        /**
         * @var array<int, Comment> $comments
         */
        $comments = (new CommentRepository())->findBy(
            ["postId" => $id],
            [\PDO::FETCH_CLASS, Comment::class]
        );

        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $this->manager->delete($comment);
            }
        }

        $this->manager->delete($post);

        return $this->json([
            "success" => true,
            "message" => "Le post a été supprimé avec succès."
        ]);

    }


    /**
     * Add or Edit post
     *
     * @throws FormException
     * @throws Exception
     */
    private function postForm(?string $id = null): Response
    {
        $post = new Post();

        if ($id) {
            /**
             * @var Post|false $post
             */
            $post = (new PostRepository())->findByOne([
                "id" => $id
            ], classObject: Post::class);

            if (!$post) {
                return $this->responseHttpNotFound();
            }
        }

        $form = new PostForm($post);
        $form->handleRequest();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->user() && $this->user()->getId()) {
                $post->setUserId($this->user()->getId());
            }

            $this->manager->flush($post);

            $entity = (new PostRepository())->getPostsForDashboard($post->getId());

            return $this->json([
                "success" => true,
                "message" => "Le post a été ". ($id === null ? "ajouté" : "modifié") ." avec succès.",
                "post" => $entity,
                "formType" => $id === null ? "created-post" : "updated-post"
            ]);

        }

        return $this->json([
            "success" => false,
            "errors" => $form->getErrors()
        ]);
    }


}
