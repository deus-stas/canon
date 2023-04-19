<?php


namespace ALS\Project;


class User {
    public static function getData(): array {
        global $USER;

        return [
            'isAuthorized' => $USER->IsAuthorized(),
            'isAdmin' => self::isAdmin(),
            'login' => $USER->GetLogin(),
        ];
    }


    public static function isAdmin(): bool {
        global $USER;
        return ($USER && $USER->IsAdmin()) || strpos($_SERVER['HTTP_ORIGIN'], '//localhost:');
    }

    public static function getAuthorizedId(): ?int {
        global $USER;

        $userId = (int)$USER->GetID();

        return $userId === 0 ? null : $userId;
    }
}
