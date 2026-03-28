<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';

function exam_example_presets(string $teacherName = ''): array
{
    $today = date('Y-m-d');
    $teacherName = trim($teacherName) !== '' ? trim($teacherName) : 'Professor(a) responsável';
    $schoolName = EXAM_DEFAULT_SCHOOL_NAME;

    $base = [
        'exam_template' => 'version_1',
        'exam_style' => 'double_column',
        'response_mode' => 'separate_answer_sheet',
        'composition_mode' => 'mixed',
        'ordering_mode' => 'automatic_numbering',
        'identification_mode' => 'standard',
        'variant_label' => '',
        'exam_label' => 'AVALIAÇÃO TRIMESTRAL',
        'discipline' => 'Língua Portuguesa',
        'component_name' => '',
        'teacher_name' => $teacherName,
        'school_name' => $schoolName,
        'year_reference' => '6º ano',
        'class_name' => '6A',
        'application_date' => $today,
        'draft_header_content' => 'Leia atentamente cada questão antes de responder.',
        'draft_body_content' => 'Organize seu tempo e revise as respostas antes de entregar.',
        'draft_footer_content' => 'Confira nome, turma e se todas as questões foram respondidas.',
    ];

    $examples = [
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
        $example['fields'] = array_merge($base, $example['fields']);
        return $example;
    }, $examples);
}

function exam_example_query(array $example, array $overrides = []): array
{
    return array_merge($example['fields'] ?? [], $overrides);
}
