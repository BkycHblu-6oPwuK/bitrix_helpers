<?php

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Type\DateTime;
use Beeralex\Core\Config\Config;

/**
 * @todo перенести бы c m1 функционалльную часть соединив с этой
 */
class BeeralexProfileController extends \Bitrix\Main\Engine\Controller
{
    public function configureActions()
    {
        return [
            'getPersonal' => [
                'prefilters' => [
                    new Csrf(),
                ],
            ],
            'getOrders' => [
                'prefilters' => [
                    new Csrf(),
                ],
            ],
            'getDressing' => [
                'prefilters' => [
                    new Csrf(),
                ],
            ],
            'getQuestions' => [
                'prefilters' => [
                    new Csrf(),
                ],
            ],
            'updateField' => [
                'prefilters' => [
                    new Csrf(),
                ],
            ],
            /*
            'updateEmail' => [
                'prefilters' => [
                    new Csrf(),
                    new Authentication()
                ],
            ],
            'updatePassword' => [
                'prefilters' => [
                    new Csrf(),
                    new Authentication()
                ],
            ],
            'updateProfile' => [
                'prefilters' => [
                    new Csrf(),
                    new Authentication()
                ],
            ],*/
        ];
    }

    public function getPersonalAction()
    {
        try {
            // $notifications = (new \App\Notification\Services\NotificationPreferenceService)->getAll(User::current()->getId());
            // $result = [
            //     'personal' => (new \App\User\Profile\PersonalBuilder($notifications))->build(),
            //     'success' => true
            // ];
            // return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    /**
     * Возвращает список заказов с фильтрацией и пагинацией.
     *
     * Ожидаемые параметры запроса:
     * - filter (string, JSON) — фильтр в виде JSON-объекта, поддерживает ключи:
     *     - isPaid (string 'Y'|'N') — оплачен ли заказ
     *     - date (array) — диапазон дат, формат:
     *         - from (string 'Y-m-d')
     *         - to (string 'Y-m-d')
     *     - query (string) — поисковая строка по товарам
     * - page (int) — номер страницы для пагинации
     * - fullLoad (int 0|1) - грузить ли все (+ склады и кол-во заказов) помимо заказов и пагинации
     *
     * Пример filter:
     * {
     *     "isPaid": "Y",
     *     "date": { "from": "2024-01-01", "to": "2024-06-01" },
     *     "query": "масло"
     * }
     *
     * Все параметры считываются из request.
     */
    public function getOrdersAction()
    {
        // try {
        //     $result = (new \App\User\Profile\OrdersBuilder(new \App\Catalog\Repository\OrderRepository))->build();
        //     $result['success'] = true;
        //     return $result;
        // } catch (\Exception $e) {
        //     return [
        //         'success' => false,
        //     ];
        // }
    }

    public function getDressingAction()
    {
        // try {
        //     $result = (new \App\User\Profile\DressingBuilder(new \App\Catalog\Repository\OrderRepository))->build();
        //     $result['success'] = true;
        //     return $result;
        // } catch (\Exception $e) {
        //     return [
        //         'success' => false,
        //     ];
        // }
    }

    public function getQuestionsAction()
    {
        // try {
        //     $result = [
        //         'questions' => (new \App\User\Profile\QuestionsBuilder(new \App\Iblock\Repository\QuestionRepository))->build(),
        //         'success' => true
        //     ];
        //     return $result;
        // } catch (\Exception $e) {
        //     return [
        //         'success' => false,
        //     ];
        // }
    }

    public function updateFieldAction($field, $value)
    {
        // try {
        //     $realField = $this->getField($field);
        //     if (!$realField) {
        //         throw new \InvalidArgumentException('Неизвестное поле для обновления');
        //     }
        //     $user = User::current();
        //     $this->validateField($field, $value);
        //     $service = new ProfileService;
        //     $service->updateProfile($user, [
        //         $realField => $value
        //     ]);
        //     if ($value instanceof DateTime) {
        //         $value = $value->format(Config::getInstance()->dateFormatSite);
        //     }
        //     $result = [
        //         'success' => true,
        //         'value' => $value,
        //     ];
        //     return $result;
        // } catch (UserNotFoundException $e) {
        //     return [
        //         'success' => false,
        //         'error' => 'Невозможно выполнить операцию. Авторизуйтесь на сайте.'
        //     ];
        // } catch (\InvalidArgumentException $e) {
        //     return [
        //         'success' => false,
        //         'error' => $e->getMessage()
        //     ];
        // } catch (\Exception $e) {
        //     return [
        //         'success' => false,
        //     ];
        // }
    }

    private function getField(string $field): ?string
    {
        $fieldMap = [
            'name' => 'NAME',
            'email' => 'EMAIL',
            'phone' => 'PERSONAL_PHONE',
            'birthday' => 'PERSONAL_BIRTHDAY',
            'gender' => 'PERSONAL_GENDER',
        ];
        return $fieldMap[$field];
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validateField(string $field, string &$value): void
    {
        // $validator = new UserValidator;
        // $isValid = match ($field) {
        //     'phone' => $validator->validatePhone(new Phone($value)),
        //     'email' => $validator->validateEmail($value),
        //     'birthday' => $validator->validateBirthday($value),
        //     'gender' => $validator->validateGender($value),
        //     'name' => $validator->validateName($value),
        //     default => null
        // };
        // if ($isValid === null) {
        //     throw new \InvalidArgumentException('Неизвестное поле для обновления');
        // } else if (!$isValid) {
        //     $error = $validator->getErrors()[0];
        //     throw new \InvalidArgumentException($error ?? 'Ошибка валидации');
        // }
    }

    /*
    public function updateEmailAction($email, $password)
    {
        try {
            $this->handleValidatePassword($password);
            $user = User::current();
            $service = new ProfileService;
            $service->updateProfile($user, [
                'EMAIL' => $email
            ]);
            $result = [
                'success' => true,
            ];
            return $result;
        } catch (UserNotFoundException $e) {
            return [
                'success' => false,
                'error' => 'Невозможно выполнить операцию. Авторизуйтесь на сайте.'
            ];
        } catch (\InvalidArgumentException | IncorrectOldPasswordException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    public function updatePasswordAction($password, $oldPassword)
    {
        try {
            $user = User::current();
            $service = new ProfileService;
            $service->changePasswordByOldPassword($user, $password, $oldPassword);
            $result = [
                'success' => true,
            ];
            return $result;
        } catch (UserNotFoundException $e) {
            return [
                'success' => false,
                'error' => 'Невозможно выполнить операцию. Авторизуйтесь на сайте.'
            ];
        } catch (\InvalidArgumentException | IncorrectOldPasswordException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    public function updateProfileAction($name, $phone)
    {
        try {
            $service = new ProfileService;
            $nameParts = explode(" ", $name, 2);
            $service->updateProfile(User::current(), [
                'NAME' => $nameParts[0],
                'LAST_NAME' => $nameParts[1] ?? "",
                'PERSONAL_PHONE' => $phone,
            ]);
            $result = [
                'success' => true,
            ];
            return $result;
        } catch (UserNotFoundException $e) {
            return [
                'success' => false,
                'error' => 'Невозможно выполнить операцию. Авторизуйтесь на сайте.'
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    private function handleValidatePassword($password)
    {
        $user = User::current();
        if (!(new PasswordValidator())->validatePassword($password, $user->getPassword())) {
            throw new IncorrectOldPasswordException('Пароль введен неверно');
        }
    }*/
}
