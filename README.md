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

##Requisitos 

1. Requisitos Funcionais (RF)

RF01: O sistema deve permitir o cadastro e login de clientes utilizando e-mail e senha.

RF02: O sistema deve permitir que o cliente agende uma lavagem escolhendo o tipo de serviço (ex: Simples, Completa) e o porte do veículo.

RF03: O sistema deve exibir um calendário para seleção de data e horário, bloqueando automaticamente os horários já reservados.

RF04: O sistema deve calcular e exibir o valor total do serviço de forma dinâmica antes da confirmação do agendamento.

RF05: O sistema deve possuir um painel administrativo (Dashboard) para o dono do lava-jato visualizar os agendamentos do dia atual.

RF06: O painel administrativo deve permitir a alteração do status do agendamento (Ex: "Aguardando", "Em Lavagem", "Finalizado", "Cancelado").

RF07: O sistema deve permitir que o cliente avalie o serviço (1 a 5 estrelas) após o status constar como "Finalizado".

2. Requisitos Não Funcionais (RNF)

RNF01 (Usabilidade): A interface web deve ser totalmente responsiva (Mobile-First), adaptando-se perfeitamente a telas de celulares e computadores via HTML/CSS.

RNF02 (Hospedagem e Arquitetura): O sistema deve ser desenvolvido para rodar na web, hospedado no InfinityFree, utilizando a linguagem PHP e banco de dados MySQL.

RNF03 (Desempenho): As consultas ao banco de dados no painel administrativo devem ser limitadas (ex: paginação ou filtro por dia) para evitar sobrecarga de CPU e suspensão da conta gratuita.

RNF04 (Comunicação): O envio de e-mails de confirmação não deve usar a função nativa do servidor, mas sim uma biblioteca externa (como PHPMailer) via SMTP autêntico.

RNF05 (Segurança): As senhas dos usuários devem ser obrigatoriamente criptografadas (usando hash) antes de serem salvas no banco de dados.


 
##Sprints 2 semestre 2026

Sprint / Datas

Foco Principal - Entregas Esperadas: 

Sprint 1 
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
 

