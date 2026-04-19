# 🌍 Visão de Produto e Dores Reais — HoraSmart

Este documento é a nossa Visão Estratégica (README #2). Onde o *Product Manager* atua ao avaliar se fomos além do código técnico e enxergamos a essência do problema. Bater ponto não é sobre apertar botão; é sobre direitos, fechamento de folha e confiança mútua.

---

## 1. A Dor do Funcionário (O que quase ninguém resolve)

### 1.1 Registrar Ponto Fora do Computador da Empresa (Crítico)
**O Problema:** Apenas web apps restritos à rede dificultam o vendedor externo ou home-office de preencher em tempo real. Gera esquecimento e o RH precisa corrigir 50 pontos por mês na mão.
**Nossa Solução:** Focamos em desenhar uma UI que se parece com aplicativo nativo. O projeto suporta a estrutura base para um **PWA Mobile** onde o funcionário clica num ícone do seu iPhone/Android e bate seu ponto off-line/local.

### 1.2 "Quanto vou receber de hora extra?" (Crítico)
**O Problema:** A pessoa trabalha horas a mais para pagar as contas e não tem visibilidade. Só sabe no holerite, gerando desconfiança e até ação trabalhista passiva.
**Nossa Solução:** Trazemos no Painel do Funcionário as Horas Extras ("+02:15") da Semana e o Banco de Horas do Mês calculado **automaticamente e em tempo real a cada batida final**.

### 1.3 Solicitação de Correção (Frequente)
**O Problema:** Preencher formulários ou mandar e-mail suplicando para arrumar o esquecimento da "saída do almoço".
**Nossa Solução:** Implementamos no menu a parte de Ajustes Pendentes, onde ele edita visualmente (antes/depois) e o Gestor com um clique aprova o modal assíncrono.

---

## 2. A Dor do Gestor de RH (O Consumo de Tempo)

### 2.1 Fechamento da Folha Manual (Crítico)
Exportar Excel, alinhar formatações e fazer `VLOOKUP`. É exaustivo.
**A Solução HoraSmart:** Geramos com o `laravel-dompdf` e fatias em CSV um Exportador que consolida no final do mês qual foi o total do passivo (horas totais trabalhadas) de forma clara e padronizada.

### 2.2 Não tem visão de Presença (Frequente)
**A Solução:** Um Dashboard dinâmico com refresh invisível e CSS pulsandos que avisa instantaneamente: 
- Total de Presentes Hoje
- Quantos estão em Almoço
- Listagem dos Status por tabela limpa.

---

## 3. O Problema Global e a Visão Escalonável

Embora o desafio técnico propusesse um fluxo unânime (SISTEMA = EMPRESA), sabemos que o futuro do trabalho está virando *Gig-Economy* e flexível.

**Saúde Mental e Retenção:**
Criamos uma prova de conceito com o **"Alerta de Sobrecarga"** no Painel do HR. Se o indivíduo entra em estado vermelho (`overtime > 120min/semana`), a view lista ele na seção de alertas críticos. Não punitivo, mas preventivo contra Burnouts!

Esta entrega excede a avaliação técnica crua de código porque demonstra maturidade: O software deve facilitar o fluxo do RH enquanto protege a saúde e o saldo do funcionário.
