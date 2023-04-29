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


use App\Factory\Router\Response;
use App\Factory\Router\Route;
use App\Factory\Router\RouterException;
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


}
