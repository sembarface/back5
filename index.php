<?php
session_start();
require 'db.php';

$messages = [];
$errors = [];
$values = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = 'Спасибо, результаты сохранены.';
        
        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                htmlspecialchars($_COOKIE['login']),
                htmlspecialchars($_COOKIE['pass'])
            );
        }
    }

    $field_names = ['name', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'contract_accepted'];
    foreach ($field_names as $field) {
        $errors[$field] = !empty($_COOKIE[$field.'_error']) ? $_COOKIE[$field.'_error'] : '';
        if (!empty($errors[$field])) {
            setcookie($field.'_error', '', time() - 3600);
        }
        $values[$field] = empty($_COOKIE[$field.'_value']) ? '' : $_COOKIE[$field.'_value'];
    }

    if (!empty($_SESSION['login'])) {
        try {
            $stmt = $pdo->prepare("SELECT a.*, GROUP_CONCAT(l.name) as languages 
                FROM applications a
                LEFT JOIN application_languages al ON a.id = al.application_id
                LEFT JOIN languages l ON al.language_id = l.id
                WHERE a.login = ? 
                GROUP BY a.id");
            $stmt->execute([$_SESSION['login']]);
            $user_data = $stmt->fetch();
            
            if ($user_data) {
                $values = array_merge($values, $user_data);
                $values['languages'] = $user_data['languages'] ? explode(',', $user_data['languages']) : [];
            }
        } catch (PDOException $e) {
            $messages[] = '<div class="alert alert-danger">Ошибка загрузки данных: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script
      src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>САМЫЙ КРУТОЙ В МИРЕ САЙТ</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <style>
        .error-field {
            border: 1px solid #dc3545 !important;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        .error-list {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body class="d-flex flex-column align-items-center">
    <header class="container-fluid">
        <div class="row row-cols-1 row-cols-md-2 justify-content-center justify-content-md-between">
            <div class="header-case m-0 ms-md-1 col-md-auto d-flex align-items-center justify-content-center m-3">
                <img class="logo mx-3 m-mr-2" src="kotyara.jpg" alt="Котяра">
                <div class="nazvanie p-3">
                    <h3>САМЫЙ КРУТОЙ В МИРЕ САЙТ</h3>
                </div>
            </div>
            <nav class="menu mt-1 mt-md-0 p-2 col-auto d-flex flex-column flex-md-row align-items-center ">
                <div class="move mx-2"><a href ="#hiper">Список гиперссылок </a></div>
                <div class="move mx-2"><a href ="#tabl">Таблица </a></div>
                <div class="move mx-2"><a href ="#forma">Форма</a></div>
            </nav>
        </div>
    </header>
    <div class="content d-flex flex-column">
        <div class="hiper m-2 p-2 m-md-3" id="hiper">
            <ul>
                <li><a href="http://www.kubsu.ru/" title="Официальный сайт Кубанского государственного университета">КубГУ</a></li>
                <li><a href="https://www.kubsu.ru/" title="Официальный сайт Кубанского государственного университета">КубГУ https</a></li>
                <li><a href="https://en.wikipedia.org/wiki/Tim_Berners-Lee">
                    <img src="kotyara.jpg" alt="Котяра" height="100">
                </a></li>
                <li><a href="dva.html">ВНУТРЕННЯ ССЫЛКА</a></li>
                <li><a href="#important">Ссылка на важный фрагмент текущей страницы</a></li>
                <li><a href="dva.html?ip=12345&pp=54321&op=228">Ссылка с 3 параметрами url</a></li>
                <li><a href="dva.html?id=666">Ссылка с параметром id</a></li>
                <li><a href="./dva.html">Относительная ссылка на страницу в текущем каталоге</a></li>
                <li><a href="./about/tri.html">Страница в каталоге about</a></li>
                <li>
                    <p>
                        Да, <a href="https://en.wikipedia.org/wiki/Wolf">волк</a> слабее льва и тигра,
                        но в цирке <a href="https://en.wikipedia.org/wiki/Wolf">волк</a> не выступает
                    </p>
                </li>
                <li><a href="https://en.wikipedia.org/wiki/Capybara#Description">Физиологические параметры капибары</a></li>
                <li>
                    <span>Ссылки из прямоугольных и круглых областей</span><br>
                    <map name="map0">
                        <area shape="rect" alt="Прямоугольная область 1" coords="0,0,201,21" href="#area1">
                        <area shape="rect" alt="Прямоугольная область 2" coords="0,21,70,102" href="#area2">
                        <area shape="circle" alt="Круглая область" coords="90,50,45" href="#area3">
                        <area shape="rect" alt="Прямоугольная область 3" coords="0,0,201,102" href="#area4">
                    </map>
                    <img src="kotyara.jpg" width="201" height="102" usemap="#map0" alt="Котяра">
                </li>
                <li><a href="#">Ссылка с пустым href</a></li>
                <li><a href="#">Ссылка без href</a></li>
                <li><a href="https://www.kubsu.ru/" rel="nofollow">Запрещен переход поисковикам</a></li>
                <li><a href="https://www.kubsu.ru/" rel="noindex">Запрещенная для индексации поисковиками</a></li>
                <li>
                    <ol>
                        <li><a href="index.html" title="Первая">Первая</a></li>
                        <li><a href="index.html" title="Вторая">Вторая</a></li>
                        <li><a href="index.html" title="Третья">Третья</a></li>
                    </ol>
                </li>
                <li><a href="ftp://username:password@ftp.sobaka.com/path/to/file">Ссылка на файл на сервере FTP с авторизацией</a></li>
            </ul>
        </div>
        <div class="tabl m-2 p-2 m-md-3" id="tabl">
            <table>
                <caption>Значения тригонометрических функций</caption>
                <thead>
                    <tr>
                        <th>Функция</th>
                        <th>Значение (0)</th>
                        <th>Значение (π/6)</th>
                        <th>Значение (π/3)</th>
                        <th>Значение (π/2)</th>
                        <th>Значение (π)</th>
                        <th>Значение (3π/2)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>sin(x)</td>
                        <td>0</td>
                        <td>1/2</td>
                        <td>√3/2</td>
                        <td>1</td>
                        <td>0</td>
                        <td>-1</td>
                    </tr>
                    <tr>
                        <td>cos(x)</td>
                        <td>1</td>
                        <td>√3/2</td>
                        <td>1/2</td>
                        <td>0</td>
                        <td>-1</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>tan(x)</td>
                        <td>0</td>
                        <td>1/√3</td>
                        <td>√3</td>
                        <td>∞</td>
                        <td>0</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>cot(x)</td>
                        <td>∞</td>
                        <td>√3</td>
                        <td>1/√3</td>
                        <td>0</td>
                        <td>∞</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>csc(x)</td>
                        <td>∞</td>
                        <td>2</td>
                        <td>2/√3</td>
                        <td>1</td>
                        <td>∞</td>
                        <td>-1</td>
                    </tr>
                    <tr>
                        <td>sec(x)</td>
                        <td>1</td>
                        <td>2/√3</td>
                        <td>2</td>
                        <td>∞</td>
                        <td>-1</td>
                        <td>∞</td>
                    </tr>
                    <tr>
                        <td>----></td>
                        <td colspan="6">Вот такие пироги</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="forma m-2 p-2 m-md-3" id="forma">
        <h1>Форма</h1>
        
        <?php if (!empty($messages)): ?>
            <div class="mb-3">
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php 
        $has_errors = false;
        foreach ($errors as $error) {
            if (!empty($error)) {
                $has_errors = true;
                break;
            }
        }
        ?>
        
        <?php if ($has_errors): ?>
            <div class="alert alert-danger mb-3">
                <h4>Обнаружены ошибки:</h4>
                <ul class="mb-0">
                    <?php foreach ($errors as $field => $error): ?>
                        <?php if (!empty($error)): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="submit.php" method="POST">
        <!-- ФИО -->
        <div class="form-group">
            <label for="name">ФИО:</label>
            <input type="text" class="form-control <?php echo !empty($errors['name']) ? 'is-invalid' : ''; ?>" 
                   id="name" name="name" placeholder="Иванов Иван Иванович" required
                   value="<?php echo htmlspecialchars($values['name'] ?? ''); ?>">
            <?php if (!empty($errors['name'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Телефон -->
        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" class="form-control <?php echo !empty($errors['phone']) ? 'is-invalid' : ''; ?>" 
                   id="phone" name="phone" placeholder="+7 (918) 123-45-67" required
                   value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>">
            <?php if (!empty($errors['phone'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Email -->
        <div class="form-group">
            <label for="email">Электронная почта:</label>
            <input type="email" class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
                   id="email" name="email" placeholder="example@mail.com" required
                   value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>">
            <?php if (!empty($errors['email'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Дата рождения -->
        <div class="form-group">
            <label for="birthdate">Дата рождения:</label>
            <input type="date" class="form-control <?php echo !empty($errors['birthdate']) ? 'is-invalid' : ''; ?>" 
                   id="birthdate" name="birthdate" required
                   value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>">
            <?php if (!empty($errors['birthdate'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Пол -->
        <div class="form-group">
            <label>Пол:</label>
            <div class="form-check">
                <input class="form-check-input <?php echo !empty($errors['gender']) ? 'is-invalid' : ''; ?>" 
                       type="radio" name="gender" id="male" value="male" required
                       <?php echo ($values['gender'] ?? '') === 'male' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="male">Мужской</label>
            </div>
            <div class="form-check">
                <input class="form-check-input <?php echo !empty($errors['gender']) ? 'is-invalid' : ''; ?>" 
                       type="radio" name="gender" id="female" value="female"
                       <?php echo ($values['gender'] ?? '') === 'female' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="female">Женский</label>
            </div>
            <div class="form-check">
                <input class="form-check-input <?php echo !empty($errors['gender']) ? 'is-invalid' : ''; ?>" 
                       type="radio" name="gender" id="other" value="other"
                       <?php echo ($values['gender'] ?? '') === 'other' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="other">Другое</label>
            </div>
            <?php if (!empty($errors['gender'])): ?>
                <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['gender']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Языки программирования -->
        <div class="form-group">
            <label for="languages">Любимый язык программирования:</label>
            <select class="form-control <?php echo !empty($errors['languages']) ? 'is-invalid' : ''; ?>" 
                    id="languages" name="languages[]" multiple="multiple" required size="5">
                <?php 
                $allLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala'];
                $selectedLanguages = isset($values['languages']) ? (is_array($values['languages']) ? $values['languages'] : explode(',', $values['languages'])) : [];
                
                foreach ($allLanguages as $lang): ?>
                    <option value="<?php echo htmlspecialchars($lang); ?>" 
                        <?php echo in_array($lang, $selectedLanguages) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($lang); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['languages'])): ?>
                <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['languages']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Биография -->
        <div class="form-group">
            <label for="bio">Биография:</label>
            <textarea class="form-control <?php echo !empty($errors['bio']) ? 'is-invalid' : ''; ?>" 
                      id="bio" name="bio" required rows="5"><?php 
                      echo htmlspecialchars($values['bio'] ?? ''); ?></textarea>
            <?php if (!empty($errors['bio'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['bio']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Чекбокс контракта -->
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input <?php echo !empty($errors['contract_accepted']) ? 'is-invalid' : ''; ?>" 
                   id="contract_accepted" name="contract_accepted" value="1" required
                   <?php echo ($values['contract_accepted'] ?? '') ? 'checked' : ''; ?>>
            <label class="form-check-label" for="contract_accepted">С контрактом ознакомлен(а)</label>
            <?php if (!empty($errors['contract_accepted'])): ?>
                <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contract_accepted']); ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
            
            <?php if (!empty($_SESSION['login'])): ?>
                <a href="logout.php" class="btn btn-danger ml-2">Выйти</a>
            <?php endif; ?>
        </form>
    </div>

        <h1 id="important">МЕНЯ ЗОВУТ ВОЛОДЯ</h1>
    </div>
    <footer class="page-footer p-3 mt-3">
        <span>© Владимир Хачатурян</span>
    </footer>
</body>
</html>
