<?php
declare(strict_types=1);

function mc(
    string $discipline,
    string $subject,
    string $level,
    string $difficulty,
    string $title,
    string $prompt,
    array $options,
    array $correct
): array {
    return [
        'discipline' => $discipline,
        'subject' => $subject,
        'education_level' => $level,
        'difficulty' => $difficulty,
        'question_type' => 'multiple_choice',
        'title' => $title,
        'prompt' => $prompt,
        'options' => $options,
        'correct' => $correct,
        'allow_multiple_correct' => count($correct) > 1 ? 1 : 0,
    ];
}

function tf(
    string $discipline,
    string $subject,
    string $level,
    string $difficulty,
    string $title,
    string $prompt,
    bool $answer
): array {
    return [
        'discipline' => $discipline,
        'subject' => $subject,
        'education_level' => $level,
        'difficulty' => $difficulty,
        'question_type' => 'true_false',
        'title' => $title,
        'prompt' => $prompt,
        'true_false_answer' => $answer ? 1 : 0,
    ];
}

function disc(
    string $discipline,
    string $subject,
    string $level,
    string $difficulty,
    string $title,
    string $prompt,
    int $lines
): array {
    return [
        'discipline' => $discipline,
        'subject' => $subject,
        'education_level' => $level,
        'difficulty' => $difficulty,
        'question_type' => 'discursive',
        'title' => $title,
        'prompt' => $prompt,
        'response_lines' => $lines,
    ];
}

function drawq(
    string $discipline,
    string $subject,
    string $level,
    string $difficulty,
    string $title,
    string $prompt,
    string $size,
    ?int $height = null
): array {
    return [
        'discipline' => $discipline,
        'subject' => $subject,
        'education_level' => $level,
        'difficulty' => $difficulty,
        'question_type' => 'drawing',
        'title' => $title,
        'prompt' => $prompt,
        'drawing_size' => $size,
        'drawing_height_px' => $height,
    ];
}

function sql_string(?string $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    return "'" . str_replace(
        ["\\", "'"],
        ["\\\\", "\\'"],
        $value
    ) . "'";
}

$passwordHash = '$2y$10$ltNnvu3eUyb7tO.7Akaj5OmhSsip8XjWcGJvmH3yShplX4DSYleB.';

$questions = [
    mc('Matematica', 'Porcentagem', 'fundamental', 'facil', 'Porcentagem de desconto', 'Um produto custa R$ 200,00 e recebeu desconto de 15%. Qual e o novo preco?', ['R$ 170,00', 'R$ 175,00', 'R$ 180,00', 'R$ 185,00'], [1]),
    mc('Matematica', 'Algebra', 'fundamental', 'facil', 'Resolucao de equacao simples', 'Qual e o valor de x na equacao 2x + 6 = 18?', ['4', '5', '6', '7'], [3]),
    mc('Matematica', 'Geometria', 'fundamental', 'facil', 'Area do retangulo', 'Um retangulo possui 8 cm de base e 5 cm de altura. Qual e a sua area?', ['13 cm2', '20 cm2', '40 cm2', '45 cm2'], [3]),
    mc('Matematica', 'Estatistica', 'fundamental', 'medio', 'Media aritmetica', 'Qual e a media dos numeros 6, 8, 10 e 6?', ['7', '7,5', '8', '8,5'], [2]),
    mc('Matematica', 'Porcentagem', 'medio', 'medio', 'Fracao equivalente', 'Qual das opcoes representa uma fracao equivalente a 3/4?', ['6/10', '9/16', '12/16', '15/25'], [3]),
    mc('Matematica', 'Algebra', 'medio', 'medio', 'Regra de tres direta', 'Se 3 cadernos custam R$ 12,00, quanto custam 5 cadernos, mantendo o mesmo preco unitario?', ['R$ 18,00', 'R$ 20,00', 'R$ 22,00', 'R$ 24,00'], [2]),
    mc('Matematica', 'Algebra', 'medio', 'medio', 'Selecao de numeros primos', 'Marque todos os numeros primos entre 1 e 10.', ['2', '3', '5', '9'], [1, 2, 3]),
    tf('Matematica', 'Geometria', 'fundamental', 'facil', 'Numero primo e par', 'Todo numero primo e impar.', false),
    disc('Matematica', 'Porcentagem', 'medio', 'medio', 'Explicacao sobre porcentagem', 'Explique com suas palavras a diferenca entre porcentagem e proporcao e cite um exemplo do cotidiano.', 6),
    drawq('Matematica', 'Geometria', 'fundamental', 'medio', 'Desenho de figura geometrica', 'Desenhe um quadrado, marque seus vertices e trace uma diagonal ligando dois vertices opostos.', 'medium'),

    mc('Portugues', 'Gramatica', 'fundamental', 'facil', 'Tempo verbal', 'Na frase "Os alunos chegaram cedo", o verbo esta em qual tempo?', ['Presente', 'Futuro', 'Preterito', 'Imperativo'], [3]),
    mc('Portugues', 'Ortografia', 'fundamental', 'facil', 'Plural de cidadao', 'Qual e o plural correto da palavra "cidadao"?', ['Cidadaos', 'Cidadoes', 'Cidadaes', 'Cidadaoses'], [1]),
    mc('Portugues', 'Gramatica', 'fundamental', 'facil', 'Identificacao do sujeito', 'Na frase "A professora explicou a atividade", qual e o sujeito da oracao?', ['Explicou', 'A atividade', 'A professora', 'Professora explicou'], [3]),
    mc('Portugues', 'Ortografia', 'fundamental', 'medio', 'Uso de mas e mais', 'Complete corretamente: "Eu queria sair, ____ estava chovendo muito."', ['mais', 'mas', 'maes', 'maiz'], [2]),
    mc('Portugues', 'Interpretacao de Texto', 'medio', 'medio', 'Sinonimo contextual', 'Qual palavra pode substituir "feliz" sem mudar o sentido principal da frase?', ['Contente', 'Rapido', 'Escuro', 'Distante'], [1]),
    mc('Portugues', 'Gramatica', 'medio', 'medio', 'Classificacao gramatical', 'Na frase "Pedro correu rapidamente", a palavra "rapidamente" e classificada como:', ['Substantivo', 'Adverbio', 'Adjetivo', 'Pronome'], [2]),
    mc('Portugues', 'Gramatica', 'medio', 'medio', 'Identificacao de adverbios', 'Marque todas as palavras que sao adverbios.', ['Ontem', 'Rapidamente', 'Casa', 'Talvez'], [1, 2, 4]),
    tf('Portugues', 'Ortografia', 'medio', 'medio', 'Regra de acentuacao', 'A palavra "excecao" e acentuada por ser oxitona terminada em "o".', false),
    disc('Portugues', 'Interpretacao de Texto', 'medio', 'medio', 'Importancia da leitura', 'Explique por que o habito da leitura contribui para a aprendizagem e para a comunicacao.', 6),
    drawq('Portugues', 'Interpretacao de Texto', 'fundamental', 'facil', 'Mapa mental de narrativa', 'Organize em forma de esquema ou desenho os elementos de uma narrativa: personagem, tempo, espaco e conflito.', 'medium'),

    mc('Fisica', 'Cinematica', 'fundamental', 'facil', 'Velocidade media', 'Um carro percorre 120 km em 2 horas. Qual e a velocidade media?', ['40 km/h', '50 km/h', '60 km/h', '80 km/h'], [3]),
    mc('Fisica', 'Dinamica', 'medio', 'facil', 'Segunda lei de Newton', 'Uma forca de 6 N atua em um corpo de massa 2 kg. Qual e a aceleracao produzida?', ['2 m/s2', '3 m/s2', '4 m/s2', '12 m/s2'], [2]),
    mc('Fisica', 'Energia', 'fundamental', 'facil', 'Fonte renovavel', 'Qual das alternativas apresenta uma fonte de energia renovavel?', ['Carvao mineral', 'Petroleo', 'Energia solar', 'Gas natural'], [3]),
    mc('Fisica', 'Eletricidade', 'medio', 'medio', 'Unidade de corrente eletrica', 'A unidade de medida da corrente eletrica no Sistema Internacional e:', ['Volt', 'Watt', 'Ampere', 'Ohm'], [3]),
    mc('Fisica', 'Termologia', 'medio', 'medio', 'Transferencia de calor', 'Quando o calor passa por contato direto entre corpos, ocorre:', ['Conveccao', 'Radiacao', 'Conducao', 'Reflexao'], [3]),
    mc('Fisica', 'Optica', 'medio', 'medio', 'Imagem em espelho plano', 'A imagem formada em um espelho plano e:', ['Real e invertida', 'Virtual e direita', 'Real e aumentada', 'Virtual e invertida'], [2]),
    mc('Fisica', 'Energia', 'medio', 'medio', 'Fontes renovaveis de energia', 'Marque todas as fontes de energia renovaveis.', ['Solar', 'Eolica', 'Petroleo', 'Carvao mineral'], [1, 2]),
    tf('Fisica', 'Ondulatoria', 'medio', 'facil', 'Propagacao do som', 'No vacuo, o som se propaga normalmente.', false),
    disc('Fisica', 'Dinamica', 'medio', 'medio', 'Massa e peso', 'Explique a diferenca entre massa e peso e indique uma unidade usada para cada grandeza.', 6),
    drawq('Fisica', 'Eletricidade', 'medio', 'medio', 'Esquema de circuito simples', 'Desenhe um circuito simples com uma bateria, um interruptor e uma lampada ligados em serie.', 'medium'),

    mc('Historia', 'Historia do Brasil', 'fundamental', 'facil', 'Independencia do Brasil', 'Em que ano foi proclamada a Independencia do Brasil?', ['1500', '1822', '1889', '1930'], [2]),
    mc('Historia', 'Historia Geral', 'medio', 'facil', 'Lema da Revolucao Francesa', 'Qual expressao representa o lema associado a Revolucao Francesa?', ['Ordem e Progresso', 'Liberdade, Igualdade e Fraternidade', 'Paz, Terra e Pao', 'Fe, Familia e Trabalho'], [2]),
    mc('Historia', 'Historia do Brasil', 'fundamental', 'facil', 'Abolicao da escravidao', 'Em que ano foi assinada a Lei Aurea no Brasil?', ['1822', '1888', '1889', '1937'], [2]),
    mc('Historia', 'Historia Geral', 'medio', 'medio', 'Revolucao Industrial', 'A Revolucao Industrial teve inicio em qual pais?', ['Franca', 'Alemanha', 'Inglaterra', 'Italia'], [3]),
    mc('Historia', 'Historia Geral', 'medio', 'medio', 'Queda do Muro de Berlim', 'A queda do Muro de Berlim ocorreu em:', ['1945', '1961', '1989', '2001'], [3]),
    mc('Historia', 'Historia do Brasil', 'medio', 'medio', 'Proclamacao da Republica', 'A Proclamacao da Republica no Brasil aconteceu em:', ['1789', '1822', '1889', '1930'], [3]),
    mc('Historia', 'Historia Geral', 'superior', 'medio', 'Eventos do seculo XX', 'Marque os acontecimentos que ocorreram no seculo XX.', ['Primeira Guerra Mundial', 'Independencia do Brasil', 'Queda do Muro de Berlim', 'Descobrimento do Brasil'], [1, 3]),
    tf('Historia', 'Historia Geral', 'fundamental', 'facil', 'Ordem das guerras mundiais', 'A Primeira Guerra Mundial ocorreu depois da Segunda Guerra Mundial.', false),
    disc('Historia', 'Historia Geral', 'medio', 'medio', 'Consequencias da Revolucao Industrial', 'Descreva duas consequencias sociais da Revolucao Industrial para a vida dos trabalhadores.', 6),
    drawq('Historia', 'Historia do Brasil', 'fundamental', 'facil', 'Linha do tempo do Brasil', 'Monte uma linha do tempo simples com os marcos: Descobrimento, Independencia e Proclamacao da Republica.', 'large'),

    mc('Geografia', 'Recursos Naturais', 'fundamental', 'facil', 'Maior bioma brasileiro', 'Qual e o maior bioma brasileiro em extensao territorial?', ['Caatinga', 'Amazonia', 'Pampa', 'Pantanal'], [2]),
    mc('Geografia', 'Cartografia', 'fundamental', 'facil', 'Latitude', 'A latitude mede a distancia de um ponto em relacao a:', ['Meridiano de Greenwich', 'Linha do Equador', 'Tropico de Capricornio', 'Nivel do mar'], [2]),
    mc('Geografia', 'Geografia Humana', 'medio', 'facil', 'Urbanizacao', 'O processo de urbanizacao corresponde principalmente ao:', ['Aumento da populacao nas cidades', 'Crescimento das areas rurais', 'Desmatamento da floresta', 'Resfriamento global'], [1]),
    mc('Geografia', 'Recursos Naturais', 'medio', 'medio', 'Matriz energetica renovavel', 'Qual fonte faz parte da matriz energetica renovavel?', ['Carvao', 'Petroleo', 'Energia eolica', 'Diesel'], [3]),
    mc('Geografia', 'Climatologia', 'medio', 'medio', 'Camada da atmosfera', 'Os principais fenomenos meteorologicos ocorrem em qual camada da atmosfera?', ['Estratosfera', 'Troposfera', 'Mesosfera', 'Termosfera'], [2]),
    mc('Geografia', 'Cartografia', 'fundamental', 'medio', 'Uso do GPS', 'O sistema GPS funciona com base em:', ['Cabos subterraneos', 'Satellites', 'Imas terrestres', 'Placas tectonicas'], [2]),
    mc('Geografia', 'Recursos Naturais', 'medio', 'medio', 'Fontes renovaveis', 'Marque todas as fontes renovaveis de energia.', ['Solar', 'Eolica', 'Petroleo', 'Carvao'], [1, 2]),
    tf('Geografia', 'Cartografia', 'fundamental', 'facil', 'Hemisferio brasileiro', 'O Brasil esta totalmente no Hemisferio Norte.', false),
    disc('Geografia', 'Geografia Humana', 'medio', 'medio', 'Crescimento urbano', 'Explique um problema gerado pelo crescimento urbano desordenado e uma possivel solucao.', 6),
    drawq('Geografia', 'Cartografia', 'fundamental', 'facil', 'Rosa dos ventos', 'Desenhe uma rosa dos ventos simples identificando norte, sul, leste e oeste.', 'medium'),

    mc('Biologia', 'Citologia', 'fundamental', 'facil', 'Unidade basica da vida', 'Qual e a unidade basica dos seres vivos?', ['Tecido', 'Organela', 'Celula', 'Molecula'], [3]),
    mc('Biologia', 'Citologia', 'fundamental', 'facil', 'Local da fotossintese', 'Em qual estrutura celular ocorre a fotossintese?', ['Mitocondria', 'Cloroplasto', 'Nucleo', 'Ribossomo'], [2]),
    mc('Biologia', 'Genetica', 'medio', 'facil', 'Material genetico', 'Qual molecula carrega a maior parte das informacoes geneticas dos seres vivos?', ['Proteina', 'Lipidios', 'DNA', 'Agua'], [3]),
    mc('Biologia', 'Ecologia', 'fundamental', 'medio', 'Produtores na cadeia alimentar', 'Em uma cadeia alimentar, os produtores sao geralmente:', ['Fungos', 'Animais carnivoros', 'Plantas e algas', 'Bacterias decompositoras'], [3]),
    mc('Biologia', 'Corpo Humano', 'fundamental', 'facil', 'Orgão do sistema circulatorio', 'Qual orgao bombeia o sangue pelo corpo humano?', ['Pulmao', 'Figado', 'Coracao', 'Rim'], [3]),
    mc('Biologia', 'Corpo Humano', 'medio', 'medio', 'Funcao da vacina', 'A vacina ajuda o organismo principalmente porque:', ['Destroi todos os virus do ambiente', 'Estimula o sistema imunologico', 'Substitui a alimentacao saudavel', 'Aumenta a temperatura corporal'], [2]),
    mc('Biologia', 'Corpo Humano', 'medio', 'medio', 'Componentes do sistema respiratorio', 'Marque os orgaos e estruturas ligados ao sistema respiratorio.', ['Pulmoes', 'Traqueia', 'Femur', 'Bronquios'], [1, 2, 4]),
    tf('Biologia', 'Citologia', 'fundamental', 'facil', 'Quantidade de celulas', 'Todo ser vivo e formado por apenas uma celula.', false),
    disc('Biologia', 'Ecologia', 'medio', 'medio', 'Importancia da biodiversidade', 'Explique por que a biodiversidade e importante para o equilibrio dos ecossistemas.', 6),
    drawq('Biologia', 'Citologia', 'fundamental', 'medio', 'Esquema de celula animal', 'Desenhe uma celula animal simples identificando membrana, citoplasma e nucleo.', 'medium'),

    mc('Quimica', 'Quimica Geral', 'fundamental', 'facil', 'Numero atomico', 'O numero atomico de um elemento corresponde a quantidade de:', ['Neutrons', 'Protons', 'Eletrons de valencia', 'Moleculas'], [2]),
    mc('Quimica', 'Quimica Geral', 'fundamental', 'facil', 'Escala de pH', 'Uma substancia com pH menor que 7 e considerada:', ['Basica', 'Neutra', 'Acida', 'Metalica'], [3]),
    mc('Quimica', 'Quimica Geral', 'fundamental', 'facil', 'Formula da agua', 'Qual e a formula quimica da agua?', ['CO2', 'O2', 'H2O', 'NaCl'], [3]),
    mc('Quimica', 'Quimica Geral', 'medio', 'medio', 'Separacao de misturas', 'Qual processo pode ser usado para separar agua e areia?', ['Filtracao', 'Destilacao fracionada', 'Sublimacao', 'Cromatografia'], [1]),
    mc('Quimica', 'Quimica Geral', 'medio', 'medio', 'Metal alcalino', 'Qual dos elementos abaixo pertence aos metais alcalinos?', ['Sodio', 'Ferro', 'Oxigenio', 'Cloro'], [1]),
    mc('Quimica', 'Quimica Organica', 'medio', 'medio', 'Compostos organicos', 'Os compostos organicos sao caracterizados principalmente pela presenca de:', ['Carbono', 'Helio', 'Sodio', 'Prata'], [1]),
    mc('Quimica', 'Quimica Geral', 'medio', 'medio', 'Mudancas fisicas', 'Marque os exemplos de mudanca fisica.', ['Derreter gelo', 'Rasgar papel', 'Queimar madeira', 'Evaporar agua'], [1, 2, 4]),
    tf('Quimica', 'Quimica Geral', 'fundamental', 'facil', 'Mistura homogenea', 'Mistura homogenea apresenta apenas uma fase visivel.', true),
    disc('Quimica', 'Quimica Geral', 'medio', 'medio', 'Transformacoes da materia', 'Explique a diferenca entre transformacao fisica e transformacao quimica e cite um exemplo de cada.', 6),
    drawq('Quimica', 'Quimica Geral', 'fundamental', 'medio', 'Modelo atomico simples', 'Desenhe um modelo atomico simples indicando nucleo e eletrosfera.', 'medium'),

    mc('Educacao Financeira', 'Orcamento', 'fundamental', 'facil', 'Reserva de emergencia', 'A reserva de emergencia serve principalmente para:', ['Comprar itens de luxo', 'Cobrir imprevistos', 'Pagar apenas lazer', 'Substituir salario fixo para sempre'], [2]),
    mc('Educacao Financeira', 'Juros e Investimentos', 'medio', 'facil', 'Juros simples', 'Qual e o juro simples gerado por R$ 1.000,00 a 10% ao mes durante 1 mes?', ['R$ 10,00', 'R$ 50,00', 'R$ 100,00', 'R$ 110,00'], [3]),
    mc('Educacao Financeira', 'Juros e Investimentos', 'medio', 'medio', 'Juros compostos', 'Aplicando R$ 1.000,00 a 10% ao mes por 2 meses em juros compostos, o montante sera:', ['R$ 1.100,00', 'R$ 1.200,00', 'R$ 1.210,00', 'R$ 1.220,00'], [3]),
    mc('Educacao Financeira', 'Orcamento', 'fundamental', 'facil', 'Conceito de orcamento', 'Orcamento financeiro e o planejamento de:', ['Receitas e despesas', 'Apenas investimentos', 'Apenas impostos', 'Somente compras parceladas'], [1]),
    mc('Educacao Financeira', 'Consumo Consciente', 'medio', 'medio', 'Cartao de credito', 'Pagar somente o valor minimo da fatura do cartao geralmente e arriscado porque:', ['Diminui o limite para sempre', 'Gera cobranca de juros altos', 'Cancela automaticamente o cartao', 'Elimina o historico de compras'], [2]),
    mc('Educacao Financeira', 'Consumo Consciente', 'medio', 'medio', 'Inflacao', 'A inflacao reduz principalmente:', ['A velocidade da internet', 'O poder de compra', 'O numero de empregos automaticamente', 'A conta de energia'], [2]),
    mc('Educacao Financeira', 'Orcamento', 'medio', 'medio', 'Despesas fixas', 'Marque as despesas normalmente consideradas fixas em um orcamento mensal.', ['Aluguel', 'Internet', 'Cinema eventual', 'Mensalidade escolar'], [1, 2, 4]),
    tf('Educacao Financeira', 'Consumo Consciente', 'fundamental', 'facil', 'Parcelamento sempre e vantajoso', 'Parcelar uma compra sempre significa pagar menos.', false),
    disc('Educacao Financeira', 'Orcamento', 'medio', 'medio', 'Importancia de poupar', 'Explique por que poupar antes de consumir pode ajudar na organizacao da vida financeira.', 6),
    drawq('Educacao Financeira', 'Orcamento', 'fundamental', 'facil', 'Quadro de orcamento mensal', 'Monte um quadro ou desenho simples separando receitas, despesas fixas, despesas variaveis e poupanca.', 'custom', 260),

    mc('Informatica', 'Computacao Basica', 'fundamental', 'facil', 'Conceito de hardware', 'Hardware corresponde a:', ['Parte fisica do computador', 'Programa de edicao de texto', 'Conta de e-mail', 'Somente arquivos da nuvem'], [1]),
    mc('Informatica', 'Internet e Seguranca', 'fundamental', 'facil', 'Senha forte', 'Uma senha forte deve combinar preferencialmente:', ['Somente letras minusculas', 'Letras, numeros e simbolos', 'Apenas a data de nascimento', 'O nome completo'], [2]),
    mc('Informatica', 'Internet e Seguranca', 'medio', 'medio', 'Phishing', 'Phishing e uma tentativa de:', ['Acelerar o computador', 'Roubar dados com mensagens falsas', 'Atualizar a impressora', 'Criar backup automatico'], [2]),
    mc('Informatica', 'Computacao Basica', 'fundamental', 'facil', 'Uso da planilha', 'Uma planilha eletronica e muito usada para:', ['Editar videos', 'Fazer calculos e organizar dados', 'Fabricar computadores', 'Enviar sinal de TV'], [2]),
    mc('Informatica', 'Computacao Basica', 'fundamental', 'facil', 'Exemplo de navegador', 'Qual opcao abaixo e um navegador de internet?', ['Chrome', 'Excel', 'Windows', 'SQL'], [1]),
    mc('Informatica', 'Logica e Dados', 'medio', 'medio', 'Banco de dados', 'Um banco de dados serve para:', ['Guardar dados de forma organizada', 'Aumentar a memoria RAM fisicamente', 'Substituir a internet', 'Apagar historico escolar'], [1]),
    mc('Informatica', 'Internet e Seguranca', 'medio', 'medio', 'Boas praticas de seguranca digital', 'Marque as boas praticas de seguranca digital.', ['Usar senha unica para cada servico', 'Ativar autenticacao em dois fatores', 'Compartilhar senha com colegas', 'Misturar letras, numeros e simbolos'], [1, 2, 4]),
    tf('Informatica', 'Internet e Seguranca', 'fundamental', 'facil', 'Compartilhamento de senha', 'E seguro compartilhar sua senha por mensagem com qualquer colega.', false),
    disc('Informatica', 'Logica e Dados', 'medio', 'medio', 'Importancia do backup', 'Explique por que fazer backup periodico ajuda na seguranca e continuidade do trabalho.', 6),
    drawq('Informatica', 'Computacao Basica', 'fundamental', 'facil', 'Rede domestica simples', 'Desenhe uma rede domestica simples com modem, roteador e dois dispositivos conectados.', 'medium'),

    mc('Ingles', 'Vocabulary', 'fundamental', 'facil', 'Traducao de book', 'A palavra "book" significa:', ['Mesa', 'Livro', 'Escola', 'Janela'], [2]),
    mc('Ingles', 'Grammar', 'fundamental', 'facil', 'Uso do verbo to be', 'Complete: "She ___ a student."', ['am', 'is', 'are', 'be'], [2]),
    mc('Ingles', 'Vocabulary', 'fundamental', 'facil', 'Plural irregular', 'Qual e o plural de "child"?', ['childs', 'children', 'childes', 'childrens'], [2]),
    mc('Ingles', 'Vocabulary', 'fundamental', 'facil', 'Antonimo simples', 'O oposto de "hot" em ingles e:', ['cold', 'tall', 'short', 'late'], [1]),
    mc('Ingles', 'Grammar', 'fundamental', 'medio', 'Uso de can', 'Na frase "I can swim", a palavra "can" indica:', ['Tempo passado', 'Permissao ou habilidade', 'Lugar', 'Plural'], [2]),
    mc('Ingles', 'Grammar', 'fundamental', 'medio', 'Preposicao de tempo', 'Complete corretamente: "Classes start ___ Monday."', ['in', 'on', 'at', 'for'], [2]),
    mc('Ingles', 'Vocabulary', 'fundamental', 'medio', 'Cores em ingles', 'Marque as palavras que sao cores em ingles.', ['Blue', 'Red', 'Table', 'Green'], [1, 2, 4]),
    tf('Ingles', 'Vocabulary', 'fundamental', 'facil', 'Significado de house', 'A palavra "house" significa carro.', false),
    disc('Ingles', 'Reading', 'medio', 'medio', 'Importancia do ingles', 'Explique, em portugues, por que estudar ingles pode ampliar oportunidades de estudo e trabalho.', 6),
    drawq('Ingles', 'Vocabulary', 'fundamental', 'facil', 'Objetos da sala de aula', 'Desenhe uma sala de aula simples e identifique em ingles pelo menos quatro objetos, como desk, chair, door e book.', 'medium'),
];

$disciplineSubjects = [];
foreach ($questions as $question) {
    $disciplineSubjects[$question['discipline']] ??= [];
    $disciplineSubjects[$question['discipline']][$question['subject']] = true;
}

$sql = [];
$sql[] = "USE `u488847015_quest_baseDado`;";
$sql[] = "";
$sql[] = "SET @marcelo_user_id := (";
$sql[] = "    SELECT `id`";
$sql[] = "    FROM `users`";
$sql[] = "    WHERE `email` = 'mbsfoz@gmail.com' OR `name` = 'Marcelo Botura'";
$sql[] = "    ORDER BY (`email` = 'mbsfoz@gmail.com') DESC, `id` ASC";
$sql[] = "    LIMIT 1";
$sql[] = ");";
$sql[] = "";
$sql[] = "INSERT INTO `users` (`name`, `email`, `password_hash`, `role`)";
$sql[] = "SELECT 'Marcelo Botura', 'mbsfoz@gmail.com', " . sql_string($passwordHash) . ", 'user'";
$sql[] = "WHERE @marcelo_user_id IS NULL;";
$sql[] = "";
$sql[] = "SET @marcelo_user_id := COALESCE(";
$sql[] = "    @marcelo_user_id,";
$sql[] = "    (";
$sql[] = "        SELECT `id`";
$sql[] = "        FROM `users`";
$sql[] = "        WHERE `email` = 'mbsfoz@gmail.com' OR `name` = 'Marcelo Botura'";
$sql[] = "        ORDER BY (`email` = 'mbsfoz@gmail.com') DESC, `id` ASC";
$sql[] = "        LIMIT 1";
$sql[] = "    )";
$sql[] = ");";
$sql[] = "";

foreach ($disciplineSubjects as $discipline => $subjects) {
    $sql[] = "INSERT INTO `disciplines` (`name`, `created_by`)";
    $sql[] = "SELECT " . sql_string($discipline) . ", @marcelo_user_id";
    $sql[] = "WHERE NOT EXISTS (";
    $sql[] = "    SELECT 1 FROM `disciplines` WHERE `name` = " . sql_string($discipline);
    $sql[] = ");";
    $sql[] = "";

    foreach (array_keys($subjects) as $subject) {
        $sql[] = "INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)";
        $sql[] = "SELECT `disciplines`.`id`, " . sql_string($subject) . ", @marcelo_user_id";
        $sql[] = "FROM `disciplines`";
        $sql[] = "WHERE `disciplines`.`name` = " . sql_string($discipline);
        $sql[] = "  AND NOT EXISTS (";
        $sql[] = "      SELECT 1";
        $sql[] = "      FROM `subjects`";
        $sql[] = "      WHERE `subjects`.`discipline_id` = `disciplines`.`id`";
        $sql[] = "        AND `subjects`.`name` = " . sql_string($subject);
        $sql[] = "  );";
        $sql[] = "";
    }
}

foreach ($questions as $index => $question) {
    $title = $question['title'];
    $sql[] = "-- Questao " . ($index + 1) . ": " . $title;
    $sql[] = "INSERT INTO `questions` (";
    $sql[] = "    `author_id`, `based_on_question_id`, `title`, `prompt`, `prompt_image_url`,";
    $sql[] = "    `question_type`, `visibility`, `discipline_id`, `subject_id`, `education_level`,";
    $sql[] = "    `difficulty`, `status`, `allow_multiple_correct`, `discursive_answer`, `response_lines`,";
    $sql[] = "    `drawing_size`, `drawing_height_px`, `true_false_answer`, `source_name`, `source_url`,";
    $sql[] = "    `source_reference`, `usage_count`";
    $sql[] = ")";
    $sql[] = "SELECT";
    $sql[] = "    @marcelo_user_id,";
    $sql[] = "    NULL,";
    $sql[] = "    " . sql_string($question['title']) . ",";
    $sql[] = "    " . sql_string($question['prompt']) . ",";
    $sql[] = "    NULL,";
    $sql[] = "    " . sql_string($question['question_type']) . ",";
    $sql[] = "    'public',";
    $sql[] = "    `disciplines`.`id`,";
    $sql[] = "    `subjects`.`id`,";
    $sql[] = "    " . sql_string($question['education_level']) . ",";
    $sql[] = "    " . sql_string($question['difficulty']) . ",";
    $sql[] = "    'published',";
    $sql[] = "    " . (string) ($question['allow_multiple_correct'] ?? 0) . ",";
    $sql[] = "    NULL,";
    $sql[] = "    " . (($question['response_lines'] ?? null) === null ? 'NULL' : (string) $question['response_lines']) . ",";
    $sql[] = "    " . sql_string($question['drawing_size'] ?? null) . ",";
    $sql[] = "    " . (($question['drawing_height_px'] ?? null) === null ? 'NULL' : (string) $question['drawing_height_px']) . ",";
    $sql[] = "    " . (($question['true_false_answer'] ?? null) === null ? 'NULL' : (string) $question['true_false_answer']) . ",";
    $sql[] = "    NULL,";
    $sql[] = "    NULL,";
    $sql[] = "    NULL,";
    $sql[] = "    0";
    $sql[] = "FROM `disciplines`";
    $sql[] = "JOIN `subjects` ON `subjects`.`discipline_id` = `disciplines`.`id`";
    $sql[] = "WHERE `disciplines`.`name` = " . sql_string($question['discipline']);
    $sql[] = "  AND `subjects`.`name` = " . sql_string($question['subject']);
    $sql[] = "  AND NOT EXISTS (";
    $sql[] = "      SELECT 1";
    $sql[] = "      FROM `questions`";
    $sql[] = "      WHERE `questions`.`author_id` = @marcelo_user_id";
    $sql[] = "        AND `questions`.`title` = " . sql_string($question['title']);
    $sql[] = "  );";
    $sql[] = "";
    $sql[] = "SET @question_id := (";
    $sql[] = "    SELECT `id`";
    $sql[] = "    FROM `questions`";
    $sql[] = "    WHERE `author_id` = @marcelo_user_id";
    $sql[] = "      AND `title` = " . sql_string($question['title']);
    $sql[] = "    ORDER BY `id` DESC";
    $sql[] = "    LIMIT 1";
    $sql[] = ");";
    $sql[] = "";

    if ($question['question_type'] === 'multiple_choice') {
        foreach ($question['options'] as $optionIndex => $optionText) {
            $displayOrder = $optionIndex + 1;
            $sql[] = "INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`, `display_order`)";
            $sql[] = "SELECT";
            $sql[] = "    @question_id,";
            $sql[] = "    " . sql_string($optionText) . ",";
            $sql[] = "    " . (in_array($displayOrder, $question['correct'], true) ? '1' : '0') . ",";
            $sql[] = "    " . $displayOrder;
            $sql[] = "FROM DUAL";
            $sql[] = "WHERE @question_id IS NOT NULL";
            $sql[] = "  AND NOT EXISTS (";
            $sql[] = "      SELECT 1";
            $sql[] = "      FROM `question_options`";
            $sql[] = "      WHERE `question_options`.`question_id` = @question_id";
            $sql[] = "        AND `question_options`.`display_order` = " . $displayOrder;
            $sql[] = "  );";
            $sql[] = "";
        }
    }
}

$outputPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'seed_100_questoes_marcelo_botura.sql';
$sqlContent = implode(PHP_EOL, $sql) . PHP_EOL;

file_put_contents($outputPath, $sqlContent);

echo 'Arquivo gerado: ' . $outputPath . PHP_EOL;
echo 'Total de questoes: ' . count($questions) . PHP_EOL;
