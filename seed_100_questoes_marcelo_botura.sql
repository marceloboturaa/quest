USE `u488847015_quest_baseDado`;

SET @marcelo_user_id := (
    SELECT `id`
    FROM `users`
    WHERE `email` = 'mbsfoz@gmail.com' OR `name` = 'Marcelo Botura'
    ORDER BY (`email` = 'mbsfoz@gmail.com') DESC, `id` ASC
    LIMIT 1
);

INSERT INTO `users` (`name`, `email`, `password_hash`, `role`)
SELECT 'Marcelo Botura', 'mbsfoz@gmail.com', '$2y$10$ltNnvu3eUyb7tO.7Akaj5OmhSsip8XjWcGJvmH3yShplX4DSYleB.', 'user'
WHERE @marcelo_user_id IS NULL;

SET @marcelo_user_id := COALESCE(
    @marcelo_user_id,
    (
        SELECT `id`
        FROM `users`
        WHERE `email` = 'mbsfoz@gmail.com' OR `name` = 'Marcelo Botura'
        ORDER BY (`email` = 'mbsfoz@gmail.com') DESC, `id` ASC
        LIMIT 1
    )
);

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Matematica', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Matematica'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Porcentagem', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Matematica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Porcentagem'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Algebra', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Matematica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Algebra'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Geometria', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Matematica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Geometria'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Estatistica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Matematica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Estatistica'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Portugues', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Portugues'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Gramatica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Portugues'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Gramatica'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Ortografia', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Portugues'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Ortografia'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Interpretacao de Texto', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Portugues'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Interpretacao de Texto'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Fisica', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Fisica'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Cinematica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Fisica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Cinematica'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Dinamica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Fisica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Dinamica'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Energia', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Fisica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Energia'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Eletricidade', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Fisica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Eletricidade'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Termologia', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Fisica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Termologia'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Optica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Fisica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Optica'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Ondulatoria', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Fisica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Ondulatoria'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Historia', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Historia'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Historia do Brasil', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Historia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Historia do Brasil'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Historia Geral', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Historia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Historia Geral'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Geografia', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Geografia'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Recursos Naturais', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Geografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Recursos Naturais'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Cartografia', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Geografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Cartografia'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Geografia Humana', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Geografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Geografia Humana'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Climatologia', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Geografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Climatologia'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Biologia', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Biologia'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Citologia', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Biologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Citologia'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Genetica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Biologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Genetica'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Ecologia', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Biologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Ecologia'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Corpo Humano', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Biologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Corpo Humano'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Quimica', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Quimica'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Quimica Geral', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Quimica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Quimica Geral'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Quimica Organica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Quimica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Quimica Organica'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Educacao Financeira', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Educacao Financeira'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Orcamento', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Orcamento'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Juros e Investimentos', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Juros e Investimentos'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Consumo Consciente', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Consumo Consciente'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Informatica', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Informatica'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Computacao Basica', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Informatica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Computacao Basica'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Internet e Seguranca', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Informatica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Internet e Seguranca'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Logica e Dados', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Informatica'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Logica e Dados'
  );

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Ingles', @marcelo_user_id
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Ingles'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Vocabulary', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Ingles'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Vocabulary'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Grammar', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Ingles'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Grammar'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT `disciplines`.`id`, 'Reading', @marcelo_user_id
FROM `disciplines`
WHERE `disciplines`.`name` = 'Ingles'
  AND NOT EXISTS (
      SELECT 1
      FROM `subjects`
      WHERE `subjects`.`discipline_id` = `disciplines`.`id`
        AND `subjects`.`name` = 'Reading'
  );

-- Questao 1: Porcentagem de desconto
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Porcentagem de desconto',
    'Um produto custa R$ 200,00 e recebeu desconto de 15%. Qual e o novo preco?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Porcentagem'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Porcentagem de desconto'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Porcentagem de desconto'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 170,00',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 175,00',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 180,00',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 185,00',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 2: Resolucao de equacao simples
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Resolucao de equacao simples',
    'Qual e o valor de x na equacao 2x + 6 = 18?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Algebra'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Resolucao de equacao simples'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Resolucao de equacao simples'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '4',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '5',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '6',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '7',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 3: Area do retangulo
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Area do retangulo',
    'Um retangulo possui 8 cm de base e 5 cm de altura. Qual e a sua area?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Geometria'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Area do retangulo'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Area do retangulo'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '13 cm2',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '20 cm2',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '40 cm2',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '45 cm2',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 4: Media aritmetica
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Media aritmetica',
    'Qual e a media dos numeros 6, 8, 10 e 6?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Estatistica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Media aritmetica'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Media aritmetica'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '7',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '7,5',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '8',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '8,5',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 5: Fracao equivalente
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Fracao equivalente',
    'Qual das opcoes representa uma fracao equivalente a 3/4?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Porcentagem'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Fracao equivalente'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Fracao equivalente'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '6/10',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '9/16',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '12/16',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '15/25',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 6: Regra de tres direta
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Regra de tres direta',
    'Se 3 cadernos custam R$ 12,00, quanto custam 5 cadernos, mantendo o mesmo preco unitario?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Algebra'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Regra de tres direta'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Regra de tres direta'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 18,00',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 20,00',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 22,00',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 24,00',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 7: Selecao de numeros primos
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Selecao de numeros primos',
    'Marque todos os numeros primos entre 1 e 10.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Algebra'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Selecao de numeros primos'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Selecao de numeros primos'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '2',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '3',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '5',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '9',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 8: Numero primo e par
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Numero primo e par',
    'Todo numero primo e impar.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Geometria'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Numero primo e par'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Numero primo e par'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 9: Explicacao sobre porcentagem
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Explicacao sobre porcentagem',
    'Explique com suas palavras a diferenca entre porcentagem e proporcao e cite um exemplo do cotidiano.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Porcentagem'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Explicacao sobre porcentagem'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Explicacao sobre porcentagem'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 10: Desenho de figura geometrica
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Desenho de figura geometrica',
    'Desenhe um quadrado, marque seus vertices e trace uma diagonal ligando dois vertices opostos.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Matematica'
  AND `subjects`.`name` = 'Geometria'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Desenho de figura geometrica'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Desenho de figura geometrica'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 11: Tempo verbal
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Tempo verbal',
    'Na frase "Os alunos chegaram cedo", o verbo esta em qual tempo?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Gramatica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Tempo verbal'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Tempo verbal'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Presente',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Futuro',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Preterito',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Imperativo',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 12: Plural de cidadao
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Plural de cidadao',
    'Qual e o plural correto da palavra "cidadao"?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Ortografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Plural de cidadao'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Plural de cidadao'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cidadaos',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cidadoes',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cidadaes',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cidadaoses',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 13: Identificacao do sujeito
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Identificacao do sujeito',
    'Na frase "A professora explicou a atividade", qual e o sujeito da oracao?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Gramatica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Identificacao do sujeito'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Identificacao do sujeito'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Explicou',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'A atividade',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'A professora',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Professora explicou',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 14: Uso de mas e mais
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Uso de mas e mais',
    'Complete corretamente: "Eu queria sair, ____ estava chovendo muito."',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Ortografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Uso de mas e mais'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Uso de mas e mais'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'mais',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'mas',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'maes',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'maiz',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 15: Sinonimo contextual
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Sinonimo contextual',
    'Qual palavra pode substituir "feliz" sem mudar o sentido principal da frase?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Interpretacao de Texto'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Sinonimo contextual'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Sinonimo contextual'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Contente',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Rapido',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Escuro',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Distante',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 16: Classificacao gramatical
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Classificacao gramatical',
    'Na frase "Pedro correu rapidamente", a palavra "rapidamente" e classificada como:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Gramatica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Classificacao gramatical'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Classificacao gramatical'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Substantivo',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Adverbio',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Adjetivo',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Pronome',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 17: Identificacao de adverbios
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Identificacao de adverbios',
    'Marque todas as palavras que sao adverbios.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Gramatica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Identificacao de adverbios'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Identificacao de adverbios'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Ontem',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Rapidamente',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Casa',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Talvez',
    1,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 18: Regra de acentuacao
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Regra de acentuacao',
    'A palavra "excecao" e acentuada por ser oxitona terminada em "o".',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Ortografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Regra de acentuacao'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Regra de acentuacao'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 19: Importancia da leitura
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Importancia da leitura',
    'Explique por que o habito da leitura contribui para a aprendizagem e para a comunicacao.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Interpretacao de Texto'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Importancia da leitura'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Importancia da leitura'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 20: Mapa mental de narrativa
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Mapa mental de narrativa',
    'Organize em forma de esquema ou desenho os elementos de uma narrativa: personagem, tempo, espaco e conflito.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Portugues'
  AND `subjects`.`name` = 'Interpretacao de Texto'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Mapa mental de narrativa'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Mapa mental de narrativa'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 21: Velocidade media
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Velocidade media',
    'Um carro percorre 120 km em 2 horas. Qual e a velocidade media?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Cinematica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Velocidade media'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Velocidade media'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '40 km/h',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '50 km/h',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '60 km/h',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '80 km/h',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 22: Segunda lei de Newton
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Segunda lei de Newton',
    'Uma forca de 6 N atua em um corpo de massa 2 kg. Qual e a aceleracao produzida?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Dinamica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Segunda lei de Newton'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Segunda lei de Newton'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '2 m/s2',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '3 m/s2',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '4 m/s2',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '12 m/s2',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 23: Fonte renovavel
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Fonte renovavel',
    'Qual das alternativas apresenta uma fonte de energia renovavel?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Energia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Fonte renovavel'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Fonte renovavel'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Carvao mineral',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Petroleo',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Energia solar',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Gas natural',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 24: Unidade de corrente eletrica
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Unidade de corrente eletrica',
    'A unidade de medida da corrente eletrica no Sistema Internacional e:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Eletricidade'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Unidade de corrente eletrica'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Unidade de corrente eletrica'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Volt',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Watt',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Ampere',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Ohm',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 25: Transferencia de calor
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Transferencia de calor',
    'Quando o calor passa por contato direto entre corpos, ocorre:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Termologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Transferencia de calor'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Transferencia de calor'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Conveccao',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Radiacao',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Conducao',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Reflexao',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 26: Imagem em espelho plano
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Imagem em espelho plano',
    'A imagem formada em um espelho plano e:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Optica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Imagem em espelho plano'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Imagem em espelho plano'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Real e invertida',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Virtual e direita',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Real e aumentada',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Virtual e invertida',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 27: Fontes renovaveis de energia
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Fontes renovaveis de energia',
    'Marque todas as fontes de energia renovaveis.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Energia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Fontes renovaveis de energia'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Fontes renovaveis de energia'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Solar',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Eolica',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Petroleo',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Carvao mineral',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 28: Propagacao do som
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Propagacao do som',
    'No vacuo, o som se propaga normalmente.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Ondulatoria'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Propagacao do som'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Propagacao do som'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 29: Massa e peso
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Massa e peso',
    'Explique a diferenca entre massa e peso e indique uma unidade usada para cada grandeza.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Dinamica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Massa e peso'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Massa e peso'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 30: Esquema de circuito simples
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Esquema de circuito simples',
    'Desenhe um circuito simples com uma bateria, um interruptor e uma lampada ligados em serie.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Fisica'
  AND `subjects`.`name` = 'Eletricidade'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Esquema de circuito simples'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Esquema de circuito simples'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 31: Independencia do Brasil
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Independencia do Brasil',
    'Em que ano foi proclamada a Independencia do Brasil?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia do Brasil'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Independencia do Brasil'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Independencia do Brasil'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1500',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1822',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1889',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1930',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 32: Lema da Revolucao Francesa
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Lema da Revolucao Francesa',
    'Qual expressao representa o lema associado a Revolucao Francesa?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Lema da Revolucao Francesa'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Lema da Revolucao Francesa'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Ordem e Progresso',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Liberdade, Igualdade e Fraternidade',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Paz, Terra e Pao',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Fe, Familia e Trabalho',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 33: Abolicao da escravidao
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Abolicao da escravidao',
    'Em que ano foi assinada a Lei Aurea no Brasil?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia do Brasil'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Abolicao da escravidao'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Abolicao da escravidao'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1822',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1888',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1889',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1937',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 34: Revolucao Industrial
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Revolucao Industrial',
    'A Revolucao Industrial teve inicio em qual pais?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Revolucao Industrial'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Revolucao Industrial'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Franca',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Alemanha',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Inglaterra',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Italia',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 35: Queda do Muro de Berlim
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Queda do Muro de Berlim',
    'A queda do Muro de Berlim ocorreu em:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Queda do Muro de Berlim'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Queda do Muro de Berlim'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1945',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1961',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1989',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '2001',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 36: Proclamacao da Republica
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Proclamacao da Republica',
    'A Proclamacao da Republica no Brasil aconteceu em:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia do Brasil'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Proclamacao da Republica'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Proclamacao da Republica'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1789',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1822',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1889',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    '1930',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 37: Eventos do seculo XX
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Eventos do seculo XX',
    'Marque os acontecimentos que ocorreram no seculo XX.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'superior',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Eventos do seculo XX'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Eventos do seculo XX'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Primeira Guerra Mundial',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Independencia do Brasil',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Queda do Muro de Berlim',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Descobrimento do Brasil',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 38: Ordem das guerras mundiais
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Ordem das guerras mundiais',
    'A Primeira Guerra Mundial ocorreu depois da Segunda Guerra Mundial.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Ordem das guerras mundiais'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Ordem das guerras mundiais'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 39: Consequencias da Revolucao Industrial
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Consequencias da Revolucao Industrial',
    'Descreva duas consequencias sociais da Revolucao Industrial para a vida dos trabalhadores.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Consequencias da Revolucao Industrial'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Consequencias da Revolucao Industrial'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 40: Linha do tempo do Brasil
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Linha do tempo do Brasil',
    'Monte uma linha do tempo simples com os marcos: Descobrimento, Independencia e Proclamacao da Republica.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    'large',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Historia'
  AND `subjects`.`name` = 'Historia do Brasil'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Linha do tempo do Brasil'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Linha do tempo do Brasil'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 41: Maior bioma brasileiro
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Maior bioma brasileiro',
    'Qual e o maior bioma brasileiro em extensao territorial?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Recursos Naturais'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Maior bioma brasileiro'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Maior bioma brasileiro'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Caatinga',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Amazonia',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Pampa',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Pantanal',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 42: Latitude
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Latitude',
    'A latitude mede a distancia de um ponto em relacao a:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Cartografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Latitude'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Latitude'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Meridiano de Greenwich',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Linha do Equador',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Tropico de Capricornio',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Nivel do mar',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 43: Urbanizacao
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Urbanizacao',
    'O processo de urbanizacao corresponde principalmente ao:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Geografia Humana'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Urbanizacao'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Urbanizacao'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Aumento da populacao nas cidades',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Crescimento das areas rurais',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Desmatamento da floresta',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Resfriamento global',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 44: Matriz energetica renovavel
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Matriz energetica renovavel',
    'Qual fonte faz parte da matriz energetica renovavel?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Recursos Naturais'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Matriz energetica renovavel'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Matriz energetica renovavel'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Carvao',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Petroleo',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Energia eolica',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Diesel',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 45: Camada da atmosfera
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Camada da atmosfera',
    'Os principais fenomenos meteorologicos ocorrem em qual camada da atmosfera?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Climatologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Camada da atmosfera'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Camada da atmosfera'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Estratosfera',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Troposfera',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Mesosfera',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Termosfera',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 46: Uso do GPS
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Uso do GPS',
    'O sistema GPS funciona com base em:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Cartografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Uso do GPS'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Uso do GPS'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cabos subterraneos',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Satellites',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Imas terrestres',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Placas tectonicas',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 47: Fontes renovaveis
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Fontes renovaveis',
    'Marque todas as fontes renovaveis de energia.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Recursos Naturais'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Fontes renovaveis'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Fontes renovaveis'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Solar',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Eolica',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Petroleo',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Carvao',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 48: Hemisferio brasileiro
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Hemisferio brasileiro',
    'O Brasil esta totalmente no Hemisferio Norte.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Cartografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Hemisferio brasileiro'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Hemisferio brasileiro'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 49: Crescimento urbano
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Crescimento urbano',
    'Explique um problema gerado pelo crescimento urbano desordenado e uma possivel solucao.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Geografia Humana'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Crescimento urbano'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Crescimento urbano'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 50: Rosa dos ventos
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Rosa dos ventos',
    'Desenhe uma rosa dos ventos simples identificando norte, sul, leste e oeste.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Geografia'
  AND `subjects`.`name` = 'Cartografia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Rosa dos ventos'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Rosa dos ventos'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 51: Unidade basica da vida
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Unidade basica da vida',
    'Qual e a unidade basica dos seres vivos?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Citologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Unidade basica da vida'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Unidade basica da vida'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Tecido',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Organela',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Celula',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Molecula',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 52: Local da fotossintese
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Local da fotossintese',
    'Em qual estrutura celular ocorre a fotossintese?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Citologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Local da fotossintese'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Local da fotossintese'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Mitocondria',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cloroplasto',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Nucleo',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Ribossomo',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 53: Material genetico
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Material genetico',
    'Qual molecula carrega a maior parte das informacoes geneticas dos seres vivos?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Genetica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Material genetico'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Material genetico'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Proteina',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Lipidios',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'DNA',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Agua',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 54: Produtores na cadeia alimentar
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Produtores na cadeia alimentar',
    'Em uma cadeia alimentar, os produtores sao geralmente:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Ecologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Produtores na cadeia alimentar'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Produtores na cadeia alimentar'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Fungos',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Animais carnivoros',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Plantas e algas',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Bacterias decompositoras',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 55: Orgão do sistema circulatorio
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Orgão do sistema circulatorio',
    'Qual orgao bombeia o sangue pelo corpo humano?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Corpo Humano'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Orgão do sistema circulatorio'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Orgão do sistema circulatorio'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Pulmao',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Figado',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Coracao',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Rim',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 56: Funcao da vacina
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Funcao da vacina',
    'A vacina ajuda o organismo principalmente porque:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Corpo Humano'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Funcao da vacina'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Funcao da vacina'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Destroi todos os virus do ambiente',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Estimula o sistema imunologico',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Substitui a alimentacao saudavel',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Aumenta a temperatura corporal',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 57: Componentes do sistema respiratorio
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Componentes do sistema respiratorio',
    'Marque os orgaos e estruturas ligados ao sistema respiratorio.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Corpo Humano'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Componentes do sistema respiratorio'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Componentes do sistema respiratorio'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Pulmoes',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Traqueia',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Femur',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Bronquios',
    1,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 58: Quantidade de celulas
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Quantidade de celulas',
    'Todo ser vivo e formado por apenas uma celula.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Citologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Quantidade de celulas'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Quantidade de celulas'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 59: Importancia da biodiversidade
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Importancia da biodiversidade',
    'Explique por que a biodiversidade e importante para o equilibrio dos ecossistemas.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Ecologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Importancia da biodiversidade'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Importancia da biodiversidade'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 60: Esquema de celula animal
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Esquema de celula animal',
    'Desenhe uma celula animal simples identificando membrana, citoplasma e nucleo.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Biologia'
  AND `subjects`.`name` = 'Citologia'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Esquema de celula animal'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Esquema de celula animal'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 61: Numero atomico
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Numero atomico',
    'O numero atomico de um elemento corresponde a quantidade de:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Numero atomico'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Numero atomico'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Neutrons',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Protons',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Eletrons de valencia',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Moleculas',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 62: Escala de pH
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Escala de pH',
    'Uma substancia com pH menor que 7 e considerada:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Escala de pH'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Escala de pH'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Basica',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Neutra',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Acida',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Metalica',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 63: Formula da agua
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Formula da agua',
    'Qual e a formula quimica da agua?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Formula da agua'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Formula da agua'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'CO2',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'O2',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'H2O',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'NaCl',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 64: Separacao de misturas
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Separacao de misturas',
    'Qual processo pode ser usado para separar agua e areia?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Separacao de misturas'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Separacao de misturas'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Filtracao',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Destilacao fracionada',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Sublimacao',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cromatografia',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 65: Metal alcalino
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Metal alcalino',
    'Qual dos elementos abaixo pertence aos metais alcalinos?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Metal alcalino'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Metal alcalino'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Sodio',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Ferro',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Oxigenio',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cloro',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 66: Compostos organicos
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Compostos organicos',
    'Os compostos organicos sao caracterizados principalmente pela presenca de:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Organica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Compostos organicos'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Compostos organicos'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Carbono',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Helio',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Sodio',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Prata',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 67: Mudancas fisicas
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Mudancas fisicas',
    'Marque os exemplos de mudanca fisica.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Mudancas fisicas'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Mudancas fisicas'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Derreter gelo',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Rasgar papel',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Queimar madeira',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Evaporar agua',
    1,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 68: Mistura homogenea
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Mistura homogenea',
    'Mistura homogenea apresenta apenas uma fase visivel.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    1,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Mistura homogenea'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Mistura homogenea'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 69: Transformacoes da materia
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Transformacoes da materia',
    'Explique a diferenca entre transformacao fisica e transformacao quimica e cite um exemplo de cada.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Transformacoes da materia'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Transformacoes da materia'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 70: Modelo atomico simples
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Modelo atomico simples',
    'Desenhe um modelo atomico simples indicando nucleo e eletrosfera.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Quimica'
  AND `subjects`.`name` = 'Quimica Geral'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Modelo atomico simples'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Modelo atomico simples'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 71: Reserva de emergencia
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Reserva de emergencia',
    'A reserva de emergencia serve principalmente para:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Orcamento'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Reserva de emergencia'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Reserva de emergencia'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Comprar itens de luxo',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cobrir imprevistos',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Pagar apenas lazer',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Substituir salario fixo para sempre',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 72: Juros simples
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Juros simples',
    'Qual e o juro simples gerado por R$ 1.000,00 a 10% ao mes durante 1 mes?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Juros e Investimentos'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Juros simples'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Juros simples'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 10,00',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 50,00',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 100,00',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 110,00',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 73: Juros compostos
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Juros compostos',
    'Aplicando R$ 1.000,00 a 10% ao mes por 2 meses em juros compostos, o montante sera:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Juros e Investimentos'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Juros compostos'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Juros compostos'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 1.100,00',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 1.200,00',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 1.210,00',
    1,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'R$ 1.220,00',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 74: Conceito de orcamento
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Conceito de orcamento',
    'Orcamento financeiro e o planejamento de:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Orcamento'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Conceito de orcamento'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Conceito de orcamento'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Receitas e despesas',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Apenas investimentos',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Apenas impostos',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Somente compras parceladas',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 75: Cartao de credito
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Cartao de credito',
    'Pagar somente o valor minimo da fatura do cartao geralmente e arriscado porque:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Consumo Consciente'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Cartao de credito'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Cartao de credito'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Diminui o limite para sempre',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Gera cobranca de juros altos',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cancela automaticamente o cartao',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Elimina o historico de compras',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 76: Inflacao
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Inflacao',
    'A inflacao reduz principalmente:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Consumo Consciente'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Inflacao'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Inflacao'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'A velocidade da internet',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'O poder de compra',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'O numero de empregos automaticamente',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'A conta de energia',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 77: Despesas fixas
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Despesas fixas',
    'Marque as despesas normalmente consideradas fixas em um orcamento mensal.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Orcamento'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Despesas fixas'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Despesas fixas'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Aluguel',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Internet',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Cinema eventual',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Mensalidade escolar',
    1,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 78: Parcelamento sempre e vantajoso
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Parcelamento sempre e vantajoso',
    'Parcelar uma compra sempre significa pagar menos.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Consumo Consciente'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Parcelamento sempre e vantajoso'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Parcelamento sempre e vantajoso'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 79: Importancia de poupar
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Importancia de poupar',
    'Explique por que poupar antes de consumir pode ajudar na organizacao da vida financeira.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Orcamento'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Importancia de poupar'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Importancia de poupar'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 80: Quadro de orcamento mensal
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Quadro de orcamento mensal',
    'Monte um quadro ou desenho simples separando receitas, despesas fixas, despesas variaveis e poupanca.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    'custom',
    260,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Educacao Financeira'
  AND `subjects`.`name` = 'Orcamento'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Quadro de orcamento mensal'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Quadro de orcamento mensal'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 81: Conceito de hardware
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Conceito de hardware',
    'Hardware corresponde a:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Computacao Basica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Conceito de hardware'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Conceito de hardware'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Parte fisica do computador',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Programa de edicao de texto',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Conta de e-mail',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Somente arquivos da nuvem',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 82: Senha forte
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Senha forte',
    'Uma senha forte deve combinar preferencialmente:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Internet e Seguranca'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Senha forte'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Senha forte'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Somente letras minusculas',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Letras, numeros e simbolos',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Apenas a data de nascimento',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'O nome completo',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 83: Phishing
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Phishing',
    'Phishing e uma tentativa de:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Internet e Seguranca'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Phishing'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Phishing'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Acelerar o computador',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Roubar dados com mensagens falsas',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Atualizar a impressora',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Criar backup automatico',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 84: Uso da planilha
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Uso da planilha',
    'Uma planilha eletronica e muito usada para:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Computacao Basica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Uso da planilha'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Uso da planilha'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Editar videos',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Fazer calculos e organizar dados',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Fabricar computadores',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Enviar sinal de TV',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 85: Exemplo de navegador
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Exemplo de navegador',
    'Qual opcao abaixo e um navegador de internet?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Computacao Basica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Exemplo de navegador'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Exemplo de navegador'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Chrome',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Excel',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Windows',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'SQL',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 86: Banco de dados
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Banco de dados',
    'Um banco de dados serve para:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Logica e Dados'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Banco de dados'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Banco de dados'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Guardar dados de forma organizada',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Aumentar a memoria RAM fisicamente',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Substituir a internet',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Apagar historico escolar',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 87: Boas praticas de seguranca digital
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Boas praticas de seguranca digital',
    'Marque as boas praticas de seguranca digital.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Internet e Seguranca'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Boas praticas de seguranca digital'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Boas praticas de seguranca digital'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Usar senha unica para cada servico',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Ativar autenticacao em dois fatores',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Compartilhar senha com colegas',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Misturar letras, numeros e simbolos',
    1,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 88: Compartilhamento de senha
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Compartilhamento de senha',
    'E seguro compartilhar sua senha por mensagem com qualquer colega.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Internet e Seguranca'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Compartilhamento de senha'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Compartilhamento de senha'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 89: Importancia do backup
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Importancia do backup',
    'Explique por que fazer backup periodico ajuda na seguranca e continuidade do trabalho.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Logica e Dados'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Importancia do backup'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Importancia do backup'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 90: Rede domestica simples
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Rede domestica simples',
    'Desenhe uma rede domestica simples com modem, roteador e dois dispositivos conectados.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Informatica'
  AND `subjects`.`name` = 'Computacao Basica'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Rede domestica simples'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Rede domestica simples'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 91: Traducao de book
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Traducao de book',
    'A palavra "book" significa:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Vocabulary'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Traducao de book'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Traducao de book'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Mesa',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Livro',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Escola',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Janela',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 92: Uso do verbo to be
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Uso do verbo to be',
    'Complete: "She ___ a student."',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Grammar'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Uso do verbo to be'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Uso do verbo to be'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'am',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'is',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'are',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'be',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 93: Plural irregular
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Plural irregular',
    'Qual e o plural de "child"?',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Vocabulary'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Plural irregular'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Plural irregular'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'childs',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'children',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'childes',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'childrens',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 94: Antonimo simples
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Antonimo simples',
    'O oposto de "hot" em ingles e:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Vocabulary'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Antonimo simples'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Antonimo simples'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'cold',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'tall',
    0,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'short',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'late',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 95: Uso de can
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Uso de can',
    'Na frase "I can swim", a palavra "can" indica:',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Grammar'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Uso de can'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Uso de can'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Tempo passado',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Permissao ou habilidade',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Lugar',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Plural',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 96: Preposicao de tempo
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Preposicao de tempo',
    'Complete corretamente: "Classes start ___ Monday."',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Grammar'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Preposicao de tempo'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Preposicao de tempo'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'in',
    0,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'on',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'at',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'for',
    0,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 97: Cores em ingles
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Cores em ingles',
    'Marque as palavras que sao cores em ingles.',
    NULL,
    'multiple_choice',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'medio',
    'published',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Vocabulary'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Cores em ingles'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Cores em ingles'
    ORDER BY `id` DESC
    LIMIT 1
);

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Blue',
    1,
    1
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 1
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Red',
    1,
    2
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 2
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Table',
    0,
    3
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 3
  );

INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)
SELECT
    @question_id,
    'Green',
    1,
    4
FROM DUAL
WHERE @question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `question_options`
      WHERE `question_options`.`question_id` = @question_id
        AND `question_options`.`display_order` = 4
  );

-- Questao 98: Significado de house
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Significado de house',
    'A palavra "house" significa carro.',
    NULL,
    'true_false',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Vocabulary'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Significado de house'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Significado de house'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 99: Importancia do ingles
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Importancia do ingles',
    'Explique, em portugues, por que estudar ingles pode ampliar oportunidades de estudo e trabalho.',
    NULL,
    'discursive',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'medio',
    'medio',
    'published',
    0,
    NULL,
    6,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Reading'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Importancia do ingles'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Importancia do ingles'
    ORDER BY `id` DESC
    LIMIT 1
);

-- Questao 100: Objetos da sala de aula
INSERT INTO `questions` (
    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,
    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,
    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,
    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,
    `source_reference`, `usage_count`
)
SELECT
    @marcelo_user_id,
    NULL,
    'Objetos da sala de aula',
    'Desenhe uma sala de aula simples e identifique em ingles pelo menos quatro objetos, como desk, chair, door e book.',
    NULL,
    'drawing',
    'public',
    `disciplines`.`id`,
    `subjects`.`id`,
    'fundamental',
    'facil',
    'published',
    0,
    NULL,
    NULL,
    'medium',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    0
FROM `disciplines`
JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`
WHERE `disciplines`.`name` = 'Ingles'
  AND `subjects`.`name` = 'Vocabulary'
  AND NOT EXISTS (
      SELECT 1
      FROM `questions`
      WHERE `questions`.`author_id` = @marcelo_user_id
        AND `questions`.`title` = 'Objetos da sala de aula'
  );

SET @question_id := (
    SELECT `id`
    FROM `questions`
    WHERE `author_id` = @marcelo_user_id
      AND `title` = 'Objetos da sala de aula'
    ORDER BY `id` DESC
    LIMIT 1
);

