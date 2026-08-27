<?php

declare(strict_types=1);

use App\Database;
use Faker\Factory;
use Faker\Generator;

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

function transliterate(string $text): string
{
    $result = strtr($text, TRANSLIT);
    $result = preg_replace('/[^A-Za-z0-9]+/', '-', $result);
    $result = trim($result, '-');
    return strtolower(mb_substr($result, 0, 80));
}

function uniqueSlug(string $title, array $usedSlugs): string
{
    $base = transliterate($title) ?: 'article';
    $slug = $base;
    $i = 2;
    while (isset($usedSlugs[$slug])) {
        $slug = mb_substr($base, 0, 80 - strlen((string) $i) - 1) . '-' . $i;
        $i++;
    }
    return $slug;
}

function cliOption(string $name, string $env, int $default): int
{
    global $cliOptions;
    if (isset($cliOptions[$name])) {
        return max(1, (int) $cliOptions[$name]);
    }
    $value = getenv($env);
    if ($value !== false && $value !== '') {
        return max(1, (int) $value);
    }
    return $default;
}

$cliOptions = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--([\w-]+)=(.*)$/', $arg, $m)) {
        $cliOptions[$m[1]] = $m[2];
    }
}

$categoryCount = cliOption('categories', 'SEED_CATEGORIES', 100);
$articlesMin = cliOption('articles-min', 'SEED_ARTICLES_MIN', 100);
$articlesMax = cliOption('articles-max', 'SEED_ARTICLES_MAX', 300);

if ($articlesMax < $articlesMin) {
    [$articlesMin, $articlesMax] = [$articlesMax, $articlesMin];
}

$faker = Factory::create('ru_RU');

function randomWords(Generator $faker, array $pool, int $min, int $max): array
{
    $count = min(mt_rand($min, $max), count($pool));
    $indexes = array_rand($pool, $count);
    if (!is_array($indexes)) {
        $indexes = [$indexes];
    }
    shuffle($indexes);
    $words = [];
    foreach ($indexes as $index) {
        $words[] = $pool[$index];
    }
    return $words;
}

function uniqueName(Generator $faker, array $pool, array &$used): string
{
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $name = mb_convert_case(implode(' ', randomWords($faker, $pool, 1, 2)), MB_CASE_TITLE, 'UTF-8');
        if (!isset($used[$name])) {
            $used[$name] = true;
            return $name;
        }
    }
    $name = 'Раздел ' . (count($used) + 1);
    $used[$name] = true;
    return $name;
}

$wordPool = [];
for ($i = 0; $i < 3; $i++) {
    $wordPool = array_merge($wordPool, preg_split('/\s+/u', $faker->realText(500)));
}
$wordPool = array_values(array_filter(array_unique(array_map(
    fn (string $word) => trim($word, ".,!?;:«»()\"'"),
    $wordPool
)), fn (string $word) => $word !== ''));

$paragraphPool = [];
for ($i = 0; $i < 40; $i++) {
    $paragraphPool[] = $faker->realText(mt_rand(120, 220));
}

$pdo = Database::getConnection();

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE article_category');
$pdo->exec('TRUNCATE TABLE articles');
$pdo->exec('TRUNCATE TABLE categories');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$pdo->beginTransaction();

$totalArticles = 0;

try {
    $categoryStmt = $pdo->prepare(
        'INSERT INTO categories (slug, name, description) VALUES (:slug, :name, :description)'
    );

    $categoryIds = [];
    $usedCategoryNames = [];
    for ($i = 0; $i < $categoryCount; $i++) {
        $name = uniqueName($faker, $wordPool, $usedCategoryNames);
        $slug = uniqueSlug($name, []);
        $categoryStmt->execute([
            ':slug' => $slug,
            ':name' => $name,
            ':description' => $faker->realText(120),
        ]);
        $categoryIds[] = (int) $pdo->lastInsertId();
    }

    $articleStmt = $pdo->prepare(
        'INSERT INTO articles (slug, title, description, text, image, views, created_at)
         VALUES (:slug, :title, :description, :text, :image, :views, :created_at)'
    );
    $linkStmt = $pdo->prepare(
        'INSERT INTO article_category (article_id, category_id) VALUES (:article_id, :category_id)'
    );

    $usedSlugs = [];

    foreach ($categoryIds as $categoryId) {
        $count = mt_rand($articlesMin, $articlesMax);
        for ($j = 0; $j < $count; $j++) {
            $title = mb_convert_case(implode(' ', randomWords($faker, $wordPool, 4, 7)), MB_CASE_TITLE, 'UTF-8');
            $slug = uniqueSlug($title, $usedSlugs);
            $usedSlugs[$slug] = true;

            $paragraphIndexes = array_rand($paragraphPool, mt_rand(3, 5));
            if (!is_array($paragraphIndexes)) {
                $paragraphIndexes = [$paragraphIndexes];
            }
            shuffle($paragraphIndexes);
            $paragraphs = [];
            foreach ($paragraphIndexes as $paragraphIndex) {
                $paragraphs[] = $paragraphPool[$paragraphIndex];
            }

            $now = time();
            $min = $now - 6 * 30 * 24 * 3600;
            $createdAt = date('Y-m-d H:i:s', mt_rand($min, $now));

            $articleStmt->execute([
                ':slug' => $slug,
                ':title' => $title,
                ':description' => mb_substr($paragraphs[0], 0, 120) . '…',
                ':text' => implode("\n\n", $paragraphs),
                ':image' => sprintf('https://picsum.photos/seed/%s/800/450', $slug),
                ':views' => mt_rand(0, 500),
                ':created_at' => $createdAt,
            ]);
            $articleId = (int) $pdo->lastInsertId();

            $linkStmt->execute([
                ':article_id' => $articleId,
                ':category_id' => $categoryId,
            ]);

            if (mt_rand(1, 100) <= 50) {
                $extraCount = min(mt_rand(1, 4), count($categoryIds) - 1);
                $others = array_values(array_diff($categoryIds, [$categoryId]));
                shuffle($others);
                foreach (array_slice($others, 0, $extraCount) as $extraCategoryId) {
                    $linkStmt->execute([
                        ':article_id' => $articleId,
                        ':category_id' => $extraCategoryId,
                    ]);
                }
            }

            $totalArticles++;
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Ошибка сидинга: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

printf(
    "Сидинг завершён: %d категорий, %d статей создано (по %d–%d на категорию)\n",
    $categoryCount,
    $totalArticles,
    $articlesMin,
    $articlesMax
);