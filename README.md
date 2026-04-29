# ⏱️ HoraSmart — Sistema de RH e Controle de Jornada

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+">
  <img src="https://img.shields.io/badge/TailwindCSS-3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 3">
  <img src="https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js 3">
  <img src="https://img.shields.io/badge/SQLite-Database-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
</p>

---

## 🎯 Visão do Produto (Product Perspective)

O **HoraSmart** é um sistema web responsivo para gestão de funcionários e controle de jornada de trabalho. Seu objetivo é simplificar a vida do departamento de RH e dos colaboradores através de uma interface premium, dinâmica e focada na experiência do usuário.

### Principais Funcionalidades
- **Ponto Digital Inteligente**: Registro com 4 batidas diárias via AJAX (sem recarregamento da página). O design conta com a animação "pulse" focada na ação principal.
- **Controle de Horas Extras**: Cálculo automático das horas trabalhadas, incluindo banco de horas mensal e diário.
- **Relatórios Avançados**: Exportação de dados consolidados em *CSV* e relatórios mensais em *PDF*. Os exportadores foram refinados para ordenar resultados primeiramente por nome do funcionário e data.
- **Solicitação de Ajustes**: Fluxo de aprovação integrado. O funcionário solicita correções na batida e o gestor revisa visualizando o impacto "antes e depois" através de um modal assíncrono projetado detalhadamente.
- **Dashboard de Gestão**: Alertas de sobrecarga (funcionários com muitas horas extras neste mês/semana) e acompanhamento em tempo real das presenças.
- **Design Premium e Mobile-First**: Interface *glassmorphism*, micro-animações, feedback visual estelar, design responsivo fluido com tabelas scrolláveis e **PWA** configurado para instalar em desktops e smartphones.

---

## 💻 Visão Técnica (Technical Perspective)

### Stack e Bibliotecas
- **Backend:** Laravel 13, PHP 8.4+, `barryvdh/laravel-dompdf` para renderização de PDF.
- **Frontend:** Blade, Tailwind CSS 3 (gerenciado pelo Vite), blocos semânticos e CSS vainilla em `app.css` para *custom properties*, Alpine.js 3 para estado efêmero e transições.
- **Banco de Dados:** SQLite no ambiente de desenvolvimento, plenamente migrável por ORM.
- **Design Pattern:** *Service Pattern*. Todas as lógicas complexas e de máquina de estado do ponto batido residem no `WorkLogService`.
- **Stack consolidada do produto:** veja `requirements.txt` (`[stack_snapshot]`) com backend, frontend, modelagem e decisões técnicas fechadas.

### Decisões de Arquitetura e Modelagem
- **Múltiplos Níveis de Usuário e RBAC**: Proteção imposta pelo `EnsureUserRole` middleware implementado, em adição às Gate/Policies providas nativamente pelo Laravel (`EmployeePolicy`, `WorkLogPolicy`).
- **Autonomia da Tabela Employee:** `Employee` contém especificações RH. A separação do `User` isola os dados de login mantendo as abstrações intactas.
- **Horário Transposto em Minutos:** As horas trabalhadas e extras são persistidas e trafegadas como inteiros. Para as *views*, Accessors encapsulam os dados via formato embutido "HH:MM" (ex. `getOvertimeMinutesAttribute` / `getFormattedOvertimeAttribute`).
- **Componentização com Alpine.js**: Extensa utilização do Alpine em componentes onde componentes Livewire seriam "pesados" demais, comumente em Filtros, Modais de Edição e dropdowns de navegação interativa.

---

## 🚀 Como Executar (Process Perspective)

### 1. Requisitos Computacionais
- PHP 8.4+ (execucao local)
- Node.js (v18 recomendável)
- SQLite3 ativado ou Servidor SQL em rodagem
- Composer (2.0+)
- Docker + Docker Compose plugin (recomendado para executar com Laravel Sail)
- Consulte `requirements.txt` para ver a lista consolidada de tecnologias e dependências do projeto.

### 2. Passo a Passo

```bash
# 1. Clonar o repositório
git clone <url-do-repositorio>
cd HoraSmart

# 2. Conferir stack e dependências do projeto
cat requirements.txt

# 3. Instalar dependências PHP
composer install

# 4. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 5. Criar dados de teste elaborados (gestor, 5 funcionários, e 30 dias de batida randomizada com lógica robusta via Factory/Seeder)
php artisan migrate:fresh --seed

# 6. Compilar assets (Tailwind e JS) e registrar publicamente os SW (Service Workers para o PWA)
npm install
npm run build

# 7. Iniciar o servidor
php artisan serve
```

Acesse a página: **http://(IP_ADDRESS ou localhost:8000)**

### 3. Execução Recomendada com Laravel Sail (Docker)

> Use este fluxo se seu PHP local for menor que 8.4.

```bash
# 1. Subir containers (app + postgres + mailpit)
./vendor/bin/sail up -d

# 2. Instalar dependências PHP dentro do container
./vendor/bin/sail composer install

# 3. Configurar ambiente (se necessário)
cp .env.example .env
./vendor/bin/sail artisan key:generate

# 4. Rodar migrations + seeders
./vendor/bin/sail artisan migrate:fresh --seed

# 5. Compilar frontend
npm install
npm run build
```

Acesse via porta configurada no `.env` (`APP_PORT`, padrão `80`): **http://localhost**

### 4. Troubleshooting Rápido

- Erro `Your Composer dependencies require a PHP version ">= 8.4.0"`: seu projeto está travado em dependências que exigem PHP 8.4. Execute com Sail ou atualize o PHP local para 8.4+.
- Se o `composer install` falhar no host, não continue com `php artisan ...` fora do container; use `./vendor/bin/sail artisan ...`.

### 🔑 Credenciais (Via DatabaseSeeder)
Os seeders injetam perfis prontos para você debugar todas as features de ponta-a-ponta:

| Perfil | E-mail | Senha |
|--------|--------|-------|
| **Gestor de RH** | `gestor@smart.com` | `password` |
| **Funcionário Demo 1** | `carlos@smart.com` | `password` |
| **Funcionário Demo 2** | `ana@smart.com` | `password` |

> *Dica: Teste primeiro visualizar os Relatórios CSV/PDF logando como gestor, depare-se com as correções já integradas, para em seguida logar-se como "carlos@smart.com" e efetuar o disparo do AJAX ponto-eletrônico em tempo-real!* 

---

## 📜 Histórico de Commits

Abaixo encontra-se o registro cronológico das últimas submissões efetuadas no repositório de desenvolvimento, evidenciando a evolução contínua da aplicação:

* **melhoras nas features de perfil** - *agora mesmo*
* **README técnico** - *3 horas atrás*
* **retirada de logs arquivados com os erros** - *3 horas atrás*
* **Correção de Bugs do front-end e finalização das views** - *3 horas atrás*
* **finalização das views Employees e do app.blade.php** - *3 dias atrás*
* **merg de brachs** - *4 dias atrás*
* **correção de bugs** - *4 dias atrás*
* **Correção de merge nas branchs** - *5 dias atrás*
* **remodelagem dos models** - *5 dias atrás*
* **remodel do projeto e exclusão de arquivos** - *6 dias atrás*
* **montagem dos Models e mudanças nas pastas do projeto** - *6 dias atrás*
* **Criação do ambiente + criação das tabelas do bd** - *10 dias atrás*
* **Initial commit** - *13 dias atrás*
