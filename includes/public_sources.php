<?php
declare(strict_types=1);

function official_question_sources(): array
{
    return [
        'inep_enem_2025' => [
            'label' => 'Inep / Enem 2025 - Provas e gabaritos',
            'name' => 'Inep / Enem 2025',
            'url' => 'https://www.gov.br/inep/pt-br/areas-de-atuacao/avaliacao-e-exames-educacionais/enem/provas-e-gabaritos/2025',
        ],
        'inep_enem_2024' => [
            'label' => 'Inep / Enem 2024 - Provas e gabaritos',
            'name' => 'Inep / Enem 2024',
            'url' => 'https://www.gov.br/inep/pt-br/areas-de-atuacao/avaliacao-e-exames-educacionais/enem/provas-e-gabaritos/2024',
        ],
        'inep_enem_microdados' => [
            'label' => 'Inep / Microdados Enem',
            'name' => 'Inep / Microdados Enem',
            'url' => 'https://www.gov.br/inep/pt-br/acesso-a-informacao/dados-abertos/microdados/enem',
        ],
    ];
}
