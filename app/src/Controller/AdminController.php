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
use App\Factory\Manager\Manager;
use App\Factory\Router\Response;
use App\Factory\Router\Route;
use App\Factory\Router\RouterException;
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

        $entities = [];

        switch ($page) {
            case "users":
                $entities = (new UserRepository())->getUsersForDashboard();
                break;
            case "posts":
            case "comments":
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


}
