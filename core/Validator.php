<?php

final class Validator
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field => $label) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = "{$label} es obligatorio.";
            }
        }
        return $errors;
    }

    public static function email(string $email): bool { return (bool) filter_var($email, FILTER_VALIDATE_EMAIL); }
}
