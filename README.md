# ⚙️ TaskFlow — Backend (Laravel)

O **TaskFlow Backend** é a API responsável por gerenciar autenticação, usuários e tarefas da aplicação TaskFlow.  
Desenvolvido em **Laravel 12** com **PHP 8.2**, ele oferece endpoints RESTful seguros, autenticação via **JWT**, e suporte completo para CRUD de tarefas e configurações de usuário.

---

## 🚀 Tecnologias Utilizadas

| Categoria | Tecnologia |
|------------|-------------|
| **Linguagem** | PHP 8.2 |
| **Framework** | [Laravel 12](https://laravel.com/) |
| **Autenticação** | JWT HttpOnly |
| **Banco de Dados** | MySQL |  |
| **Seeders e Factories** | Laravel ORM (Eloquent) |
| **CLI & Debug** | Artisan / Laravel Sail / Laravel Pail |

---


## 🧰 Configuração do Ambiente

### 1. Clonar o repositório

```bash
git clone https://github.com/maryanasampaio/agendaKa-backend.git
cd taskflow-backend
```
### 2. Instalar dependências

```bash
git clone https://github.com/seu-usuario/taskflow-backend.git
cd taskflow-backend
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


