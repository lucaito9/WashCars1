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

---

## 📄 Licença

Este projeto foi desenvolvido exclusivamente para fins acadêmicos e de portfólio.
Copyright © 2024 **Wash Cars**. Todos os direitos reservados.
