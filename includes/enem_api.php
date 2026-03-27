<?php
declare(strict_types=1);

const ENEM_API_BASE_URL = 'https://api.enem.dev/v1';
const ENEM_API_SOURCE_NAME = 'API ENEM';
const ENEM_API_CACHE_DIR = 'cache/enem';

function enem_fetch_exams(): array
{
    $payload = enem_api_get_json('/exams', [], 43200);

    if (!is_array($payload)) {
        throw new RuntimeException('Resposta invalida ao listar provas do ENEM.');
    }

    usort(
        $payload,
        static fn(array $left, array $right): int => (int) ($right['year'] ?? 0) <=> (int) ($left['year'] ?? 0)
    );

    return $payload;
}

function enem_fetch_exam(int $year): ?array
{
    foreach (enem_fetch_exams() as $exam) {
        if ((int) ($exam['year'] ?? 0) === $year) {
            return $exam;
        }
    }

    return null;
}

function enem_fetch_questions(int $year, int $limit = 10, int $offset = 0, ?string $language = null): array
{
    $query = [
        'limit' => max(1, min($limit, 50)),
        'offset' => max(0, $offset),
    ];

    if ($language !== null && $language !== '') {
        $query['language'] = $language;
    }

    $payload = enem_api_get_json('/exams/' . $year . '/questions', $query, 900);

    if (!is_array($payload) || !isset($payload['questions']) || !is_array($payload['questions'])) {
        throw new RuntimeException('Resposta invalida ao listar questoes do ENEM.');
    }

    return $payload;
}

function enem_fetch_question(int $year, int $index, ?string $language = null): array
{
    $query = [];

    if ($language !== null && $language !== '') {
        $query['language'] = $language;
    }

    $payload = enem_api_get_json('/exams/' . $year . '/questions/' . $index, $query, 86400);

    if (!is_array($payload) || !isset($payload['title'])) {
        throw new RuntimeException('Resposta invalida ao carregar a questao do ENEM.');
    }

    return $payload;
}

function enem_import_question(int $userId, int $year, int $index, ?string $language = null): array
{
    $question = enem_fetch_question($year, $index, $language);
    $sourceReference = enem_api_reference(
        (int) ($question['year'] ?? $year),
        (int) ($question['index'] ?? $index),
        isset($question['language']) ? (string) $question['language'] : null
    );
    $existingId = enem_existing_imported_question_id($userId, $sourceReference);

    if ($existingId !== null) {
        return [
            'id' => $existingId,
            'created' => false,
        ];
    }

    $disciplineName = enem_resolve_discipline_name((string) ($question['discipline'] ?? ''), (int) ($question['year'] ?? $year));
    $subjectName = enem_resolve_subject_name((int) ($question['year'] ?? $year), isset($question['language']) ? (string) $question['language'] : null);
    $catalog = enem_ensure_catalog_entries($userId, $disciplineName, $subjectName);
    $prompt = enem_compose_prompt($question);
    $files = enem_string_list($question['files'] ?? []);
    $promptImageUrl = $files[0] ?? null;

    if (count($files) > 1) {
        $prompt .= "\n\nArquivos complementares:\n" . implode(
            "\n",
            array_map(
                static fn(string $fileUrl): string => '- ' . $fileUrl,
                array_slice($files, 1)
            )
        );
    }

    if ($prompt === '') {
        $prompt = (string) ($question['title'] ?? ('Questao ' . $index . ' - ENEM ' . $year));
    }

    $alternatives = is_array($question['alternatives'] ?? null) ? $question['alternatives'] : [];

    if ($alternatives === []) {
        throw new RuntimeException('A questao do ENEM nao possui alternativas importaveis.');
    }

    db()->beginTransaction();

    try {
        $insert = db()->prepare(
            'INSERT INTO questions
             (author_id, based_on_question_id, title, prompt, prompt_image_url, question_type, visibility, discipline_id, subject_id, education_level, difficulty, status, allow_multiple_correct, discursive_answer, response_lines, drawing_size, drawing_height_px, true_false_answer, source_name, source_url, source_reference, usage_count, created_at, updated_at)
             VALUES
             (:author_id, NULL, :title, :prompt, :prompt_image_url, :question_type, :visibility, :discipline_id, :subject_id, :education_level, :difficulty, :status, :allow_multiple_correct, NULL, NULL, NULL, NULL, NULL, :source_name, :source_url, :source_reference, 0, NOW(), NOW())'
        );
        $insert->execute([
            'author_id' => $userId,
            'title' => (string) ($question['title'] ?? ('Questao ' . $index . ' - ENEM ' . $year)),
            'prompt' => $prompt,
            'prompt_image_url' => $promptImageUrl,
            'question_type' => 'multiple_choice',
            'visibility' => 'private',
            'discipline_id' => $catalog['discipline_id'],
            'subject_id' => $catalog['subject_id'],
            'education_level' => 'medio',
            'difficulty' => 'medio',
            'status' => 'published',
            'allow_multiple_correct' => 0,
            'source_name' => ENEM_API_SOURCE_NAME,
            'source_url' => enem_question_api_url((int) ($question['year'] ?? $year), (int) ($question['index'] ?? $index), isset($question['language']) ? (string) $question['language'] : null),
            'source_reference' => $sourceReference,
        ]);

        $questionId = (int) db()->lastInsertId();
        $insertOption = db()->prepare(
            'INSERT INTO question_options (question_id, option_text, is_correct, display_order, created_at)
             VALUES (:question_id, :option_text, :is_correct, :display_order, NOW())'
        );

        foreach (array_values($alternatives) as $displayOrder => $alternative) {
            $optionText = enem_compose_alternative_text(is_array($alternative) ? $alternative : []);

            $insertOption->execute([
                'question_id' => $questionId,
                'option_text' => $optionText,
                'is_correct' => !empty($alternative['isCorrect']) ? 1 : 0,
                'display_order' => $displayOrder + 1,
            ]);
        }

        db()->commit();

        return [
            'id' => $questionId,
            'created' => true,
        ];
    } catch (Throwable $throwable) {
        db()->rollBack();
        throw $throwable;
    }
}

function enem_compose_prompt(array $question): string
{
    $parts = [];
    $context = enem_markdown_to_text((string) ($question['context'] ?? ''));
    $intro = enem_markdown_to_text((string) ($question['alternativesIntroduction'] ?? ''));

    if ($context !== '') {
        $parts[] = $context;
    }

    if ($intro !== '') {
        $parts[] = $intro;
    }

    return trim(implode("\n\n", $parts));
}

function enem_compose_alternative_text(array $alternative): string
{
    $text = trim(enem_markdown_to_text((string) ($alternative['text'] ?? '')));
    $file = trim((string) ($alternative['file'] ?? ''));
    $letter = trim((string) ($alternative['letter'] ?? ''));

    if ($text === '' && $file !== '') {
        $text = 'Imagem da alternativa: ' . $file;
    } elseif ($file !== '') {
        $text .= "\nImagem da alternativa: " . $file;
    }

    if ($letter !== '') {
        return $letter . ') ' . $text;
    }

    return $text;
}

function enem_markdown_to_text(string $markdown): string
{
    $text = str_replace(["\r\n", "\r"], "\n", trim($markdown));
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1 ($2)', $text) ?? $text;
    $text = preg_replace('/(?<!\w)[*_~`]+|[*_~`]+(?!\w)/', '', $text) ?? $text;
    $text = preg_replace("/[ \t]+\n/", "\n", $text) ?? $text;
    $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

    return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function enem_question_reference(array $question): string
{
    return enem_api_reference(
        (int) ($question['year'] ?? 0),
        (int) ($question['index'] ?? 0),
        isset($question['language']) ? (string) $question['language'] : null
    );
}

function enem_resolve_discipline_name(string $disciplineValue, int $year): string
{
    return enem_api_discipline_name($disciplineValue);
}

function enem_resolve_language_label(?string $languageValue, int $year): ?string
{
    if ($languageValue === null || $languageValue === '') {
        return null;
    }

    $exam = enem_fetch_exam($year);
    $label = $exam ? enem_find_label_by_value((array) ($exam['languages'] ?? []), $languageValue) : null;

    if ($label !== null && $label !== '') {
        return enem_normalize_spaces($label);
    }

    return match ($languageValue) {
        'ingles' => 'Ingles',
        'espanhol' => 'Espanhol',
        default => ucfirst(str_replace('-', ' ', $languageValue)),
    };
}

function enem_resolve_subject_name(int $year, ?string $languageValue): string
{
    return 'ENEM';
}

function enem_find_label_by_value(array $items, string $value): ?string
{
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        if ((string) ($item['value'] ?? '') === $value) {
            return isset($item['label']) ? (string) $item['label'] : null;
        }
    }

    return null;
}

function enem_existing_imported_question_id(int $userId, string $sourceReference): ?int
{
    $statement = db()->prepare(
        'SELECT id
         FROM questions
         WHERE author_id = :author_id AND source_name = :source_name AND source_reference = :source_reference
         LIMIT 1'
    );
    $statement->execute([
        'author_id' => $userId,
        'source_name' => ENEM_API_SOURCE_NAME,
        'source_reference' => $sourceReference,
    ]);
    $id = $statement->fetchColumn();

    return $id === false ? null : (int) $id;
}

function enem_ensure_catalog_entries(int $userId, string $disciplineName, string $subjectName): array
{
    $insertDiscipline = db()->prepare(
        'INSERT IGNORE INTO disciplines (name, created_by, created_at)
         VALUES (:name, :created_by, NOW())'
    );
    $insertDiscipline->execute([
        'name' => $disciplineName,
        'created_by' => $userId,
    ]);

    $disciplineLookup = db()->prepare('SELECT id FROM disciplines WHERE name = :name LIMIT 1');
    $disciplineLookup->execute(['name' => $disciplineName]);
    $disciplineId = (int) $disciplineLookup->fetchColumn();

    if ($disciplineId <= 0) {
        throw new RuntimeException('Nao foi possivel localizar a disciplina do ENEM.');
    }

    $insertSubject = db()->prepare(
        'INSERT IGNORE INTO subjects (discipline_id, name, created_by, created_at)
         VALUES (:discipline_id, :name, :created_by, NOW())'
    );
    $insertSubject->execute([
        'discipline_id' => $disciplineId,
        'name' => $subjectName,
        'created_by' => $userId,
    ]);

    $subjectLookup = db()->prepare(
        'SELECT id
         FROM subjects
         WHERE discipline_id = :discipline_id AND name = :name
         LIMIT 1'
    );
    $subjectLookup->execute([
        'discipline_id' => $disciplineId,
        'name' => $subjectName,
    ]);
    $subjectId = (int) $subjectLookup->fetchColumn();

    if ($subjectId <= 0) {
        throw new RuntimeException('Nao foi possivel localizar o assunto do ENEM.');
    }

    return [
        'discipline_id' => $disciplineId,
        'subject_id' => $subjectId,
    ];
}

function enem_question_api_url(int $year, int $index, ?string $language = null): string
{
    $url = ENEM_API_BASE_URL . '/exams/' . $year . '/questions/' . $index;

    if ($language !== null && $language !== '') {
        $url .= '?' . http_build_query(['language' => $language]);
    }

    return $url;
}

function enem_api_get_json(string $path, array $query = [], int $ttlSeconds = 900): array
{
    $url = rtrim(ENEM_API_BASE_URL, '/') . '/' . ltrim($path, '/');

    if ($query !== []) {
        $url .= '?' . http_build_query($query);
    }

    $cacheFile = enem_cache_file_path($url);
    $cached = enem_read_cache($cacheFile, $ttlSeconds);

    if ($cached !== null) {
        return $cached;
    }

    [$statusCode, $body] = enem_http_get($url);
    $decoded = json_decode($body, true);

    if (!is_array($decoded)) {
        throw new RuntimeException('A API ENEM retornou um payload invalido.');
    }

    if ($statusCode >= 400) {
        $message = isset($decoded['message']) && is_string($decoded['message'])
            ? $decoded['message']
            : 'Erro ao consultar a API ENEM.';
        throw new RuntimeException($message . ' (HTTP ' . $statusCode . ')');
    }

    enem_write_cache($cacheFile, $decoded);

    return $decoded;
}

function enem_http_get(string $url): array
{
    if (function_exists('curl_init')) {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $body = curl_exec($curl);

        if ($body === false) {
            $message = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('Falha na requisicao para a API ENEM: ' . $message);
        }

        $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return [$statusCode, $body];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'timeout' => 15,
            'ignore_errors' => true,
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];

    if ($body === false) {
        throw new RuntimeException('Falha na requisicao para a API ENEM.');
    }

    $statusCode = 200;

    foreach ($headers as $headerLine) {
        if (preg_match('#HTTP/\S+\s+(\d{3})#', $headerLine, $matches) === 1) {
            $statusCode = (int) $matches[1];
            break;
        }
    }

    return [$statusCode, $body];
}

function enem_cache_file_path(string $url): string
{
    return storage_path(ENEM_API_CACHE_DIR . DIRECTORY_SEPARATOR . sha1($url) . '.json');
}

function enem_read_cache(string $cacheFile, int $ttlSeconds): ?array
{
    if (!is_file($cacheFile)) {
        return null;
    }

    if ((time() - (int) filemtime($cacheFile)) > $ttlSeconds) {
        return null;
    }

    $contents = file_get_contents($cacheFile);

    if ($contents === false) {
        return null;
    }

    $decoded = json_decode($contents, true);

    return is_array($decoded) ? $decoded : null;
}

function enem_write_cache(string $cacheFile, array $payload): void
{
    $directory = dirname($cacheFile);

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Nao foi possivel criar o diretorio de cache da API ENEM.');
    }

    file_put_contents(
        $cacheFile,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

function enem_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $items = array_filter(
        array_map(
            static fn(mixed $item): string => is_string($item) ? trim($item) : '',
            $value
        ),
        static fn(string $item): bool => $item !== ''
    );

    return array_values($items);
}

function enem_normalize_spaces(string $value): string
{
    return trim(preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $value)) ?? $value);
}

function enem_api_supported_languages(): array
{
    return [
        'ingles' => 'Ingles',
        'espanhol' => 'Espanhol',
    ];
}

function enem_api_list_questions(int $year, int $limit = 10, int $offset = 0, ?string $language = null): array
{
    return enem_fetch_questions($year, $limit, $offset, $language);
}

function enem_api_get_question(int $year, int $index, ?string $language = null): array
{
    return enem_fetch_question($year, $index, $language);
}

function enem_api_reference(int $year, int $index, ?string $language = null): string
{
    $reference = 'ENEM ' . $year . ' Q' . $index;

    if ($language !== null && $language !== '') {
        $reference .= ' [' . $language . ']';
    }

    return $reference;
}

function enem_api_source_url(int $year, int $index, ?string $language = null): string
{
    return enem_question_api_url($year, $index, $language);
}

function enem_api_discipline_name(mixed $discipline): string
{
    return match ((string) $discipline) {
        'ciencias-humanas' => 'Ciencias Humanas e suas Tecnologias',
        'ciencias-natureza' => 'Ciencias da Natureza e suas Tecnologias',
        'linguagens' => 'Linguagens, Codigos e suas Tecnologias',
        'matematica' => 'Matematica e suas Tecnologias',
        default => 'ENEM',
    };
}

function enem_api_join_prompt(array $question): string
{
    return enem_compose_prompt($question);
}

function enem_api_to_text(mixed $value): string
{
    return is_string($value) ? enem_markdown_to_text($value) : '';
}
