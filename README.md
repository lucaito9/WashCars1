  Wash Cars  -
Plataforma Web Integrada para Revolucionar o Agendamento e a Gestão de Lava Jatos.

---

##  Contexto Acadêmico e Profissional

O **Wash Cars** é um projeto desenvolvido com dupla finalidade: como peça central de **Portfólio Pessoal** e como projeto prático para o curso de **Análise e Desenvolvimento de Sistemas (ADS)** no **Centro Universitário de Brasília (UniCEUB)**.

Esta iniciativa simula um ambiente real de desenvolvimento de software, aplicando conceitos de:
* **Engenharia de Software:** Levantamento de requisitos, regras de negócio e UX/UI.
* **Banco de Dados:** Modelagem relacional e segurança de dados com consultas preparadas (PDO).
* **Programação Web:** Desenvolvimento Full-Stack orientado a objetos.
* **Metodologias Ágeis:** Divisão de tarefas e desenvolvimento incremental.

---

##  Visão Geral do Projeto

### O Problema
Muitos lava jatos ainda realizam o agendamento de clientes através de processos manuais ou mensagens soltas. Esse cenário resulta em retrabalho operacional, confusão de horários e uma experiência limitada para o cliente, que não possui um histórico de seus veículos e serviços.

### A Solução Wash Cars
O **Wash Cars** propõe uma plataforma web unificada (no padrão visual *Dark Edition*) que conecta o cliente ao Lava Jato de forma automatizada. 
* **Centralização:** Dados de clientes, veículos e agendamentos em um único ecossistema.
* **Autonomia:** O cliente gerencia sua própria garagem virtual e escolhe seus horários.
* **Dinamismo:** A empresa possui um painel administrativo para gerir serviços e preços em tempo real.

---

##  Escopo do Projeto (MVP)

O objetivo do MVP (Produto Mínimo Viável) é entregar um fluxo completo de agendamento funcional e seguro.

### Funcionalidades Implementadas

**1. Segurança e Perfis de Acesso**
* Sistema de Login com sessões protegidas.
* **RBAC (Role-Based Access Control):** Perfis distintos para Empresa e Cliente, garantindo que um usuário não acesse áreas administrativas indevidas.

**2. Painel Administrativo (Empresa)**
* Gestão dinâmica de catálogo: criação e edição de serviços (ex: Lavagem Completa, Higienização) com valores em Real (BRL).
* Monitoramento de agendamentos pendentes.

**3. Painel do Cliente**
* **Garagem Virtual:** Cadastro e manutenção de veículos com validação de placa.
* **Agendamento Inteligente:** Fluxo guiado onde o cliente seleciona o serviço, escolhe o veículo e define data/hora.
* Prevenção de erros: O sistema impede agendamentos se não houver veículos cadastrados.

---

##  Tecnologias Utilizadas

* **Frontend:** HTML5, CSS3 (Custom Dark Theme), JavaScript e Bootstrap 5 para responsividade.
* **Backend:** PHP 8+ (Processamento de rotas e lógica de negócio).
* **Banco de Dados:** MySQL com camada de segurança PDO para prevenção de SQL Injection.
* **Hospedagem:** Ambiente de produção configurado na plataforma InfinityFree.

---

## Requisitos 

Requisitos Funcionais (RF)

Ações e funcionalidades que o sistema deve oferecer aos usuários e administradores.

RF01 - Bloqueio de Horários: O sistema deve verificar a disponibilidade no banco de dados e bloquear horários que já estejam agendados, impedindo reservas duplicadas. (Sprint 2)

RF02 - Painel Administrativo: O sistema deve possuir um Dashboard de acesso restrito para os administradores. (Sprint 3)

RF03 - Gestão de Agendamentos: O administrador deve ser capaz de visualizar a lista de agendamentos e alterar os status de cada um (ex: pendente, confirmado, cancelado). (Sprint 3)

RF04 - Envio de Comprovantes por E-mail: O sistema deve enviar comprovantes de agendamento para o e-mail do cliente de forma automatizada. (Sprint 4)

RF05 - Sistema de Avaliação: O sistema deve permitir que os clientes avaliem o serviço utilizando um formato de 1 a 5 estrelas. (Sprint 5)

RF06 - Redirecionamento de Pagamento: O sistema deve gerar e fornecer um link de redirecionamento para pagamento simplificado via PIX ou contato de WhatsApp. (Sprint 5)

Requisitos Não Funcionais (RNF)

Restrições técnicas, requisitos de infraestrutura, desempenho e usabilidade.

RNF01 - Responsividade (Usabilidade): A interface web deve ser totalmente responsiva, contendo a configuração da tag viewport para adaptação em diferentes tamanhos de tela. (Sprint 1)

RNF02 - Adaptação Mobile (Usabilidade): Os botões devem ser ajustados para o toque na tela (touch-friendly) e as tabelas do painel Admin devem ser convertidas em "Cards" empilháveis via CSS em telas menores. (Sprint 1)

RNF03 - Otimização de Banco de Dados (Desempenho/Restrição): As consultas SQL (especialmente as de agendamento) devem ser altamente otimizadas para evitar o estouro do limite diário de processamento do servidor de hospedagem. (Sprint 2)

RNF04 - Carregamento Leve (Desempenho): O painel do Administrador deve manter uma estrutura de interface leve, garantindo o carregamento rápido mesmo em conexões móveis limitadas (ex: 4G). (Sprint 3)

RNF05 - Comunicação Externa de E-mail (Infraestrutura): O envio de e-mails deve ser feito obrigatoriamente através de uma biblioteca de terceiros (como PHPMailer) utilizando uma conexão SMTP externa (ex: Gmail), para contornar bloqueios da hospedagem. (Sprint 4)

RNF06 - Arquitetura de Pagamento (Restrição): O fluxo de pagamento não deve depender de webhooks ou integrações de retorno complexas (devido aos bloqueios do servidor InfinityFree), devendo operar exclusivamente por redirecionamento de links. (Sprint 5)


 
## Sprints 2 semestre 2026

Sprint / Datas

Foco Principal / Entregas Esperadas: 

* Sprint 1 
26/08 a 09/09 Refinamento de UX/UI: Criação da barra de progresso no formulário e revisão da responsividade (Mobile-first). 

* Sprint 2
10/09 a 24/09 Lógica Avançada: Implementação do controle dinâmico de tempo (bloquear mais horários para carros grandes) e agendamento recorrente.

* Sprint 3
25/09 a 09/10 Painel do Admin: Desenvolvimento do Dashboard Financeiro e funcionalidade para atribuir lavagens a funcionários específicos.

* Sprint 4
10/10 a 24/10 Integrações: Configuração do Login Social (Google) e implementação de notificações de lembrete (E-mail).

* Sprint 5
25/10 a 08/11 Pagamentos e Avaliação: Integração da API de pagamentos (sinal) e criação da tela de avaliação de serviço pelo cliente.

* Sprint 6
09/11 a 23/11 Testes e Correções: Testes de usabilidade finais, correção de bugs, testes de estresse na agenda. Congelamento de código (Code Freeze).

* Sprint 7
24/11 a 30/11 Entrega Final: Ajustes no Plano de Ensino, geração de prints finais, preparação dos slides e ensaio para a banca.
 

