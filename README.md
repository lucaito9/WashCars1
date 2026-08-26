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

## 💻 Tecnologias Utilizadas

* **Frontend:** HTML5, CSS3 (Custom Dark Theme), JavaScript e Bootstrap 5 para responsividade.
* **Backend:** PHP 8+ (Processamento de rotas e lógica de negócio).
* **Banco de Dados:** MySQL com camada de segurança PDO para prevenção de SQL Injection.
* **Hospedagem:** Ambiente de produção configurado na plataforma InfinityFree.

---

## 👥 Equipe de Desenvolvimento

O projeto foi fruto de uma colaboração focada em excelência técnica:

* **Lucas Germano Braga Ito** - *Desenvolvedor Full-Stack / Tech Lead*
    * Responsável pela arquitetura do banco de dados, lógica de backend em PHP, sistema de sessões e liderança técnica do projeto.
    * 📧 [Seu Email Aqui] | [Seu LinkedIn Aqui]

* **Cauã Ito** - *Frontend & UX Design*
    * Atuou no desenvolvimento da interface visual, estilização Dark Edition e usabilidade das telas do cliente.

* **Gustavo Rocha** - *Backend & QA (Quality Assurance)*
    * Focado na implementação de regras de negócio, validações de formulários e testes de integridade do sistema.
 
Sprints 2 semestre 2026

Sprint / Datas
Foco Principal - Entregas Esperadas: Sprint 1 
26/08 a 09/09 Refinamento de UX/UICriação da barra de progresso no formulário e revisão da responsividade (Mobile-first). 

Sprint 2
10/09 a 24/09 Lógica Avançada: Implementação do controle dinâmico de tempo (bloquear mais horários para carros grandes) e agendamento recorrente.

Sprint 3
25/09 a 09/10 Painel do Admin: Desenvolvimento do Dashboard Financeiro e funcionalidade para atribuir lavagens a funcionários específicos.

Sprint 4
10/10 a 24/10 Integrações: Configuração do Login Social (Google) e implementação de notificações de lembrete (E-mail).

Sprint 5
25/10 a 08/11 Pagamentos e Avaliação: Integração da API de pagamentos (sinal) e criação da tela de avaliação de serviço pelo cliente.

Sprint 6
09/11 a 23/11 Testes e Correções: Testes de usabilidade finais, correção de bugs, testes de estresse na agenda. Congelamento de código (Code Freeze).

Sprint 7
24/11 a 30/11 Entrega Final: Ajustes no Plano de Ensino, geração de prints finais, preparação dos slides e ensaio para a banca.
 

