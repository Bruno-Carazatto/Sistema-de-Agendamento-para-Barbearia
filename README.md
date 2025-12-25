# 💈 Sistema de Agendamento para Barbearia

Sistema web desenvolvido em **PHP e MySQL** para gerenciamento de **agendamentos de horários em barbearias**, com separação de acesso entre **clientes** e **administradores**.

O projeto permite que clientes realizem reservas online e que administradores controlem a agenda, disponibilidade de horários e cancelamentos de forma simples e eficiente.

---

## 📌 Funcionalidades

### 👤 Cliente
- Cadastro e login de usuários
- Visualização de datas e horários disponíveis
- Agendamento de horários
- Listagem de reservas realizadas
- Cancelamento de reservas

### 🔐 Administrador
- Login com permissões administrativas
- Visualização completa da agenda
- Bloqueio e desbloqueio de horários
- Cancelamento de agendamentos de clientes
- Gerenciamento da disponibilidade

---

## 🛠 Tecnologias Utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript (AJAX)
- Sessões PHP

---

## 📁 Estrutura do Projeto

```txt
barbearia
├── assets
│   ├── css
│   │   └── style.css
│   └── js
│       └── app.js
│
├── config
│   └── db.php
│
├── includes
│   ├── auth.php
│   ├── header.php
│   └── footer.php
│
├── public
│   ├── index.php
│   ├── register.php
│   ├── dashboard.php
│   ├── user_agendar.php
│   ├── minhas_reservas.php
│   ├── logout.php
│   │
│   ├── admin_agenda.php
│   ├── admin_disponibilidade.php
│   │
│   ├── api_agendar.php
│   ├── api_slots.php
│   ├── api_available_dates.php
│   ├── api_cancel_booking.php
│   ├── api_admin_cancel.php
│   ├── api_block.php
│   └── api_unblock.php
│
└── sql
    └── schema.sql
```

---

## ⚙️ Instalação e Configuração

### 1️⃣ Clone o repositório
```bash
git clone https://github.com/Bruno-Carazatto/Sistema-de-Agendamento-para-Barbearia
```

### 2️⃣ Configure o banco de dados
- Crie um banco de dados MySQL com o nome (barbearia)
- Importe o arquivo: barbearia.sql / Localizado em ( barbearia / sql / barbearia.sql
- Observação o arquivo (barbearia.sql) já vem o usuários: (admin,teste) já criados

```bash
sql/schema.sql
```

### 3️⃣ Configure a conexão com o banco
Edite o arquivo:

```bash
config/db.php
```

```php
$host = "localhost";
$db   = "nome_do_banco";
$user = "usuario";
$pass = "senha";
```

### 4️⃣ Inicie o servidor local
Você pode utilizar:
- XAMPP
- WAMP
- Laragon
- Servidor embutido do PHP

```bash
php -S localhost:8000 -t public
```

Acesse no navegador:

```
http://localhost:8000
```

---

## 🔐 Controle de Acesso

- Autenticação baseada em **sessões PHP**
- Proteção de rotas via `includes/auth.php`
- Separação clara entre: Admin e Usuários
- Usuários já criados:
  - Usuário: teste@teste.com / Senha: teste@123456
  - Admin: admin@admin.com / Senha: admin@admin

---

## 📈 Possíveis Melhorias Futuras

- Cadastro de serviços (corte, barba, etc.)
- Confirmação de agendamento por e-mail
- Interface responsiva
- Suporte a múltiplos funcionários
- Histórico de atendimentos
- Painel administrativo avançado

---

## 📄 Licença

Este projeto está sob a licença **MIT**.  
Sinta-se livre para usar, modificar e distribuir.

---

## 👨‍💻 Autor

Desenvolvido por **Bruno Carazatto**  
📧 Email: brunocarazatto@gmail.com  
🔗 GitHub: https://github.com/Bruno-Carazatto

---

⭐ Se este projeto te ajudou, considere deixar uma estrela no repositório!
