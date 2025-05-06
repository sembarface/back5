<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $validation_rules = [
        'name' => [
            'pattern' => '/^[a-zA-Zа-яА-ЯёЁ\s]{1,150}$/u',
            'message' => 'ФИО должно содержать только буквы и пробелы (макс. 150 символов)'
        ],
        'phone' => [
            'pattern' => '/^\+?\d[\d\s\-\(\)]{6,}\d$/',
            'message' => 'Неверный формат телефона'
        ],
        'email' => [
            'pattern' => '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i',
            'message' => 'Введите корректный email'
        ],
        'birthdate' => [
            'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
            'message' => 'Дата должна быть в формате ГГГГ-ММ-ДД'
        ],
        'gender' => [
            'pattern' => '/^(male|female|other)$/',
            'message' => 'Выберите пол из предложенных вариантов'
        ],
        'languages' => [
            'pattern' => '/^.+$/',
            'message' => 'Выберите хотя бы один язык программирования'
        ],
        'bio' => [
            'pattern' => '/^[\s\S]{10,2000}$/',
            'message' => 'Биография должна содержать от 10 до 2000 символов'
        ],
        'contract_accepted' => [
            'pattern' => '/^1$/',
            'message' => 'Необходимо принять условия контракта'
        ]
    ];

    $errors = [];
    $data = [];
    
    foreach ($validation_rules as $field => $rule) {
        $value = $_POST[$field] ?? '';
        
        if ($field === 'contract_accepted') {
            $value = isset($_POST['contract_accepted']) ? '1' : '0';
        }
        
        // Особый случай для languages - проверяем, что массив не пустой
        if ($field === 'languages') {
            if (empty($_POST['languages']) {
                $errors[$field] = $rule['message'];
                setcookie($field.'_error', $rule['message'], time() + 24 * 60 * 60);
                continue;
            }
            $data[$field] = $_POST['languages']; // Сохраняем как массив
            continue;
        }
        
        $data[$field] = $value;
        
        if (!preg_match($rule['pattern'], $value)) {
            $errors[$field] = $rule['message'];
            setcookie($field.'_error', $rule['message'], time() + 24 * 60 * 60);
        } else {
            setcookie($field.'_value', $value, time() + 30 * 24 * 60 * 60);
        }
    }

    if (!empty($errors)) {
        setcookie('form_errors', json_encode($errors), time() + 24 * 60 * 60, '/');
        header('Location: index.php');
        exit();
    }

    try {
        if (!empty($_SESSION['login'])) {
            // Обновление существующей записи
            $stmt = $pdo->prepare("UPDATE applications SET 
                name = :name, phone = :phone, email = :email, 
                birthdate = :birthdate, gender = :gender, 
                bio = :bio, contract_accepted = :contract_accepted 
                WHERE id = :id");
            
            $data['id'] = $_SESSION['uid'];
            $stmt->execute($data);
            
            // Удаляем старые языки
            $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")
                ->execute([$_SESSION['uid']]);
        } else {
            // Создание новой записи
            $login = uniqid('user_');
            $pass = bin2hex(random_bytes(4));
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO applications 
                (name, phone, email, birthdate, gender, bio, contract_accepted, login, pass_hash) 
                VALUES (:name, :phone, :email, :birthdate, :gender, :bio, :contract_accepted, :login, :pass_hash)");
            
            $data['login'] = $login;
            $data['pass_hash'] = $pass_hash;
            $stmt->execute($data);
            $app_id = $pdo->lastInsertId();
            
            setcookie('login', $login, time() + 24 * 60 * 60);
            setcookie('pass', $pass, time() + 24 * 60 * 60);
        }
        
        // Добавляем выбранные языки
        $app_id = $_SESSION['uid'] ?? $app_id;
        $lang_stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) 
            SELECT ?, id FROM languages WHERE name = ?");
        
        foreach ($data['languages'] as $lang) {
            $lang_stmt->execute([$app_id, $lang]);
        }
        
        setcookie('save', '1', time() + 24 * 60 * 60);
        header('Location: index.php?success=1');
        exit();
    } catch (PDOException $e) {
        die("Ошибка сохранения данных: " . $e->getMessage());
    }
}
?>
