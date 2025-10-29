<?

use Bitrix\Main\Context;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Web\Uri;
use App\Notification\Contracts\SmsCodeContract;
use App\Main\PageHelper;
use Beeralex\Oauth2\Repository\UserRepository;
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Exceptions\IncorrectOldPasswordException;
use Beeralex\User\Exceptions\UserNotFoundException;
use Beeralex\User\Phone;
use Beeralex\User\UserBuilder;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexAuthController extends \Bitrix\Main\Engine\Controller
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'authorize' => [
                'prefilters' => [
                    new Csrf(),
                ],
                'postfilters' => []
            ],
            'register' => [
                'prefilters' => [
                    new Csrf(),
                ],
                'postfilters' => []
            ],
            'restorePassword' => [
                'prefilters' => [
                    new Csrf(),
                ],
                'postfilters' => []
            ],
            'sendCode' => [
                'prefilters' => [
                    new Csrf(),
                ],
                'postfilters' => []
            ],
            'checkCode' => [
                'prefilters' => [
                    new Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }


    /**
     * Запрос на смену пароля
     *
     * @param $email
     *
     * @return AjaxResponse
     */
    public function restorePasswordAction($email): array
    {
        try {
            (new AuthService())->restorePassword($email);
            return ['success' => true];
        } catch (UserNotFoundException $e) {
            return [
                'success' => false,
                'error' => 'Пользователь с указанным email не найден'
            ];
        }
    }


    /**
     * Регистрация нового пользователя по email
     *
     * @param $firstName
     * @param $email
     * @param $phoneNumber
     * @param $password
     *
     * @return array
     */
    public function registerAction(
        $firstName,
        $email,
        $phoneNumber,
        $password
    ): array {
        try {
            $phone = Phone::fromString($phoneNumber);

            $user = (new UserBuilder())
                ->setEmail(trim($email))
                ->setName(trim($firstName))
                ->setPhone($phone)
                ->setPassword($password)
                ->build();

            // $userValidator = new UserValidator();
            // if (!$userValidator->validateUser($user, true)) {
            //     return [
            //         'success' => false,
            //         'errors' => array_map(fn($errors) => implode(' ', (array)$errors), $userValidator->getErrors())
            //     ];
            // }

            (new AuthService())->register($user);

            return [
                'success' => true,
                'url' => $this->getBackUrl() ?? PageHelper::getProfilePageUrl('?success_reg=y')
            ];
        } catch (\Exception $e) {
        }

        return [
            'success' => false,
        ];
    }

    /**
     * Авторизация пользователя по email
     *
     * @param $email
     * @param $password
     *
     * @return array [
     *
     */
    public function authorizeAction($email, $password): array
    {
        try {
            (new AuthService())->authorize($email, $password);
        } catch (UserNotFoundException | IncorrectOldPasswordException $e) {
            return [
                'success' => false,
                'error' => 'Неверный логин или пароль'
            ];
        }

        // в случае успеха редиректим пользователя или обновляем страницу
        return [
            'success' => true,
            'url' => $this->getBackUrl() ?? $this->getHttpRefererRedirectUrl()
        ];
    }

    public function sendCodeAction($phoneNumber)
    {
        try {
            $phone = Phone::fromString($phoneNumber);
            // $service = ServiceLocator::getInstance()->get(SmsCodeContract::class);
            // $service->sendCode($phone);
            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    public function checkCodeAction($phoneNumber, $code)
    {
        try {
            $phone = new Phone($phoneNumber);
            // $service = ServiceLocator::getInstance()->get(SmsCodeContract::class);
            // $result = $service->checkCode($phone, (int)$code);
            // if ($result) {
            //     $user = (new UserRepository())->getByPhone($phone);
            //     if (!$user) {
            //         $user = $this->registrationByPhone($phone);
            //     } else {
            //         (new AuthService)->authorizeByUserId($user->getId());
            //     }
            // }
            // return [
            //     'success' => true,
            //     'isVerified' => $result,
            //     'url' => $this->getBackUrl() ?? $this->getHttpRefererRedirectUrl()
            // ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Код устарел, запросите новый.'
            ];
        }
    }

    // private function registrationByPhone(Phone $phone): User
    // {
    //     $user = (new UserBuilder())
    //         ->setPhone($phone)
    //         ->build();
    //     (new AuthService())->register($user);
    //     return $user;
    // }


    /**
     * @return string|null URL указанный для редиректа в запросе
     */
    private function getBackUrl(): ?string
    {
        return Context::getCurrent()->getRequest()->get('backUrl');
    }


    /**
     * @return string URL текущей страницы с убранными GET параметрами связанными с авторизацией
     */
    private function getHttpRefererRedirectUrl(): string
    {
        // убираем из Url битровые параметры для авторизации/регистрации
        return (new Uri($_SERVER['HTTP_REFERER']))
            ->deleteParams([
                'login',
                'logout',
                'register',
                'forgot_password',
                'change_password'
            ])
            ->addParams([
                "success_auth" => "y"
            ])
            ->getUri();
    }
}
