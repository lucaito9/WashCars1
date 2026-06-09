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
 
* ## 📑 Documentação de Governança e Qualidade (2ª Avaliação Parcial - PI-III)

Este espaço consolida as evidências técnicas e de gestão de projetos para a Unidade 2, demonstrando a rastreabilidade entre o planejamento e o MVP funcional da **Plataforma de Gestão para Lava Jatos**.

---

### 1. Quadro 1: Comparativo de Evidências de Entrega (Sprints #02 a #05)

| Tipo de Evidência | Sprint #02 (Concepção/Inicial) | Sprint #04 e #05 (Maturidade Técnica) |
| :--- | :--- | :--- |
| **Vídeos Demonstrativos** | Navegação em protótipos e fluxos iniciais de telas no Figma. | Demonstração do sistema PHP funcional (Filtros, caixa e integração WhatsApp). |
| **Prints de Tela** | Wireframes, mockups e representações preliminares da interface. | Capturas do sistema funcional operando no navegador com registros de sucesso. |
| **Registros de Devolutiva** | Reuniões de alinhamento de escopo com os microempreendedores parceiros. | Feedbacks de validação interna, testes de usabilidade e encerramento da Sprint. |

---

### 2. Protocolo de Testes e Validação (Conceito "So What?")
*Competência C19 (Garantia de Qualidade) - Framework Fases_ACE*

| Funcionalidade Auditada | Tipo de Teste Realizado | Resultado Esperado | Devolutiva (Feedback) / Impacto (**So What?**) |
| :--- | :--- | :--- | :--- |
| **Cálculo de Lucro Líquido** | Regra de Negócio / Lógica | Subtrair despesas do faturamento total de forma exata. | **SUCESSO:** Impede que o dono do lava jato tome decisões falsas baseadas em um caixa irreal. |
| **Filtro Combinado (Data + Status)** | Caso de Teste Funcional | Isolar na tela apenas veículos agendados para o dia e status selecionados. | **SUCESSO:** Evita gargalos no pátio físico do lava jato, garantindo que o lavador foque no veículo correto do dia. |
| **Notificação via WhatsApp** | Critério de Aceitação / Integração | Gerar link dinâmico para o WhatsApp com mensagem de "Carro Pronto". | **SUCESSO:** Aumenta a rotatividade de vagas no estabelecimento, agilizando a retirada do veículo pelo cliente. |

---

### 3. Quadro 2: Plano de Contingência Estruturado
*Competência C21 (Gerenciamento de Projetos)*

| Impedimento Identificado | Ação de Contingência | Impacto no Cronograma (**So What?**) |
| :--- | :--- | :--- |
| Dificuldade técnica com queries SQL dinâmicas complexas no PHP. | Centralização do tratamento de filtros em estruturas condicionais simplificadas. | Atraso de 2 dias na Sprint para refatoração, mitigado sem afetar o prazo de entrega do MVP. |
| Ausência/indisponibilidade temporária de membro da equipe. | Redistribuição das tarefas de interface via GitHub Projects com pareamento. | Impacto nulo no cronograma devido à transparência e governança ágil no ALM. |

---

### 4. Próximos Passos (Planejamento Sprint #06)
* **O Que:** Estabilização do ambiente, implementação de criptografia de senhas e fechamento do MVP.
* **Como:** Execução dos últimos Critérios de Aceitação e testes de stress de dados.
* **Quem:** Alocação definitiva de responsabilidades visível no Sprint Backlog do GitHub Projects.

---

## 📄 Licença

Este projeto foi desenvolvido exclusivamente para fins acadêmicos e de portfólio.
Copyright © 2024 **Wash Cars**. Todos os direitos reservados.
