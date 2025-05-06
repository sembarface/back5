<?php
session_start();
require 'db.php';

// Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Валидация данных
    $validation_rules = [
        'name' => '/^[a-zA-Zа-яА-ЯёЁ\s]{1,150}$/u',
        'phone' => '/^\+?\d[\d\s\-\(\)]{6,}\d$/',
        'email' => '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i',
        'birthdate' => '/^\d{4}-\d{2}-\d{2}$/',
        'gender' => '/^(male|female|other)$/',
        'languages' => '/^.+$/',
        'bio' => '/^[\s\S]{10,2000}$/',
        'contract_accepted' => '/^1$/'
    ];

    $errors = false;
    foreach ($validation_rules as $field => $pattern) {
        $value = $_POST[$field] ?? '';
        
        if ($field === 'languages') {
            $value = implode(',', $_POST['languages'] ?? []);
        } elseif ($field === 'contract_accepted') {
            $value = isset($_POST['contract_accepted']) ? '1' : '0';
        }
        
        if (!preg_match($pattern, $value)) {
            setcookie($field.'_error', '1', time() + 24 * 60 * 60);
            $errors = true;
        } else {
            setcookie($field.'_value', $value, time() + 30 * 24 * 60 * 60);
        }
    }

    // Дополнительная проверка даты
    if (empty($errors['birthdate'])) {
        $birthdate = DateTime::createFromFormat('Y-m-d', $_POST['birthdate']);
        if ($birthdate > new DateTime()) {
            setcookie('birthdate_error', '1', time() + 24 * 60 * 60);
            $errors = true;
        }
    }

    if ($errors) {
        header('Location: index.php');
        exit();
    }

    // Подготовка данных для сохранения
    $data = [
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'birthdate' => $_POST['birthdate'],
        'gender' => $_POST['gender'],
        'languages' => implode(',', $_POST['languages'] ?? []),
        'bio' => $_POST['bio'],
        'contract_accepted' => isset($_POST['contract_accepted']) ? 1 : 0
    ];

    // Если пользователь авторизован - обновляем данные
    if (!empty($_SESSION['login'])) {
        try {
            $stmt = $pdo->prepare("UPDATE applications SET 
                name = :name, phone = :phone, email = :email, 
                birthdate = :birthdate, gender = :gender, 
                languages = :languages, bio = :bio, 
                contract_accepted = :contract_accepted 
                WHERE login = :login");
            
            $data['login'] = $_SESSION['login'];
            $stmt->execute($data);
            
            setcookie('save', '1');
        } catch (PDOException $e) {
            die("Ошибка обновления данных: " . $e->getMessage());
        }
    } 
    // Иначе создаем нового пользователя
    else {
        // Генерация логина и пароля
        $login = uniqid('user_');
        $pass = bin2hex(random_bytes(4));
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO applications 
                (login, pass_hash, name, phone, email, birthdate, gender, languages, bio, contract_accepted) 
                VALUES (:login, :pass_hash, :name, :phone, :email, :birthdate, :gender, :languages, :bio, :contract_accepted)");
            
            $data['login'] = $login;
            $data['pass_hash'] = $pass_hash;
            $stmt->execute($data);
            
            // Сохраняем логин и пароль в куки для показа пользователю
            setcookie('login', $login, time() + 24 * 60 * 60);
            setcookie('pass', $pass, time() + 24 * 60 * 60);
            setcookie('save', '1');
        } catch (PDOException $e) {
            die("Ошибка сохранения данных: " . $e->getMessage());
        }
    }

    header('Location: index.php');
    exit();
}
?>
