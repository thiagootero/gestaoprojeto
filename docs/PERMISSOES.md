# Permissoes e Perfis de Acesso - SGP Sementes do Vale

## Visao Geral dos Perfis

O sistema possui 6 perfis de usuario, definidos pelo campo `perfil` na tabela `users`:

| Perfil | Descricao |
|--------|-----------|
| `super_admin` | Acesso total e irrestrito ao sistema. Herda todas as permissoes do admin_geral. |
| `admin_geral` | Administrador geral da organizacao. CRUD completo em todos os modulos. |
| `diretor_projetos` | Diretor responsavel pela gestao de projetos. Gerencia projetos, metas, tarefas e valida entregas. |
| `diretor_operacoes` | Diretor responsavel pelas operacoes. Foco em validacao de prestacoes de contas. Acesso somente leitura na maioria dos modulos. |
| `coordenador_polo` | Coordenador de polo regional. Acesso filtrado pelos polos atribuidos. Trabalha com tarefas e prestacoes qualitativas. |
| `coordenador_financeiro` | Coordenador financeiro. Ve todos os projetos, trabalha com prestacoes financeiras. Tarefas filtradas por responsabilidade. |

---

## Hierarquia de Permissoes

```
super_admin (herda tudo de admin_geral)
  |
  admin_geral (acesso total)
  |
  +-- diretor_projetos (gerencia projetos, valida tarefas e prestacoes)
  |
  +-- diretor_operacoes (somente leitura + valida prestacoes)
  |
  +-- coordenador_polo (filtrado por polo, envia tarefas e prestacoes qualitativas)
  |
  +-- coordenador_financeiro (ve todos os projetos, envia prestacoes financeiras)
```

---

## Detalhamento por Perfil

### 1. super_admin

- Acesso total a todos os recursos e acoes do sistema
- Pode criar, editar e excluir qualquer registro
- Herda automaticamente permissoes de `admin_geral` via metodo `isAdminGeral()`

### 2. admin_geral

**Recursos - CRUD Completo:**
- Projetos (criar, ver, editar, excluir)
- Metas (criar, ver, editar, excluir)
- Tarefas (criar, ver, editar, excluir, validar comprovacao)
- Polos (criar, ver, editar, excluir)
- Financiadores (criar, ver, editar, excluir)
- Usuarios (criar, ver, editar, excluir)
- Etapas de Prestacao (criar, ver, editar, excluir, validar)

**Paginas de Projeto:**
- EditProjeto, EditProjetoPrestacao, EditProjetoMetas

**Cronograma Operacional (Metas/Tarefas):**
- Enviar tarefas para analise
- Analisar tarefas (aprovar ou devolver)
- Ver historico

**Cronograma Prestacao de Contas:**
- Enviar prestacoes para analise
- Analisar prestacoes (aprovar ou devolver)
- Ver historico

**Widgets no Dashboard:**
- TarefasEmAnalise (tarefas pendentes de analise)
- PrestacoesEmAnalise (prestacoes pendentes de analise)

---

### 3. diretor_projetos

**Recursos:**
- Projetos: criar, ver todos, editar (NAO exclui)
- Metas: CRUD completo
- Tarefas: CRUD completo, validar comprovacao
- Polos: ver, editar (NAO cria, NAO exclui)
- Financiadores: criar, ver, editar (NAO exclui)
- Usuarios: somente visualizar
- Etapas de Prestacao: CRUD completo, validar

**Paginas de Projeto:**
- EditProjeto, EditProjetoPrestacao, EditProjetoMetas

**Cronograma Operacional:**
- Enviar, Analisar (aprovar/devolver), Historico

**Cronograma Prestacao de Contas:**
- Enviar, Analisar (aprovar/devolver), Historico

**Widgets:** TarefasEmAnalise, PrestacoesEmAnalise

---

### 4. diretor_operacoes

**Recursos (somente leitura na maioria):**
- Projetos: somente ver
- Metas: somente ver
- Tarefas: ver, editar status/observacoes
- Polos: somente ver
- Financiadores: somente ver
- Usuarios: somente ver
- Etapas de Prestacao: ver, validar

**Paginas de Projeto:**
- NAO tem acesso a EditProjeto, EditProjetoPrestacao, EditProjetoMetas
- NAO ve Cronograma Operacional
- NAO ve Cronograma Prestacao de Contas (na sub-navegacao)

**Widgets:** PrestacoesEmAnalise (pode analisar prestacoes pelo dashboard)

> **Nota:** O diretor de operacoes valida prestacoes pelo widget no dashboard, mas nao acessa a pagina de cronograma de prestacao diretamente.

---

### 5. coordenador_polo

**Filtro de Dados:** So ve projetos, metas e tarefas dos polos atribuidos ao usuario + polos marcados como `is_geral`.

**Recursos:**
- Projetos: ver (filtrado por polo)
- Metas: ver (filtrado por polo)
- Tarefas: ver e editar (filtrado por polo)
- Polos: NAO tem acesso
- Financiadores: NAO tem acesso
- Usuarios: NAO tem acesso
- Etapas de Prestacao: NAO tem acesso direto

**Paginas de Projeto:**
- NAO tem EditProjeto, EditProjetoPrestacao, EditProjetoMetas

**Cronograma Operacional:**
- Enviar tarefas para analise
- Reenviar tarefas devolvidas
- Ver historico
- NAO pode Analisar (ve badge "Aguardando validacao")

**Cronograma Prestacao de Contas:**
- NAO tem acesso a esta pagina

**Widgets:** Nenhum widget de analise

---

### 6. coordenador_financeiro

**Filtro de Dados:** Ve todos os projetos. Metas e tarefas filtradas por responsabilidade (onde e responsavel ou co-responsavel).

**Recursos:**
- Projetos: ver todos
- Metas: ver (filtrado por responsabilidade em tarefas)
- Tarefas: ver e editar (filtrado por responsabilidade)
- Polos: NAO tem acesso
- Financiadores: NAO tem acesso
- Usuarios: NAO tem acesso
- Etapas de Prestacao: ver (filtrado por responsabilidade em tarefas do projeto)

**Paginas de Projeto:**
- NAO tem EditProjeto, EditProjetoPrestacao, EditProjetoMetas

**Cronograma Operacional:**
- NAO tem acesso a esta pagina

**Cronograma Prestacao de Contas:**
- Enviar prestacoes APENAS do tipo `financeira`
- Reenviar prestacoes financeiras devolvidas
- Ver historico
- NAO pode Analisar (ve badge "Aguardando validacao")
- Se tentar enviar prestacao qualitativa: erro "Coordenador financeiro so pode enviar prestacoes financeiras"

**Widgets:** Nenhum widget de analise

---

## Tabela Resumo de Acesso

### Recursos (CRUD)

| Recurso | super_admin | admin_geral | diretor_projetos | diretor_operacoes | coordenador_polo | coordenador_financeiro |
|---------|:-----------:|:-----------:|:----------------:|:-----------------:|:----------------:|:----------------------:|
| Projetos - Ver | Todos | Todos | Todos | Todos | Filtrado (polo) | Todos |
| Projetos - Criar | Sim | Sim | Sim | Nao | Nao | Nao |
| Projetos - Editar | Sim | Sim | Sim | Nao | Nao | Nao |
| Projetos - Excluir | Sim | Sim | Nao | Nao | Nao | Nao |
| Metas - Ver | Todas | Todas | Todas | Todas | Filtrado (polo) | Filtrado (responsavel) |
| Metas - Criar | Sim | Sim | Sim | Nao | Nao | Nao |
| Metas - Editar | Sim | Sim | Sim | Nao | Nao | Nao |
| Metas - Excluir | Sim | Sim | Sim | Nao | Nao | Nao |
| Tarefas - Ver | Todas | Todas | Todas | Todas | Filtrado (polo) | Filtrado (responsavel) |
| Tarefas - Criar | Sim | Sim | Sim | Nao | Nao | Nao |
| Tarefas - Editar | Sim | Sim | Sim | Status apenas | Filtrado (polo) | Filtrado (responsavel) |
| Tarefas - Excluir | Sim | Sim | Sim | Nao | Nao | Nao |
| Polos - Ver | Sim | Sim | Sim | Sim | Nao | Nao |
| Polos - Criar | Sim | Sim | Nao | Nao | Nao | Nao |
| Polos - Editar | Sim | Sim | Sim | Nao | Nao | Nao |
| Polos - Excluir | Sim | Sim | Nao | Nao | Nao | Nao |
| Financiadores - Ver | Sim | Sim | Sim | Sim | Nao | Nao |
| Financiadores - Criar | Sim | Sim | Sim | Nao | Nao | Nao |
| Financiadores - Editar | Sim | Sim | Sim | Nao | Nao | Nao |
| Financiadores - Excluir | Sim | Sim | Nao | Nao | Nao | Nao |
| Usuarios - Ver | Sim | Sim | Sim | Sim | Nao | Nao |
| Usuarios - Criar | Sim | Sim | Nao | Nao | Nao | Nao |
| Usuarios - Editar | Sim | Sim | Nao | Nao | Nao | Nao |
| Usuarios - Excluir | Sim | Sim | Nao | Nao | Nao | Nao |

### Paginas e Navegacao

| Pagina | super_admin | admin_geral | diretor_projetos | diretor_operacoes | coordenador_polo | coordenador_financeiro |
|--------|:-----------:|:-----------:|:----------------:|:-----------------:|:----------------:|:----------------------:|
| EditProjeto | Sim | Sim | Sim | Nao | Nao | Nao |
| EditProjetoPrestacao | Sim | Sim | Sim | Nao | Nao | Nao |
| EditProjetoMetas | Sim | Sim | Sim | Nao | Nao | Nao |
| Cronograma Operacional | Sim | Sim | Sim | Nao | Sim | Nao |
| Cronograma Prestacao | Sim | Sim | Sim | Nao | Nao | Sim |
| Calendario Projeto | Sim | Sim | Sim | Sim | Sim | Sim |

### Fluxo de Validacao - Acoes

| Acao | super_admin | admin_geral | diretor_projetos | diretor_operacoes | coordenador_polo | coordenador_financeiro |
|------|:-----------:|:-----------:|:----------------:|:-----------------:|:----------------:|:----------------------:|
| Enviar tarefa | Sim | Sim | Sim | - | Sim | - |
| Analisar tarefa (aprovar/devolver) | Sim | Sim | Sim | Nao | Nao | - |
| Enviar prestacao qualitativa | Sim | Sim | Sim | - | Sim* | Nao |
| Enviar prestacao financeira | Sim | Sim | Sim | - | Nao | Sim |
| Analisar prestacao (aprovar/devolver) | Sim | Sim | Sim | Sim** | Nao | Nao |
| Ver historico (tarefas) | Sim | Sim | Sim | - | Sim | - |
| Ver historico (prestacoes) | Sim | Sim | Sim | - | - | Sim |

> *coordenador_polo so pode enviar prestacoes qualitativas (se a pagina estiver acessivel)
> **diretor_operacoes analisa prestacoes pelo widget no dashboard
> `-` significa que o perfil nao tem acesso a pagina onde a acao esta disponivel

### Widgets no Dashboard

| Widget | super_admin | admin_geral | diretor_projetos | diretor_operacoes | coordenador_polo | coordenador_financeiro |
|--------|:-----------:|:-----------:|:----------------:|:-----------------:|:----------------:|:----------------------:|
| ProximasPrestacoes | Sim | Sim | Sim | Sim | Sim | Sim |
| ProximasPrestacoesInternas | Sim | Sim | Sim | Sim | Sim | Sim |
| ProgressoProjetos | Sim | Sim | Sim | Sim | Sim | Sim |
| TarefasPendentesValidacao | Sim | Sim | Sim | Sim | Sim | Sim |
| TarefasEmAnalise | Sim | Sim | Sim | Nao | Nao | Nao |
| PrestacoesEmAnalise | Sim | Sim | Sim | Sim | Nao | Nao |

---

## Fluxo de Status - Tarefas

```
[a_iniciar / em_andamento]
      |
      | Coordenador/Admin clica "Enviar"
      v
  [em_analise]  -----> Admin/Diretor Projetos clica "Analisar"
      |                         |
      |                    +----+----+
      |                    |         |
      |                 Aprovar   Devolver
      |                    |         |
      |                    v         v
      |              [realizado]  [devolvido]
      |                              |
      |                              | Coordenador clica "Reenviar"
      |                              v
      +<-----------------------------+
```

## Fluxo de Status - Prestacao de Contas

```
[pendente / em_elaboracao / enviada]
      |
      | Coordenador/Admin clica "Enviar"
      v
  [em_analise]  -----> Admin/Diretores clicam "Analisar"
      |                         |
      |                    +----+----+
      |                    |         |
      |                 Aprovar   Devolver
      |                    |         |
      |                    v         v
      |              [realizado]  [devolvido]
      |                              |
      |                              | Coordenador clica "Reenviar"
      |                              v
      +<-----------------------------+
```

---

## Arquivos Relevantes

| Arquivo | Funcao |
|---------|--------|
| `app/Models/User.php` | Metodos de verificacao de perfil (isAdminGeral, isDiretorProjetos, etc.) |
| `app/Policies/ProjetoPolicy.php` | Permissoes de projeto |
| `app/Policies/TarefaPolicy.php` | Permissoes de tarefa |
| `app/Policies/MetaPolicy.php` | Permissoes de meta |
| `app/Policies/PoloPolicy.php` | Permissoes de polo |
| `app/Policies/FinanciadorPolicy.php` | Permissoes de financiador |
| `app/Policies/UserPolicy.php` | Permissoes de usuario |
| `app/Policies/EtapaPrestacaoPolicy.php` | Permissoes de etapa de prestacao |
| `app/Filament/Resources/ProjetoResource.php` | Navegacao e filtros por perfil |
| `app/Filament/Resources/ProjetoResource/Pages/CronogramaOperacional.php` | Acoes de tarefa (enviar, analisar, historico) |
| `app/Filament/Resources/ProjetoResource/Pages/CronogramaPrestacaoContas.php` | Acoes de prestacao (enviar, analisar, historico) |
| `app/Filament/Widgets/TarefasEmAnalise.php` | Widget de tarefas em analise |
| `app/Filament/Widgets/PrestacoesEmAnalise.php` | Widget de prestacoes em analise |
