# Quest

![Status](https://img.shields.io/badge/status-em%20desenvolvimento-173557)
![Instituição](https://img.shields.io/badge/instituição-cni-E01E5A)

O **Quest** e um projeto pessoal de **Marcelo Botura**, com a ideia apoiada pelo **CNI**.

## O que existe agora

- autenticacao com login, cadastro e logout;
- recuperacao de senha por token;
- painel com perfis `master_admin`, `local_admin` e `user`;
- pagina master para promover usuarios para admin local;
- cadastro de questoes de multipla escolha, discursiva e verdadeiro ou falso.

## Banco de dados

Configuracao atual no projeto:

- banco: `u488847015_quest_baseDado`
- usuario: `u488847015_quest_userName`
- dominio: `quest.cidadenovainforma.com.br`

O arquivo [database.sql](/c:/xampp/htdocs/quest/database.sql) cria as tabelas principais e insere um master admin inicial:

- e-mail: `master@quest.local`
- senha: `Admin123!`

Troque essa senha assim que iniciar o sistema.

## Como subir no XAMPP

1. Importe o arquivo `database.sql` no MySQL.
2. Ajuste a senha do banco em [config.php](/c:/xampp/htdocs/quest/config.php), se necessario.
3. Inicie o Apache no XAMPP.
4. Acesse `http://localhost/quest/`.

## Observacao

O fluxo de reset de senha tenta enviar e-mail via `mail()` e tambem grava o conteudo em `storage/mail.log`, o que facilita testes locais sem SMTP configurado.
