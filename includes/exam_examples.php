<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';

function exam_example_default_fields(string $teacherName = ''): array
{
    $today = date('Y-m-d');
    $teacherName = trim($teacherName) !== '' ? trim($teacherName) : 'Professor(a) responsável';

    return [
        'draft_title' => 'Modelo coringa - prova completa',
        'exam_template' => 'version_1',
        'exam_style' => 'double_column',
        'response_mode' => 'separate_answer_sheet',
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
        'header_logo_size' => '80',
        'header_min_height' => '120',
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
        $completed['draft_title'] = 'Modelo coringa - prova completa';
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
            'slug' => 'modelo-coringa',
            'title' => 'Modelo coringa',
            'description' => 'Modelo completo, com todos os campos preenchidos, pronto para usar mesmo sem informar nada antes.',
            'tags' => ['Completo', 'Pronto para uso', 'Coringa'],
            'fields' => [
                'draft_title' => 'Modelo coringa - prova completa',
                'discipline' => 'Língua Portuguesa',
                'component_name' => 'Língua Portuguesa',
                'exam_label' => 'SIMULADO BIMESTRAL',
                'class_name' => '6A',
                'year_reference' => '6º ano',
            ],
        ],
        [
            'slug' => 'enem-dupla',
            'title' => 'Coluna dupla (padrão ENEM)',
            'description' => 'Modelo objetivo em duas colunas, ideal para provas extensas.',
            'tags' => ['Layout', 'Objetiva', 'ENEM'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova coluna dupla',
                'exam_style' => 'double_column',
            ],
        ],
        [
            'slug' => 'coluna-simples',
            'title' => 'Coluna simples',
            'description' => 'Leitura linear, boa para provas mais tradicionais.',
            'tags' => ['Layout', 'Linear'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova coluna simples',
                'exam_style' => 'single_column',
            ],
        ],
        [
            'slug' => 'economica',
            'title' => 'Estilo econômico',
            'description' => 'Mais compacto para economizar páginas e impressão.',
            'tags' => ['Layout', 'Compacta'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova econômica',
                'exam_style' => 'economic',
            ],
        ],
        [
            'slug' => 'acessibilidade',
            'title' => 'Modo acessibilidade',
            'description' => 'Fonte maior e espaçamento ampliado para leitura confortável.',
            'tags' => ['Layout', 'Acessível'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova acessível',
                'exam_style' => 'accessibility',
                'draft_header_content' => 'Use este modelo quando a prova precisar de leitura ampliada.',
            ],
        ],
        [
            'slug' => 'gabarito-lateral',
            'title' => 'Prova com gabarito lateral',
            'description' => 'Alternativas com marcação lateral ao lado das questões objetivas.',
            'tags' => ['Resposta', 'Lateral'],
            'fields' => [
                'draft_title' => 'Exemplo - Gabarito lateral',
                'response_mode' => 'lateral_answer_key',
            ],
        ],
        [
            'slug' => 'folha-separada',
            'title' => 'Prova com folha de resposta separada',
            'description' => 'Questões na prova e marcação final em folha separada.',
            'tags' => ['Resposta', 'Separada'],
            'fields' => [
                'draft_title' => 'Exemplo - Folha de resposta separada',
                'response_mode' => 'separate_answer_sheet',
            ],
        ],
        [
            'slug' => 'discursiva',
            'title' => 'Prova discursiva com espaço para resposta',
            'description' => 'Modelo pensado para respostas escritas com linhas no próprio enunciado.',
            'tags' => ['Resposta', 'Discursiva'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova discursiva',
                'response_mode' => 'discursive_space',
                'composition_mode' => 'mixed',
                'discipline' => 'Produção de Texto',
                'draft_body_content' => 'Desenvolva as respostas com clareza, organização e argumentação.',
            ],
        ],
        [
            'slug' => 'mista',
            'title' => 'Prova mista (objetiva + discursiva)',
            'description' => 'Combina questões objetivas e discursivas no mesmo caderno.',
            'tags' => ['Composição', 'Mista'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova mista',
                'composition_mode' => 'mixed',
            ],
        ],
        [
            'slug' => 'caderno',
            'title' => 'Prova estilo caderno',
            'description' => 'Pensada para várias páginas, com sequência contínua de leitura.',
            'tags' => ['Composição', 'Caderno'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova estilo caderno',
                'composition_mode' => 'booklet',
                'draft_footer_content' => 'Vire a página com atenção e mantenha a sequência da prova.',
            ],
        ],
        [
            'slug' => 'blocos',
            'title' => 'Prova por blocos',
            'description' => 'Organizada por conteúdo ou dificuldade em blocos distintos.',
            'tags' => ['Composição', 'Blocos'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova por blocos',
                'composition_mode' => 'blocks_content',
                'draft_body_content' => "Bloco 1: conteúdos básicos.\nBloco 2: aplicação.\nBloco 3: desafios.",
            ],
        ],
        [
            'slug' => 'embaralhada',
            'title' => 'Prova com embaralhamento de questões',
            'description' => 'Mantém a montagem, mas embaralha a ordem das questões no salvamento.',
            'tags' => ['Organização', 'Embaralhada'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova embaralhada',
                'ordering_mode' => 'shuffle_questions',
            ],
        ],
        [
            'slug' => 'versao-a',
            'title' => 'Prova com versões (A, B, C, D...)',
            'description' => 'Modelo configurado para trabalhar com identificação por versão.',
            'tags' => ['Organização', 'Versão A'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova versão A',
                'ordering_mode' => 'variants',
                'variant_label' => 'A',
            ],
        ],
        [
            'slug' => 'banco-dinamico',
            'title' => 'Prova com banco de questões dinâmico',
            'description' => 'Estrutura preparada para montagem orientada pelo banco de questões.',
            'tags' => ['Composição', 'Banco dinâmico'],
            'fields' => [
                'draft_title' => 'Exemplo - Banco dinâmico',
                'composition_mode' => 'dynamic_bank',
                'draft_body_content' => 'Selecione questões do banco por tema, dificuldade e objetivo.',
            ],
        ],
        [
            'slug' => 'numeracao-automatica',
            'title' => 'Prova com numeração automática',
            'description' => 'Numeração contínua das questões, sem intervenção manual.',
            'tags' => ['Organização', 'Numeração'],
            'fields' => [
                'draft_title' => 'Exemplo - Numeração automática',
                'ordering_mode' => 'automatic_numbering',
            ],
        ],
        [
            'slug' => 'modular',
            'title' => 'Prova modular (por competências/habilidades)',
            'description' => 'Estrutura com foco em módulos ou competências avaliadas.',
            'tags' => ['Composição', 'Modular'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova modular',
                'composition_mode' => 'modular',
                'draft_body_content' => "Módulo 1: interpretação.\nMódulo 2: resolução.\nMódulo 3: argumentação.",
            ],
        ],
        [
            'slug' => 'anexos',
            'title' => 'Prova com anexos (tabelas, textos, gráficos)',
            'description' => 'Preparada para trabalhar com materiais de apoio na abertura da prova.',
            'tags' => ['Composição', 'Anexos'],
            'fields' => [
                'draft_title' => 'Exemplo - Prova com anexos',
                'composition_mode' => 'attachments',
                'draft_body_content' => 'Anexo 1: tabela de dados.\nAnexo 2: texto-base.\nAnexo 3: gráfico de apoio.',
            ],
        ],
        [
            'slug' => 'bolinhas',
            'title' => 'Prova com leitura por gabarito (bolinhas)',
            'description' => 'Folha de respostas preparada em formato de marcação circular.',
            'tags' => ['Resposta', 'Bolinhas'],
            'fields' => [
                'draft_title' => 'Exemplo - Gabarito por bolinhas',
                'response_mode' => 'bubble_answer_sheet',
            ],
        ],
        [
            'slug' => 'qr-code',
            'title' => 'Prova com QR Code de identificação',
            'description' => 'Configuração com identificação da prova preparada para QR Code.',
            'tags' => ['Identificação', 'QR Code'],
            'fields' => [
                'draft_title' => 'Exemplo - Identificação por QR Code',
                'identification_mode' => 'qr_code',
                'draft_header_content' => 'Mantenha o identificador visível durante toda a aplicação da prova.',
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
