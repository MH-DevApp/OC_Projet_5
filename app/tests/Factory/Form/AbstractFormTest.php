<?php

/**
 * AbstractFormTest file
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

namespace tests\Factory\Form;

use App\Entity\User;
use App\Factory\Form\AbstractForm;
use App\Factory\Form\FormException;
use App\Factory\Utils\Csrf\Csrf;
use App\Factory\Utils\DotEnv\DotEnv;
use App\Factory\Utils\DotEnv\DotEnvException;
use App\Service\Container\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Test AbstractForm cases
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
#[CoversClass(AbstractForm::class)]
class AbstractFormTest extends TestCase
{


    /**
     * Test should be to throw form exception : missed CSRF Token.
     *
     * @return void
     *
     * @throws FormException
     */
    #[Test]
    #[TestDox("should be to throw form exception : missed CSRF Token")]
    public function itThrowFormExceptionCSRFTokenMissed(): void
    {
        $this->expectException(FormException::class);
        $this->expectExceptionMessage(
            "The CSRF token is not set in the construct method. 
                \$this->csrfKey = \"string phrase or word\"."
        );

        $user = new User();
        $form = new FormMissCsrfKeyTest($user);
    }


    /**
     * Test should be to throw form exception : need array or numeric
     * for sanitize option in addField method.
     *
     * @return void
     *
     * @throws FormException
     */
    #[Test]
    #[TestDox("should be to throw form exception : need array or numeric for sanitize option in addField method")]
    public function itThrowFormExceptionArrayOrNumSanitizeOption(): void
    {
        $this->expectException(FormException::class);
        $this->expectExceptionMessage(
            "You need set the keys \"filter\" and \"flags\" 
                    in the array sanitize options or set with a global variable filter."
        );

        $user = new User();
        $form = new FormArrayOrNumSanitizeOptionTest($user);
    }


    /**
     * Test should be to throw form exception : callable for validation
     * option in addField method.
     *
     * @return void
     *
     * @throws FormException
     */
    #[Test]
    #[TestDox("should be to throw form exception : callable for validation option in addField method")]
    public function itThrowFormExceptionCallableValidationOption(): void
    {
        $this->expectException(FormException::class);
        $this->expectExceptionMessage(
            "The validation option must be callable."
        );

        $user = new User();
        $form = new FormCallableValidationOptionTest($user);
    }


    /**
     * Test should be to submitted form true.
     *
     * @return void
     *
     * @throws FormException
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to submitted form true")]
    public function itSubmittedFormTrue(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_ENV["TEST_PATH"] = "_test";
        $_POST["_csrf"] = "Test";
        (new DotEnv())->load();
        Container::loadServices();

        $user = new User();
        $form = new FormFullTest($user);

        $form->handleRequest();

        $this->assertTrue($form->isSubmitted());
    }


    /**
     * Test should be to submitted form false.
     *
     * @return void
     *
     * @throws FormException
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to submitted form false")]
    public function itSubmittedFormFalse(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_ENV["TEST_PATH"] = "_test";
        (new DotEnv())->load();
        Container::loadServices();

        $user = new User();
        $form = new FormFullTest($user);

        $form->handleRequest();

        $this->assertFalse($form->isSubmitted());
    }


    /**
     * Test should be to valid form false.
     *
     * @return void
     *
     * @throws FormException
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to valid form false")]
    public function itValidFormFalse(): void
    {
        (new DotEnv())->load();
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_ENV["TEST_PATH"] = "_test";
        $_POST["_csrf"] = "Test";
        $_POST["lastname"] = "";
        $_POST["firstname"] = "";
        $_POST["pseudo"] = "";
        $_POST["email"] = "";
        $_POST["password"] = "";
        $_POST["confirmPassword"] = "";
        Container::loadServices();

        $user = new User();
        $form = new FormFullTest($user);

        $form->handleRequest();

        $this->assertFalse($form->isValid());
        $this->assertEquals("Le token CSRF n'est pas valide.", $form->getError("global"));
        $this->assertEquals("Ce champ est requis.", $form->getError("lastname"));
        $this->assertEquals("Ce champ est requis.", $form->getError("firstname"));
        $this->assertEquals("Ce champ est requis.", $form->getError("pseudo"));
        $this->assertEquals("L'email n'est pas valide.", $form->getError("email"));
        $this->assertEquals("Ce champ est requis.", $form->getError("password"));
    }


    /**
     * Test should be to valid form true.
     *
     * @return void
     *
     * @throws FormException
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to valid form true")]
    public function itValidFormTrue(): void
    {
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        (new DotEnv())->load();
        Container::loadServices();
        $csrf = Csrf::generateTokenCsrf("test");
        $_POST["_csrf"] = $csrf;
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_ENV["TEST_PATH"] = "_test";
        $_POST["lastname"] = "Test";
        $_POST["firstname"] = "Test";
        $_POST["pseudo"] = "Test";
        $_POST["email"] = "test@test.com";
        $_POST["password"] = "password";
        $_POST["confirmPassword"] = "password";
        Container::loadServices();

        $user = new User();
        $form = new FormFullTest($user);

        $form = $form->handleRequest();

        $this->assertTrue($form->isValid());
        $this->assertEquals("Test", $user->getLastname());
        $this->assertEquals("Test", $user->getFirstname());
        $this->assertEquals("Test", $user->getPseudo());
        $this->assertEquals("test@test.com", $user->getEmail());
        $this->assertEquals("password", $user->getPassword());
    }


    /**
     * Test should be to get errors of the form.
     *
     * @return void
     *
     * @throws FormException
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to get all errors of the form")]
    public function itGetAllErrorsForm(): void
    {
        (new DotEnv())->load();
        Container::loadServices();

        $user = new User();
        $form = new FormFullTest($user);

        $form->handleRequest();

        $this->assertIsArray($form->getErrors());
    }


    /**
     * Test should be to get an error field of the form.
     *
     * @return void
     *
     * @throws FormException
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to get an error field of the form")]
    public function itGetErrorFieldForm(): void
    {
        (new DotEnv())->load();
        Container::loadServices();

        $user = new User();
        $form = new FormFullTest($user);

        $form->handleRequest();

        $this->assertNotNull($form->getError("lastname"));
        $this->assertNull($form->getError("test"));
    }


    /**
     * Test should be to get all data values, data
     * field value, and null value of the form.
     *
     * @return void
     *
     * @throws FormException
     *
     * @throws DotEnvException
     * @throws ReflectionException
     */
    #[Test]
    #[TestDox("should be to get all data values and data field value of the form")]
    public function itGetAllDataAndDataFieldAndNullForm(): void
    {
        $_SERVER["REMOTE_ADDR"] = "Test";
        $_SERVER["HTTP_USER_AGENT"] = "Test";
        (new DotEnv())->load();
        Container::loadServices();
        $csrf = Csrf::generateTokenCsrf("test");
        $_POST["_csrf"] = $csrf;
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_ENV["TEST_PATH"] = "_test";
        $_POST["lastname"] = "Test";
        $_POST["firstname"] = "Test";
        $_POST["pseudo"] = "Test";
        $_POST["email"] = "test@test.com";
        $_POST["password"] = "password";
        $_POST["confirmPassword"] = "password";
        Container::loadServices();

        $user = new User();
        $form = new FormFullTest($user);

        $form->handleRequest();

        /**
         * @var array<string, string|int> $allData
         */
        $allData = $form->getData();

        $this->assertEquals($csrf, $allData["_csrf"]);
        $this->assertEquals("Test", $allData["lastname"]);
        $this->assertEquals("Test", $allData["firstname"]);
        $this->assertEquals("Test", $allData["pseudo"]);
        $this->assertEquals("test@test.com", $allData["email"]);
        $this->assertEquals("password", $allData["password"]);
        $this->assertEquals("password", $allData["confirmPassword"]);

        /**
         * @var string $dataField
         */
        $dataField = $form->getData("lastname");
        $this->assertEquals("Test", $dataField);

        /**
         * @var null $dataNull
         */
        $dataNull = $form->getData("Test");
        $this->assertNull($dataNull);
    }
}


/**
 * FormFullTest class.
 * Class for test AbstractForm
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class FormFullTest extends AbstractForm
{
    protected string $csrfKey = "test";
    protected ?object $entity = null;


    /**
     * Constructor
     *
     * @param object|null $entity
     * @throws FormException
     */
    public function __construct(?object $entity = null)
    {
        $this->entity = $entity;
        parent::__construct($entity);
    }


    final protected function builder(): void
    {
        parent::builder();
        $this
            ->addField(name: "lastname", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function (string $value): bool {

                    if (empty($value)) {
                        $this->setError(
                            "lastname",
                            self::ERROR_REQUIRED
                        );
                        return false;
                    }

                    if (strlen($value) < 3 || strlen($value) > 50) {
                        $this->setError(
                            "lastname",
                            sprintf(self::ERROR_LENGTH, 3, 50)
                        );
                        return false;
                    }

                    return true;
                }
            ])
            ->addField(name: "firstname", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function (string $value): bool {

                    if (empty($value)) {
                        $this->setError(
                            "firstname",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) < 3 || strlen($value) > 50) {
                        $this->setError(
                            "firstname",
                            sprintf(self::ERROR_LENGTH, 3, 50)
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField(name: "pseudo", options: [
                "sanitize" => FILTER_SANITIZE_SPECIAL_CHARS,
                "validation" => function (string $value): bool {

                    if (empty($value)) {
                        $this->setError(
                            "pseudo",
                            self::ERROR_REQUIRED
                        );

                        return false;
                    }

                    if (strlen($value) < 4) {
                        $this->setError(
                            "pseudo",
                            sprintf(self::ERROR_MIN_LENGTH, 4)
                        );

                        return false;
                    }

                    if (strlen($value) > 20) {
                        $this->setError(
                            "pseudo",
                            sprintf(self::ERROR_MAX_LENGTH, 20)
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField(name: "email", options: [
                "sanitize" => FILTER_SANITIZE_EMAIL,
                "validation" => function (string $value): bool {

                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->setError(
                            "email",
                            sprintf(self::ERROR_BAD_FORMAT, "L'email")
                        );

                        return false;
                    }

                    return true;
                }
            ])
            ->addField(name: "password", options: [
                "validation" => function (string $value): bool {

                    if (empty($value)) {
                        $this->setError(
                            "password",
                            self::ERROR_REQUIRED
                        );
                        return false;
                    }

                    if (strlen($value) < 6 || strlen($value) > 20) {
                        $this->setError(
                            "password",
                            sprintf(self::ERROR_LENGTH, 6, 20)
                        );
                        return false;
                    }

                    if ((
                            !isset($this->fields["data"]["password"]) ||
                            !isset($this->fields["data"]["confirmPassword"])
                        ) ||
                        $this->fields["data"]["password"] !== $this->fields["data"]["confirmPassword"]
                    ) {
                        $this->setError(
                            "password",
                            self::ERROR_CONFIRM
                        );
                        return false;
                    }

                    return true;
                }
            ])
            ->addField(name: "confirmPassword", options: [
                "mapped" => false
            ])
        ;
    }
}


/**
 * FormMissCsrfKeyTest class.
 * For test miss Crsf key.
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class FormMissCsrfKeyTest extends AbstractForm
{


    /**
     * Constructor
     *
     * @param object|null $entity
     * @throws FormException
     */
    public function __construct(?object $entity = null)
    {
        parent::__construct($entity);
    }


    final protected function builder(): void
    {
        parent::builder();
    }
}


/**
 * FormArrayOrNumSanitizeOptionTest class.
 * For test bad format to Array or Num sanitize Option
 * in addField method.
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class FormArrayOrNumSanitizeOptionTest extends AbstractForm
{
    protected string $csrfKey = "test";


    /**
     * Constructor
     *
     * @param object|null $entity
     * @throws FormException
     */
    public function __construct(?object $entity = null)
    {
        parent::__construct($entity);
    }


    final protected function builder(): void
    {
        parent::builder();
        $this->addField(name: "Test", options: [
            "sanitize" => "Test"
        ]);
    }
}


/**
 * FormCallableValidationOptionTest class.
 * For test bad format to Callable for validation option in
 * addField method.
 *
 * PHP Version 8.1
 *
 * @category PHP
 * @package  OC_P5_BLOG
 * @author   Mehdi Haddou <mehdih.devapp@gmail.com>
 * @license  MIT Licence
 * @link     https://p5.mehdi-haddou.fr
 */
final class FormCallableValidationOptionTest extends AbstractForm
{
    protected string $csrfKey = "test";


    /**
     * Constructor
     *
     * @param object|null $entity
     * @throws FormException
     */
    public function __construct(?object $entity = null)
    {
        parent::__construct($entity);
    }


    final protected function builder(): void
    {
        parent::builder();
        $this->addField(name: "Test", options: [
            "validation" => "Test"
        ]);
    }
}
