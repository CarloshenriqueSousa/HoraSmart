# 🧠 Arquitetura e Decisões Técnicas — HoraSmart

Este documento é o Diário de Desenvolvimento (README #3) focado em **COMO** pensamos e construímos o produto, além de justificar a fundo as escolhas de arquitetura submetidas neste desafio de RH.

---

## 1. Modelagem do Banco de Dados

### 1.1 Separação entre Auth (`users`) e Entidade (`employees`)
Optamos por separar a autenticação dos dados da empresa. A tabela `users` cuida unicamente de e-mail e password (autenticação), enquanto `employees` (com `user_id` único) guarda CPF, cargo, data de admissão e endereço.
**Por que?** Isso evita inflar o objeto `$user` em todas as requisições e isola os contextos. Se amanhã o sistema possuir "Terceirizados" ou "PJs", a tabela `users` continua intocável.

### 1.2 "Abordagem Ingênua" vs "Abordagem Correta" em Tempos (Enum de Status)
Em muitos sistemas de ponto simples, a tabela conta com 4 colunas obrigatórias e sem validação estrita. Nós usamos:
- `work_date` (Como `date`, garantindo busca por dia).
- `clock_in`, `lunch_out`, `lunch_in`, `clock_out` (Como `timestamp` permitindo `null`).
- `minutes_worked` (inteiro, ao invés de `decimal`, para evitar perda em casting/arredondamento).
- `status` (`enum` de máquina de estados).

**Por que o Status Enum importa?**
Isso transforma nossa coluna `status` (`in_progress`, `on_lunch`, `back_from_lunch`, `complete`) no limitador exato da UI. O back-end SEMPRE sabe o próximo passo, bloqueando fraudes (tentar bater a saída antes do almoço).

### 1.3 Impedindo registros duplicados
Usamos na migration uma **Unique Constraint** composta `UNIQUE(employee_id, work_date)`. O Banco de Dados literalmente "chuta" se tentarem bater ponto para o mesmo dia em fluxos bifurcados (garantia no nível SGBD e não apenas no PHP).

---

## 2. Regras de Negócio e Organização de Pastas

### 2.1 Uso do Service Pattern (`WorkLogService.php`)
Não existe 1 linha sequer de regra de negócio complexa dentro do Controller de Registro de Ponto (`WorkLogController`).
Criamos o `app/Services/WorkLogService.php` que responde por:
- Buscar o dia de hoje, abrindo uma `DB::transaction()`
- Utilizar `firstOrCreate` para assegurar os steps.
- Rodar o *state engine* (passar do step A pro B).
- Fazer a matemática exata entre horas e repassar ao final.

**O benefício:** Se amanhã decidirmos bater o ponto via API do Celular (PWA) ao invés da View web, consumimos o MESMO serviço, e os testes unitários focam estritamente nesta camada.

### 2.2 Accessor Pattern nos Models
A formatação do Front-End não polui a base de dados. Criamos lógicas no `WorkLog.php` usando Accessors do Laravel:
- `getFormattedHoursAttribute()` converte os minutos para string `HH:MM`.
- `getOvertimeMinutesAttribute()` faz a subtração em tempo de execução comparado com a constante `DAILY_WORKLOAD`.

---

## 3. Experiência de Produto e Front-End

### 3.1 PWA Ready
Configuramos `manifest.json` e Service Workers atrelados ao layout Blade. Como as dores de Ponto são muito severas para Home-office ou terceiros em campo (que usam smartphone), ele pode ser "Adicionado à Tela Inicial" de forma orgânica.

### 3.2 O abandono do Livewire a favor do Alpine.js + Fetch
Escolhemos o Alpine.js porque em uma batida de ponto com múltiplos usuários simultâneos, recarregar todo o DOM da página num refresh Livewire é custoso. Um botão renderizado em Alpine usando Fetch API, injetando o CSRF-Token na mão disparando Ajax, gasta `~40ms` pra confirmar a batida de forma assíncrona.

Ao combinar com o TailwindCSS (transições e micro-interações como o `animate-pulse-ring` do ponteiro verde), geramos um Wow-effect no usuário por quase custo zero de server-side.
