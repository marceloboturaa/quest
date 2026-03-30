<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';

function exam_example_default_fields(string $teacherName = ''): array
{
    $today = date('Y-m-d');
    $teacherName = trim($teacherName) !== '' ? trim($teacherName) : 'Professor(a) responsável';

    return [
        'draft_title' => 'Modelo padrão - prova completa',
        'exam_template' => 'version_1',
        'exam_style' => 'double_column',
        'response_mode' => 'bubble_answer_sheet',
        'composition_mode' => 'mixed',
        'ordering_mode' => 'automatic_numbering',
        'identification_mode' => 'standard',
        'variant_label' => '',
        'exam_label' => 'AVALIAÇÃO TRIMESTRAL',
        'discipline' => 'Língua Portuguesa',
        'component_name' => 'Língua Portuguesa',
        'teacher_name' => $teacherName,
        'school_name' => EXAM_DEFAULT_SCHOOL_NAME,
        'school_subtitle' => EXAM_DEFAULT_SCHOOL_SUBTITLE,
        'year_reference' => '6º ano',
        'class_name' => '6A',
        'application_date' => $today,
        'draft_header_content' => 'Leia atentamente cada questão antes de responder.',
        'draft_body_content' => 'Organize seu tempo, mantenha a calma e revise as respostas antes de entregar.',
        'draft_footer_content' => 'Confira nome, turma e se todas as questões foram respondidas.',
        'header_logo_left' => EXAM_DEFAULT_LOGO_URL,
        'header_logo_right' => '',
        'header_background_color' => '#ffffff',
        'header_title_color' => '#334155',
        'header_subtitle_color' => '#64748b',
        'header_title_size' => '20',
        'header_subtitle_size' => '16',
        'header_logo_size' => '2.2',
        'header_min_height' => '3.2',
        'content_font_size' => '11',
    ];
}

function exam_example_complete_fields(array $fields): array
{
    $completed = array_merge(
        exam_example_default_fields((string) ($fields['teacher_name'] ?? '')),
        $fields
    );

    if (trim((string) ($completed['component_name'] ?? '')) === '') {
        $completed['component_name'] = (string) ($completed['discipline'] ?? 'Língua Portuguesa');
    }

    if (trim((string) ($completed['draft_title'] ?? '')) === '') {
        $completed['draft_title'] = 'Modelo padrão - prova completa';
    }

    if (trim((string) ($completed['draft_header_content'] ?? '')) === '') {
        $completed['draft_header_content'] = 'Leia atentamente cada questão antes de responder.';
    }

    if (trim((string) ($completed['draft_body_content'] ?? '')) === '') {
        $completed['draft_body_content'] = 'Organize seu tempo, mantenha a calma e revise as respostas antes de entregar.';
    }

    if (trim((string) ($completed['draft_footer_content'] ?? '')) === '') {
        $completed['draft_footer_content'] = 'Confira nome, turma e se todas as questões foram respondidas.';
    }

    return $completed;
}

function exam_example_presets(string $teacherName = ''): array
{
    $base = exam_example_default_fields($teacherName);

    $examples = [
        [
            'slug' => 'modelo-padrao',
            'title' => 'Modelo padrão',
            'description' => 'Modelo único da plataforma, com cabeçalho completo, gabarito, questões em card e rodapé prontos para editar.',
            'tags' => ['Padrão', 'Cabeçalho', 'Gabarito', 'Cards'],
            'fields' => [
                'draft_title' => 'Modelo padrão - prova completa',
                'discipline' => 'Língua Portuguesa',
                'component_name' => 'Língua Portuguesa',
                'exam_label' => 'AVALIAÇÃO TRIMESTRAL',
                'class_name' => '6A',
                'year_reference' => '6º ano',
                'exam_style' => 'double_column',
                'response_mode' => 'bubble_answer_sheet',
            ],
        ],
    ];

    return array_map(static function (array $example) use ($base): array {
        $example['fields'] = exam_example_complete_fields(array_merge($base, $example['fields']));
        return $example;
    }, $examples);
}

function exam_example_query(array $example, array $overrides = []): array
{
    return exam_example_complete_fields(array_merge($example['fields'] ?? [], $overrides));
}

function exam_example_find(string $slug, string $teacherName = ''): ?array
{
    foreach (exam_example_presets($teacherName) as $example) {
        if ((string) ($example['slug'] ?? '') === $slug) {
            return $example;
        }
    }

    return null;
}

function exam_example_preview_questions(array $fields): array
{
    $discipline = trim((string) ($fields['discipline'] ?? 'Língua Portuguesa'));
    $responseMode = (string) ($fields['response_mode'] ?? 'separate_answer_sheet');
    $compositionMode = (string) ($fields['composition_mode'] ?? 'mixed');
    $questions = [];
    $questionOptions = [];

    $addMultipleChoice = static function (int $id, string $title, string $prompt, array $options, string $source = '') use (&$questions, &$questionOptions): void {
        $questions[] = [
            'id' => $id,
            'title' => $title,
            'prompt' => $prompt,
            'question_type' => 'multiple_choice',
            'response_lines' => 0,
            'source_name' => $source,
            'prompt_image_url' => '',
        ];

        $questionOptions[$id] = array_map(
            static fn(string $text): array => ['option_text' => $text],
            $options
        );
    };

    $addDiscursive = static function (int $id, string $title, string $prompt, int $lines = 6, string $source = '') use (&$questions): void {
        $questions[] = [
            'id' => $id,
            'title' => $title,
            'prompt' => $prompt,
            'question_type' => 'discursive',
            'response_lines' => $lines,
            'source_name' => $source,
            'prompt_image_url' => '',
        ];
    };

    $introSource = $compositionMode === 'attachments' ? 'Texto-base da própria prova' : '';

    $addMultipleChoice(1, $discipline . ' - leitura', 'Leia o trecho apresentado e identifique a ideia principal desenvolvida no texto.', ['Tema central do texto', 'Nome do autor', 'Quantidade de parágrafos', 'Formato da impressão'], $introSource);
    $addMultipleChoice(2, $discipline . ' - interpretação', 'Assinale a alternativa que melhor apresenta uma conclusão coerente com o enunciado.', ['Conclusão compatível com os dados', 'Informação contraditória ao texto', 'Comentário fora do tema', 'Trecho sem relação com a proposta']);
    $addMultipleChoice(3, $discipline . ' - análise', 'Observe as informações e marque a alternativa correta.', ['A análise exige comparação entre dados', 'O enunciado não apresenta contexto', 'Não existe alternativa adequada', 'A questão depende de desenho técnico']);
    $addMultipleChoice(4, $discipline . ' - contexto', 'Considerando o conteúdo estudado, selecione a opção mais adequada.', ['Resposta construída a partir do conteúdo', 'Alternativa sem relação temática', 'Comentário opinativo sem base', 'Trecho repetido do cabeçalho']);
    $addMultipleChoice(5, $discipline . ' - aplicação', 'Em uma situação prática, qual solução demonstra melhor compreensão do tema?', ['Aplicação correta do conteúdo', 'Cópia literal sem interpretação', 'Resposta aleatória', 'Ausência de justificativa']);
    $addMultipleChoice(6, $discipline . ' - revisão', 'Marque a alternativa que melhor resume o conteúdo trabalhado em sala.', ['Síntese adequada do conteúdo', 'Informação sem vínculo com a aula', 'Exemplo de outra disciplina', 'Trecho incompleto']);

    if ($responseMode === 'discursive_space') {
        $addDiscursive(7, $discipline . ' - resposta argumentativa', 'Explique com suas palavras o conteúdo estudado e apresente um exemplo prático relacionado ao tema.', 8);
        $addDiscursive(8, $discipline . ' - produção escrita', 'Produza uma resposta organizada, com começo, desenvolvimento e conclusão, relacionando teoria e prática.', 10);
        $addDiscursive(9, $discipline . ' - reflexão final', 'Descreva o que você aprendeu e como esse conteúdo pode ser aplicado no cotidiano.', 8);
    } else {
        $addMultipleChoice(7, $discipline . ' - síntese', 'A partir da proposta apresentada, qual alternativa representa melhor a ideia final?', ['Síntese consistente', 'Conclusão sem base no texto', 'Comentário paralelo', 'Resposta incompleta']);
        $addMultipleChoice(8, $discipline . ' - comparação', 'Qual alternativa demonstra comparação adequada entre os elementos analisados?', ['Comparação coerente', 'Lista sem critério', 'Informação repetida', 'Opção sem ligação']);
        $addDiscursive(9, $discipline . ' - justificativa', 'Justifique sua resposta anterior usando pelo menos dois argumentos relacionados ao conteúdo estudado.', 7);
    }

    if (in_array($compositionMode, ['booklet', 'blocks_content', 'blocks_difficulty', 'modular', 'attachments'], true)) {
        $addMultipleChoice(10, $discipline . ' - aprofundamento', 'No bloco final da prova, marque a alternativa que apresenta maior domínio do conteúdo.', ['Aplicação ampliada do tema', 'Resposta superficial', 'Trecho sem relação', 'Comentário fora do bloco']);
        $addDiscursive(11, $discipline . ' - produção final', 'Elabore uma resposta final relacionando os pontos principais vistos na prova.', 8);
        $addMultipleChoice(12, $discipline . ' - fechamento', 'Assinale a alternativa que melhor representa o fechamento do conteúdo avaliado.', ['Fechamento coerente', 'Conclusão inconsistente', 'Informação sem contexto', 'Trecho isolado']);
    }

    return [
        'questions' => $questions,
        'question_options' => $questionOptions,
    ];
}

function exam_example_preview_payload(array $example): array
{
    $fields = exam_example_complete_fields($example['fields'] ?? []);
    $metadata = exam_collect_metadata($fields);
    $sections = [
        'header' => (string) ($fields['draft_header_content'] ?? ''),
        'body' => (string) ($fields['draft_body_content'] ?? ''),
        'footer' => (string) ($fields['draft_footer_content'] ?? ''),
    ];
    $previewQuestions = exam_example_preview_questions($fields);

    return [
        'exam' => [
            'id' => 0,
            'title' => (string) ($fields['draft_title'] ?? 'Modelo de prova'),
            'instructions' => exam_build_stored_content($metadata, $sections),
            'user_id' => 0,
        ],
        'questions' => $previewQuestions['questions'],
        'question_options' => $previewQuestions['question_options'],
        'fields' => $fields,
    ];
}
