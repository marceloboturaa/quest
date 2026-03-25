# Quest

![Status](https://img.shields.io/badge/status-em%20desenvolvimento-173557)
![Instituição](https://img.shields.io/badge/instituição-cni-E01E5A)

O **Quest** e um projeto pessoal de **Marcelo Botura**, com a ideia apoiada pelo **CNI**.

## Checklist resumida

### Essencial

- [x] Criar questao (CRUD)
- [x] Tipos: multipla, discursiva, desenho
- [x] Alternativas dinamicas (A, B, C... ilimitado)
- [x] Marcar resposta correta

### Usuario e privacidade

- [x] Login funcionando
- [x] Questao vinculada ao usuario
- [x] Publico / Privado
- [x] Bloquear edicao de outros usuarios

### Compartilhamento

- [x] Copiar (clonar) questao publica
- [x] Criar nova questao ao copiar

### Organizacao

- [x] Cadastro de disciplinas
- [x] Cadastro de assuntos
- [x] Nivel: Fundamental, Medio, Tecnico, Superior

### Banco de questoes

- [x] Filtros por disciplina, assunto, tipo e nivel
- [x] Listagem organizada

### Tipos especificos

- [x] Discursiva com linhas
- [x] Desenho com espaco e altura ajustavel

### Provas

- [x] Selecionar questoes
- [x] Montar prova

### Qualidade do sistema

- [x] Usar PDO
- [x] Separar funcoes
- [x] Evitar codigo repetido

### Extras

- [x] Favoritos
- [x] Contador de uso
- [x] Exportar PDF

## Prioridade principal coberta

- [x] Criar questao
- [x] Alternativas
- [x] Publico/privado
- [x] Clonar
- [x] Filtros

## Banco de dados

O arquivo `database.sql` cria a estrutura principal, incluindo:

- `users`
- `disciplines`
- `subjects`
- `questions`
- `question_options`
- `question_favorites`
- `exams`
- `exam_questions`

Atualizacao manual adicional:

- `database_update_20260325.sql`

## Como executar

1. Importe `database.sql` no banco configurado.
2. Se necessario, aplique `database_update_20260325.sql`.
3. Ajuste [config.php](/c:/xampp/htdocs/quest/config.php) com as credenciais corretas.
4. Use `config.local.php` para credenciais locais sensiveis.
5. Inicie o Apache no XAMPP.
6. Acesse `http://localhost/quest/`.
