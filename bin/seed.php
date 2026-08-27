<?php

declare(strict_types=1);

use App\Database;

require dirname(__DIR__) . '/vendor/autoload.php';

const TRANSLIT = [
    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
    'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
    'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
    'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
    'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
    'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
    'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
    'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
    'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
    'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
    'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch',
    'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
    'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
];

const PARAGRAPHS = [
    'Технологии не стоят на месте, и каждое новое решение открывает перед нами возможности, о которых ещё вчера мы не могли и мечтать. Главное — уметь вовремя заметить тренд и использовать его с умом.',
    'История показывает, что самые смелые идеи часто встречают сопротивление. Но именно те, кто не боится пробовать новое, в итоге оказываются впереди и задают направление для остальных.',
    'В повседневной жизни мы редко задумываемся о том, сколько мелких деталей окружает нас. А ведь именно из них складывается комфорт, который мы часто воспринимаем как должное.',
    'Практика — лучший учитель. Теория даёт фундамент, но настоящий опыт приходит только через действие, ошибки и умение делать из них правильные выводы.',
    'Каждый выбор, который мы делаем, открывает одни двери и закрывает другие. Важно научиться принимать решения осознанно и не жалеть о сделанном.',
    'Вдохновение приходит к тем, кто к нему готов. Иногда достаточно просто остановиться, оглянуться вокруг и заметить красоту в самых обычных вещах.',
    'Сообщество единомышленников — это огромная сила. Вместе мы можем достичь того, что в одиночку казалось бы невозможным.',
    'Скорость важна, но не менее важна надёжность. Успех приходит к тем, кто умеет балансировать между дерзостью и осторожностью.',
];

$categories = [
    ['slug' => 'technology', 'name' => 'Технологии', 'description' => 'Новости и обзоры из мира IT и цифровых технологий'],
    ['slug' => 'travel', 'name' => 'Путешествия', 'description' => 'Гиды, советы и впечатления из поездок по всему миру'],
    ['slug' => 'cooking', 'name' => 'Кулинария', 'description' => 'Рецепты, техники и секреты профессиональных поваров'],
    ['slug' => 'design', 'name' => 'Дизайн', 'description' => 'Тренды, кейсы и разборы в графическом и веб-дизайне'],
    ['slug' => 'sport', 'name' => 'Спорт', 'description' => 'Новости спорта, тренировки и здоровый образ жизни'],
];

$titles = [
    'Как искусственный интеллект меняет нашу повседневную жизнь',
    'Лучшие маршруты для самостоятельного путешествия по Европе',
    'Рецепт идеального борща: секреты и тонкости',
    'Типографика в веб-дизайне: почему шрифт решает всё',
    'Тренировки дома: программа на каждый день',
    'Обзор современных смартфонов середины года',
    'Что посмотреть в Японии за две недели',
    'Идеальные блины: проверенный рецепт и советы',
    'Моушн-дизайн: как оживить интерфейс',
    'Бег по утрам: с чего начать новичку',
    'Кибербезопасность дома: базовые правила',
    'Три дня в Петербурге: куда сходить обязательно',
    'Ферментация: современный взгляд на консервацию',
    'Цветовые палитры в дизайне: психология цвета',
    'Питание спортсмена: что есть до и после тренировки',
];

function transliterate(string $text): string
{
    $result = strtr($text, TRANSLIT);
    $result = preg_replace('/[^A-Za-z0-9]+/', '-', $result);
    $result = trim($result, '-');
    return strtolower($result);
}

function uniqueSlug(string $title, array $usedSlugs): string
{
    $base = transliterate($title);
    $slug = $base;
    $i = 2;
    while (isset($usedSlugs[$slug])) {
        $slug = $base . '-' . $i;
        $i++;
    }
    return $slug;
}

function randomText(int $paragraphCount): string
{
    $indexes = array_rand(PARAGRAPHS, min($paragraphCount, count(PARAGRAPHS)));
    if (!is_array($indexes)) {
        $indexes = [$indexes];
    }
    shuffle($indexes);
    $parts = [];
    foreach ($indexes as $index) {
        $parts[] = PARAGRAPHS[$index];
    }
    return implode("\n\n", $parts);
}

function randomDate(): string
{
    $now = new DateTimeImmutable();
    $min = $now->sub(new DateInterval('P6M'));
    $random = mt_rand(0, $now->getTimestamp() - $min->getTimestamp());
    return date('Y-m-d H:i:s', $min->getTimestamp() + $random);
}

$pdo = Database::getConnection();

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE article_category');
$pdo->exec('TRUNCATE TABLE articles');
$pdo->exec('TRUNCATE TABLE categories');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$pdo->beginTransaction();

try {
    $categoryStmt = $pdo->prepare(
        'INSERT INTO categories (slug, name, description) VALUES (:slug, :name, :description)'
    );
    foreach ($categories as $category) {
        $categoryStmt->execute([
            ':slug' => $category['slug'],
            ':name' => $category['name'],
            ':description' => $category['description'],
        ]);
    }

    $articleStmt = $pdo->prepare(
        'INSERT INTO articles (slug, title, description, text, image, views, created_at)
         VALUES (:slug, :title, :description, :text, :image, :views, :created_at)'
    );

    $usedSlugs = [];
    $categoryIds = $pdo->query('SELECT id FROM categories')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($titles as $index => $title) {
        $slug = uniqueSlug($title, $usedSlugs);
        $usedSlugs[$slug] = true;

        $paragraphs = 3 + ($index % 4);
        $description = mb_substr(PARAGRAPHS[$index % count(PARAGRAPHS)], 0, 120) . '…';

        $articleStmt->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':description' => $description,
            ':text' => randomText($paragraphs),
            ':image' => sprintf('https://picsum.photos/seed/%s/800/450', $slug),
            ':views' => mt_rand(0, 500),
            ':created_at' => randomDate(),
        ]);
    }

    $articleIds = $pdo->query('SELECT id FROM articles')->fetchAll(PDO::FETCH_COLUMN);

    $linkStmt = $pdo->prepare(
        'INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)'
    );

    foreach ($articleIds as $index => $articleId) {
        $firstCategory = $categoryIds[$index % count($categoryIds)];
        $linked = [$firstCategory];
        if (($index % 3) === 0) {
            $linked[] = $categoryIds[($index + 2) % count($categoryIds)];
        }
        foreach ($linked as $categoryId) {
            $linkStmt->execute([
                ':article_id' => $articleId,
                ':category_id' => $categoryId,
            ]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Ошибка сидинга: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

printf("Сидинг завершён: %d категорий, %d статей создано\n", count($categories), count($titles));
