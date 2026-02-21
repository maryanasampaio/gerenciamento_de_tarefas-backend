# ⚙️ TaskFlow — Backend (Laravel)

O **TaskFlow Backend** é a API responsável por gerenciar autenticação, usuários e metas da aplicação TaskFlow.  
Desenvolvido em **Laravel 12** com **PHP 8.2**, ele oferece endpoints RESTful seguros, autenticação via **JWT**, e suporte completo para CRUD de metas, tarefas vinculadas e configurações de usuário.

---

## 🚀 Tecnologias Utilizadas

| Categoria | Tecnologia |
|------------|-------------|
| **Linguagem** | PHP 8.2 |
| **Framework** | [Laravel 12](https://laravel.com/) |
| **Autenticação** | JWT HttpOnly |
| **Banco de Dados** | MySQL |  |
| **Seeders e Factories** | Laravel ORM (Eloquent) |

---


## 🧰 Configuração do Ambiente

### 1. Clonar o repositório

```bash
git clone https://github.com/maryanasampaio/gerenciamento_de_tarefas-backend.git
cd gerenciamento_de_tarefas-backend
```
### 2. Instalar dependências

```bash
composer install
```

### 3. Criar o arquivo `.env`

```bash
cp .env.example .env
```

### 4. Configurar o banco de dados

No arquivo `.env`, configure as variáveis de conexão:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskflow
DB_USERNAME=root
DB_PASSWORD=senha
```

### 5. Gerar a chave da aplicação

```bash
php artisan key:generate
``` 

### 6. Executar as migrações e seeders

```bash
php artisan migrate --seed
``` 

### 7. Gerar a chave JWT

```bash
php artisan jwt:secret

```

### 8. Iniciar o servidor local

```bash
php artisan serve
```

A API estará disponível em:
👉 http://localhost:8000

## 📚 Estrutura de Pastas

app/
├── Http/
│ ├── Controllers/
│ ├── Middleware/
│ └── Requests/
├── Models/
├── Services/
├── Providers/
database/
├── migrations/
├── seeders/
routes/
├── api.php
└── web.php

---

## 🏁 Novos Recursos de Metas

- Metas com `tipo`: diaria, mensal, anual.
- Campos: `titulo`, `descricao` (opcional), `contexto`, `prioridade` (baixa, media, alta; padrão: baixa), `status` (pendente, em_andamento, concluida; padrão: pendente). Para metas mensais/anuais: `data_inicio` e `data_fim` obrigatórios.
- Tarefas vinculadas à meta (`id_meta`) com `titulo`, `descricao` (opcional) e `status` (pendente ou concluida). O status da meta atualiza automaticamente: todas concluídas → concluida; pelo menos uma concluída → em_andamento; nenhuma → pendente.
- Progresso da meta é retornado como `{ total, concluidas, percentual }` nas listagens e detalhes.

## 🔗 Endpoints Principais

- POST /api/metas/criar
- GET /api/metas/listar?tipo=diaria|mensal|anual
	- Opcional: `data=dd/mm/YYYY` ou `YYYY-mm-dd` para filtrar.
		- `tipo=diaria`: usa o dia pela data de criação (`created_at`) da meta.
		- `tipo=mensal|anual`: retorna metas cujo intervalo `data_inicio..data_fim` contém a data.
		- Sem `tipo`: combina diárias do dia e mensais/anuais que contem a data.
- GET /api/metas/detalhes/{id}
- PUT /api/metas/atualizar/{id}
- DELETE /api/metas/deletar/{id}
- POST /api/metas/{id_meta}/tarefas/criar
- PUT /api/metas/{id_meta}/tarefas/{id_tarefa}/status


