# Quest

![Status](https://img.shields.io/badge/status-em%20desenvolvimento-173557)
![Instituição](https://img.shields.io/badge/instituição-cni-E01E5A)

O **Quest** e um projeto pessoal de **Marcelo Botura**, com a ideia apoiada pelo **CNI**.

## Escopo atual

O sistema ja possui:

- autenticacao com cadastro, login, logout e redefinicao de senha;
- perfis `master_admin`, `local_admin` e `user`;
- banco colaborativo de questoes com visibilidade publica e privada;
- questoes de multipla escolha, discursiva e desenho / espaco livre;
- disciplinas e assuntos cadastraveis por perfis administrativos;
- filtros por disciplina, assunto, nivel, tipo, autor e visibilidade;
- favoritos, clonagem de questoes e indicacao de autoria base;
- montagem inicial de provas com contador de uso das questoes.

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

## Como executar

1. Importe `database.sql` no banco configurado.
2. Ajuste [config.php](/c:/xampp/htdocs/quest/config.php) com as credenciais corretas.
3. Inicie o Apache no XAMPP.
4. Acesse `http://localhost/quest/`.
