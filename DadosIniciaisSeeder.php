<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Polo;
use App\Models\Financiador;
use App\Models\Projeto;
use App\Models\ProjetoFinanciador;
use App\Models\EtapaPrestacao;
use App\Models\Meta;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DadosIniciaisSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. POLOS
        // =====================================================================
        $poloBandinha   = Polo::create(['nome' => 'Bandinha', 'cidade' => 'Salinas', 'ativo' => true]);
        $poloNinheira   = Polo::create(['nome' => 'Ninheira', 'cidade' => 'Salinas', 'ativo' => true]);
        $poloSalinas    = Polo::create(['nome' => 'Salinas', 'cidade' => 'Salinas', 'ativo' => true]);
        $poloTaiobeiras = Polo::create(['nome' => 'Taiobeiras', 'cidade' => 'Taiobeiras', 'ativo' => true]);

        // Mapa para referência rápida
        $polos = [
            'Bandinha'   => $poloBandinha->id,
            'Ninheira'   => $poloNinheira->id,
            'Salinas'    => $poloSalinas->id,
            'Taiobeiras' => $poloTaiobeiras->id,
        ];

        // =====================================================================
        // 2. FINANCIADORES
        // =====================================================================
        $fiLIE   = Financiador::create(['nome' => 'Lei de Incentivo ao Esporte', 'tipo' => 'publico']);
        $fiFIAS  = Financiador::create(['nome' => 'FIA Salinas', 'tipo' => 'publico']);
        $fiFIAN  = Financiador::create(['nome' => 'FIA Ninheira', 'tipo' => 'publico']);
        $fiMINC  = Financiador::create(['nome' => 'Ministério da Cultura', 'tipo' => 'publico']);
        $fiPNAB  = Financiador::create(['nome' => 'PNAB', 'tipo' => 'publico']);
        $fiCE    = Financiador::create(['nome' => 'Criança Esperança / Globo', 'tipo' => 'privado']);
        $fiLATI  = Financiador::create(['nome' => 'Latimpacto', 'tipo' => 'internacional']);
        $fiSV    = Financiador::create(['nome' => 'Sertão Verde / Sonho de Maria', 'tipo' => 'privado']);
        $fiCPMS  = Financiador::create(['nome' => 'CAPEMISA', 'tipo' => 'privado']);

        // =====================================================================
        // 3. USUÁRIO ADMIN
        // =====================================================================
        $admin = User::create([
            'name'     => 'Thiago',
            'email'    => 'admin@sementesdovale.org.br',
            'password' => Hash::make('password'),
            'perfil'   => 'super_admin',
            'ativo'    => true,
        ]);

        // =====================================================================
        // HELPER: Resolver polo_id a partir de texto da planilha
        // =====================================================================
        $resolverPolo = function(?string $texto) use ($polos) {
            if (!$texto) return null;
            $texto = trim($texto);
            if (in_array($texto, ['NSA', 'NSA ', '', 'Bandinha e Ninheira', 'Ninheira e Bandinha', 'Bandinha, Ninheira e Salinas'])) {
                return null; // Tarefa administrativa ou multi-polo
            }
            foreach ($polos as $nome => $id) {
                if (stripos($texto, $nome) !== false) return $id;
            }
            return null;
        };

        $resolverStatus = function(?string $texto): string {
            if (!$texto) return 'a_iniciar';
            $texto = trim(mb_strtolower($texto));
            return match(true) {
                str_contains($texto, 'conclu') => 'concluido',
                str_contains($texto, 'andamento') => 'em_andamento',
                str_contains($texto, 'não realizado') || str_contains($texto, 'nao realizado') => 'nao_realizado',
                str_contains($texto, 'cancelad') => 'cancelado',
                default => 'a_iniciar',
            };
        };

        $resolverStatusMeta = function(?string $texto): string {
            if (!$texto) return 'a_iniciar';
            $texto = trim(mb_strtolower($texto));
            return match(true) {
                str_contains($texto, 'alcançada') || str_contains($texto, 'alcancada') => 'alcancada',
                str_contains($texto, 'não alcançada') || str_contains($texto, 'nao alcancada') => 'nao_alcancada',
                str_contains($texto, 'andamento') => 'em_andamento',
                str_contains($texto, 'parcial') => 'parcialmente_alcancada',
                default => 'a_iniciar',
            };
        };

        // =====================================================================
        // HELPER: Criar etapas de prestação
        // =====================================================================
        $criarEtapa = function(int $pfId, int $num, string $tipo, string $dataLimite, string $status = 'pendente', ?string $dataEnvio = null) {
            EtapaPrestacao::create([
                'projeto_financiador_id' => $pfId,
                'numero_etapa'           => $num,
                'tipo'                   => $tipo,
                'data_limite'            => $dataLimite,
                'status'                 => $status,
                'data_envio'             => $dataEnvio,
            ]);
        };

        // =====================================================================
        // 4. PROJETO: CAMPEÕES DO SERTÃO 3 (antes era II, agora Ano 3)
        // =====================================================================
        $pCampeoes = Projeto::create([
            'nome'                   => 'Campeões do Sertão 3',
            'descricao'              => 'Projeto esportivo com oficinas de balé, futsal e vôlei nos 4 polos.',
            'data_inicio'            => '2025-07-01',
            'data_encerramento'      => '2026-07-01',
            'encerramento_contratos' => '2026-09-30',
            'status'                 => 'em_execucao',
        ]);
        $pCampeoes->polos()->attach([$poloBandinha->id, $poloNinheira->id, $poloSalinas->id, $poloTaiobeiras->id]);

        // Financiador: CAPEMISA
        $pfCampeoesCPMS = ProjetoFinanciador::create([
            'projeto_id'          => $pCampeoes->id,
            'financiador_id'      => $fiCPMS->id,
            'data_inicio_contrato'=> '2025-07-01',
            'data_fim_contrato'   => '2026-07-01',
            'pc_interna_dia'      => 10,
            'periodicidade'       => 'semestral',
        ]);
        $criarEtapa($pfCampeoesCPMS->id, 1, 'qualitativa', '2025-11-10', 'enviada');
        $criarEtapa($pfCampeoesCPMS->id, 2, 'qualitativa', '2026-10-01');
        $criarEtapa($pfCampeoesCPMS->id, 1, 'financeira', '2025-11-10', 'enviada');
        $criarEtapa($pfCampeoesCPMS->id, 2, 'financeira', '2026-10-01');

        // Metas e Tarefas - Campeões do Sertão
        $m1 = Meta::create(['projeto_id' => $pCampeoes->id, 'numero' => 1, 'descricao' => 'Melhorar, em média, o condicionamento físico dos beneficiários', 'indicador' => 'Melhora da capacidade física', 'meio_verificacao' => 'Relatório com os resultados de avaliação física dos beneficiários', 'status' => 'alcancada']);

        $tarefasM1 = [
            ['1.1', 'Aplicar o formulário de avaliação física inicial balé', 'Educador e Coordenador (Cris e Ana)', 'Bandinha', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.2', 'Aplicar o formulário de avaliação física inicial futsal', 'Educador e Coordenador (Jadson e Ana)', 'Bandinha', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.3', 'Aplicar o formulário de avaliação física inicial vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Bandinha', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.4', 'Aplicar o formulário de avaliação física inicial futsal', 'Educador e Coordenador (Jadson e Ana)', 'Ninheira', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.5', 'Aplicar o formulário de avaliação física inicial vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Ninheira', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.6', 'Aplicar formulário de avaliação física inicial balé', 'Educador e Coordenador (Lucélia e Ana)', 'Salinas', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.7', 'Aplicar formulário de avaliação inicial balé', 'Educador e Coordenador (Lucélia e Ana)', 'Taiobeiras', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.8', 'Aplicar o formulário de avaliação física final balé', 'Educador e Coordenador (Cris e Ana)', 'Bandinha', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.9', 'Aplicar o formulário de avaliação física final futsal', 'Educador e Coordenador (Jadson e Ana)', 'Bandinha', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.10', 'Aplicar o formulário de avaliação física final vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Bandinha', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.11', 'Aplicar o formulário de avaliação física final futsal', 'Educador e Coordenador (Jadson e Ana)', 'Ninheira', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.12', 'Aplicar o formulário de avaliação física final vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Ninheira', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.13', 'Aplicar formulário de avaliação física final balé', 'Educador e Coordenador (Luciléia e Ana)', 'Salinas', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.14', 'Elaborar relatório final das avaliações', 'Diretor de Projetos (Ana)', null, '2025-06-01', '2025-06-30', 'concluido'],
        ];

        foreach ($tarefasM1 as $t) {
            Tarefa::create([
                'meta_id'     => $m1->id,
                'numero'      => $t[0],
                'descricao'   => $t[1],
                'responsavel' => $t[2],
                'polo_id'     => $t[3] ? $resolverPolo($t[3]) : null,
                'data_inicio' => $t[4],
                'data_fim'    => $t[5],
                'status'      => $t[6],
            ]);
        }

        $m2 = Meta::create(['projeto_id' => $pCampeoes->id, 'numero' => 2, 'descricao' => 'Proporcionar melhores resultados na vivência e prática esportiva dos beneficiados', 'indicador' => 'Evolução média dos beneficiários aferindo a melhora nas práticas esportivas', 'meio_verificacao' => 'Relatório elaborado pela equipe do projeto com a evolução dos beneficiários', 'status' => 'alcancada']);

        $tarefasM2 = [
            ['2.1', 'Aplicar o formulário de avaliação tática e técnica inicial balé', 'Educador e Coordenador (Cris e Ana)', 'Bandinha', '2025-03-03', '2025-03-31', 'concluido'],
            ['2.2', 'Aplicar o formulário de avaliação tática e técnica inicial futsal', 'Educador e Coordenador (Jadson e Ana)', 'Bandinha', '2025-03-03', '2025-03-31', 'concluido'],
            ['2.3', 'Aplicar o formulário de avaliação tática e técnica inicial vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Bandinha', '2025-03-03', '2025-03-31', 'concluido'],
            ['2.4', 'Aplicar o formulário de avaliação tática e técnica inicial futsal', 'Educador e Coordenador (Jadson e Ana)', 'Ninheira', '2025-03-03', '2025-03-31', 'concluido'],
            ['2.5', 'Aplicar o formulário de avaliação tática e técnica inicial vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Ninheira', '2025-03-03', '2025-03-31', 'concluido'],
            ['2.6', 'Aplicar o formulário de avaliação tática e técnica inicial balé', 'Educador e Coordenador (Lucélia e Ana)', 'Salinas', '2025-03-03', '2025-03-31', 'concluido'],
            ['2.7', 'Aplicar o formulário de avaliação tática e técnica inicial balé', 'Educador e Coordenador (Lucélia e Ana)', 'Taiobeiras', '2025-03-03', '2025-03-31', 'concluido'],
            ['2.8', 'Aplicar o formulário de avaliação tática e técnica final balé', 'Educador e Coordenador (Cris e Ana)', 'Bandinha', '2025-05-01', '2025-05-31', 'concluido'],
            ['2.9', 'Aplicar o formulário de avaliação tática e técnica final futsal', 'Educador e Coordenador (Jadson e Ana)', 'Bandinha', '2025-05-01', '2025-05-31', 'concluido'],
            ['2.10', 'Aplicar o formulário de avaliação tática e técnica final vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Bandinha', '2025-05-01', '2025-05-31', 'concluido'],
            ['2.11', 'Aplicar o formulário de avaliação tática e técnica final futsal', 'Educador e Coordenador (Jadson e Ana)', 'Ninheira', '2025-05-01', '2025-05-31', 'concluido'],
            ['2.12', 'Aplicar o formulário de avaliação tática e técnica final vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Ninheira', '2025-05-01', '2025-05-31', 'concluido'],
            ['2.13', 'Aplicar o formulário de avaliação tática e técnica final balé', 'Educador e Coordendor (Lucélia e Ana)', 'Salinas', '2025-05-01', '2025-05-31', 'concluido'],
            ['2.14', 'Elaborar relatório final das avaliações', 'Diretora de Projetos (Ana)', null, '2025-06-01', '2025-06-30', 'concluido'],
        ];
        foreach ($tarefasM2 as $t) {
            Tarefa::create(['meta_id' => $m2->id, 'numero' => $t[0], 'descricao' => $t[1], 'responsavel' => $t[2], 'polo_id' => $t[3] ? $resolverPolo($t[3]) : null, 'data_inicio' => $t[4], 'data_fim' => $t[5], 'status' => $t[6]]);
        }

        $m3 = Meta::create(['projeto_id' => $pCampeoes->id, 'numero' => 3, 'descricao' => 'Aprimoramento dos profissionais do projeto', 'indicador' => 'Participação das reuniões pedagógicas', 'meio_verificacao' => 'Fotos e relatórios de presença das reuniões pedagógicas', 'status' => 'em_andamento']);

        $tarefasM3 = [
            ['3.1', 'Agendar reunião com membros do projeto', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-01-27', '2025-01-27', 'concluido'],
            ['3.2', 'Elaborar atas de reunião', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-01-27', '2025-01-27', 'concluido'],
            ['3.3', 'Fotografar as reuniões pedagógicas', 'Fotógrafa (Tainá)', 'Bandinha', '2025-01-27', '2025-01-27', 'concluido'],
            ['3.4', 'Subir fotos no Sistema ISG', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-01-31', '2025-01-31', 'concluido'],
            ['3.5', 'Agendar reunião com membros do projeto', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-02-21', '2025-02-21', 'concluido'],
            ['3.6', 'Elaborar atas de reunião', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-02-21', '2025-02-21', 'concluido'],
            ['3.7', 'Fotografar as reuniões pedagógicas', 'Fotógrafa (Tainá)', 'Bandinha', '2025-02-21', '2025-02-21', 'concluido'],
            ['3.8', 'Subir fotos no drive', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-02-28', '2025-02-28', 'concluido'],
            ['3.9', 'Agendar reunião com membros do projeto', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-03-21', '2025-03-21', 'concluido'],
            ['3.10', 'Elaborar atas de reunião', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-03-21', '2025-03-21', 'concluido'],
            ['3.11', 'Fotografar as reuniões pedagógicas', 'Fotógrafa (Tainá)', 'Bandinha', '2025-03-21', '2025-03-21', 'concluido'],
            ['3.12', 'Subir fotos no drive', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-03-28', '2025-03-28', 'concluido'],
            ['3.13', 'Agendar reunião com membros do projeto', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-04-25', '2025-04-25', 'nao_realizado'],
            ['3.14', 'Elaborar atas de reunião', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-04-25', '2025-04-25', 'nao_realizado'],
            ['3.15', 'Fotografar as reuniões pedagógicas', 'Fotógrafa (Tainá)', 'Bandinha', '2025-04-25', '2025-04-25', 'nao_realizado'],
            ['3.16', 'Subir fotos no drive', 'Coordenadora de Projetos (Ana)', 'Bandinha', '2025-04-30', '2025-04-30', 'nao_realizado'],
        ];
        foreach ($tarefasM3 as $t) {
            Tarefa::create(['meta_id' => $m3->id, 'numero' => $t[0], 'descricao' => $t[1], 'responsavel' => $t[2], 'polo_id' => $t[3] ? $resolverPolo($t[3]) : null, 'data_inicio' => $t[4], 'data_fim' => $t[5], 'status' => $t[6]]);
        }

        $m4 = Meta::create(['projeto_id' => $pCampeoes->id, 'numero' => 4, 'descricao' => 'Alcançar, em média, 70% na taxa de frequência dos alunos', 'indicador' => 'Percentual de presença', 'meio_verificacao' => 'Relatório com tabulação de frequência das turmas', 'status' => 'alcancada']);

        foreach (['Bandinha','Ninheira','Salinas','Taiobeiras'] as $polo) {
            foreach ([['2025-02-25','concluido'],['2025-03-25','concluido'],['2025-04-29','concluido'],['2025-05-27','concluido']] as $i => $d) {
                $num = '4.' . (($i + 1) + (array_search($polo, ['Bandinha','Ninheira','Salinas','Taiobeiras']) * 4));
                Tarefa::create(['meta_id' => $m4->id, 'numero' => $num, 'descricao' => 'Verificar o alcance de 70% de frequência nas oficinas', 'responsavel' => 'Coordenador', 'polo_id' => $resolverPolo($polo), 'data_fim' => $d[0], 'status' => $d[1]]);
            }
        }
        Tarefa::create(['meta_id' => $m4->id, 'numero' => '4.17', 'descricao' => 'Elaborar relatório final de tabulação de frequência', 'responsavel' => 'Diretor de Projetos (Ana)', 'polo_id' => null, 'data_fim' => '2025-05-31', 'status' => 'a_iniciar']);

        $m5 = Meta::create(['projeto_id' => $pCampeoes->id, 'numero' => 5, 'descricao' => 'Atender 90% dos beneficiários do projeto matriculados no sistema público de ensino', 'indicador' => '121 beneficiários participando das oficinas nos polos', 'meio_verificacao' => 'Relatório com a lista de beneficiários e as escolas deles', 'status' => 'alcancada']);
        Tarefa::create(['meta_id' => $m5->id, 'numero' => '5.1', 'descricao' => 'Reunir documentos e subir no drive', 'responsavel' => 'Diretor de Projetos (Ana)', 'polo_id' => null, 'data_fim' => '2025-05-31', 'status' => 'concluido']);

        // =====================================================================
        // 5. PROJETO: FORMANDO CAMPEÕES 3
        // =====================================================================
        $pFormando = Projeto::create([
            'nome'                   => 'Formando Campeões 3',
            'descricao'              => 'Projeto esportivo com oficinas de muay thai, futsal e vôlei.',
            'data_inicio'            => '2025-07-01',
            'data_encerramento'      => '2026-07-01',
            'encerramento_contratos' => '2026-09-30',
            'status'                 => 'em_execucao',
        ]);
        $pFormando->polos()->attach([$poloSalinas->id, $poloNinheira->id, $poloTaiobeiras->id]);

        $pfFormandoCPMS = ProjetoFinanciador::create([
            'projeto_id' => $pFormando->id, 'financiador_id' => $fiCPMS->id,
            'data_inicio_contrato' => '2025-07-01', 'data_fim_contrato' => '2026-07-01',
            'pc_interna_dia' => 10, 'periodicidade' => 'semestral',
        ]);
        $criarEtapa($pfFormandoCPMS->id, 1, 'qualitativa', '2025-11-10', 'enviada');
        $criarEtapa($pfFormandoCPMS->id, 2, 'qualitativa', '2026-10-01');
        $criarEtapa($pfFormandoCPMS->id, 1, 'financeira', '2025-11-10', 'enviada');
        $criarEtapa($pfFormandoCPMS->id, 2, 'financeira', '2026-10-01');

        // Meta 1 Formando: Condicionamento físico
        $fm1 = Meta::create(['projeto_id' => $pFormando->id, 'numero' => 1, 'descricao' => 'Melhorar, em média, o condicionamento físico dos beneficiários', 'indicador' => 'Melhora da capacidade física', 'meio_verificacao' => 'Relatório com os resultados de avaliação física dos beneficiários', 'status' => 'alcancada']);
        $ftM1 = [
            ['1.1', 'Aplicar o formulário de avaliação física inicial muay thai', 'Educador e Coordenador (Brenda e Ana)', 'Salinas', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.2', 'Aplicar o formulário de avaliação física inicial futsal', 'Educador e Coordenador', 'Salinas', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.3', 'Aplicar o formulário de avaliação física inicial vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Salinas', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.4', 'Aplicar o formulário de avaliação física inicial futsal', 'Educador e Coordenador (Jadson e Ana)', 'Ninheira', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.5', 'Aplicar o formulário de avaliação física inicial vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Ninheira', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.6', 'Aplicar o formulário de avaliação física inicial muay thai', 'Educador e Coordenador (Brenda e Ana)', 'Taiobeiras', '2025-03-03', '2025-03-31', 'concluido'],
            ['1.7', 'Aplicar o formulário de avaliação física final muay thai', 'Educador e Coordenador (Cris e Ana)', 'Salinas', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.8', 'Aplicar o formulário de avaliação física final futsal', 'Educador e Coordenador (Robertinha e Ana)', 'Salinas', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.9', 'Aplicar o formulário de avaliação física final vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Salinas', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.10', 'Aplicar o formulário de avaliação física final futsal', 'Educador e Coordenador (Jadson e Ana)', 'Ninheira', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.11', 'Aplicar o formulário de avaliação física final vôlei', 'Educador e Coordenador (Xayane e Ana)', 'Ninheira', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.12', 'Aplicar o formulário de avaliação física final muay thai', 'Educador e Coordenador (Brenda e Ana)', 'Taiobeiras', '2025-05-01', '2025-05-31', 'concluido'],
            ['1.13', 'Elaborar relatório final das avaliações', 'Diretora de Projetos (Ana)', null, '2025-06-01', '2025-06-30', 'concluido'],
        ];
        foreach ($ftM1 as $t) {
            Tarefa::create(['meta_id' => $fm1->id, 'numero' => $t[0], 'descricao' => $t[1], 'responsavel' => $t[2], 'polo_id' => $t[3] ? $resolverPolo($t[3]) : null, 'data_inicio' => $t[4], 'data_fim' => $t[5], 'status' => $t[6]]);
        }

        // Meta 2 Formando: Reuniões pedagógicas (resumido)
        $fm2 = Meta::create(['projeto_id' => $pFormando->id, 'numero' => 2, 'descricao' => 'Aprimoramento dos profissionais envolvidos no projeto', 'indicador' => 'Participação das reuniões pedagógicas', 'meio_verificacao' => 'Fotos e relatórios de presença das reuniões pedagógicas', 'status' => 'alcancada']);
        // Tarefas repetitivas de reunião (Bandinha jan-abr + Salinas jan-abr)
        $meses = [['01','27','31'],['02','21','28'],['03','28','31'],['04','25','30']];
        foreach ($meses as $i => $m) {
            $base = ($i * 4) + 1;
            Tarefa::create(['meta_id' => $fm2->id, 'numero' => "2.{$base}", 'descricao' => 'Agendar reunião com membros do projeto', 'responsavel' => 'Coordenadora de Projetos (Poliana)', 'polo_id' => $poloBandinha->id, 'data_fim' => "2025-{$m[0]}-{$m[1]}", 'status' => 'concluido']);
            Tarefa::create(['meta_id' => $fm2->id, 'numero' => "2." . ($base+1), 'descricao' => 'Elaborar atas de reunião', 'responsavel' => 'Supervisora de Projetos (Ana)', 'polo_id' => null, 'data_fim' => "2025-{$m[0]}-{$m[1]}", 'status' => 'concluido']);
            Tarefa::create(['meta_id' => $fm2->id, 'numero' => "2." . ($base+2), 'descricao' => 'Fotografar as reuniões pedagógicas', 'responsavel' => 'Fotógrafa (Tainá)', 'polo_id' => $poloBandinha->id, 'data_fim' => "2025-{$m[0]}-{$m[1]}", 'status' => 'concluido']);
            Tarefa::create(['meta_id' => $fm2->id, 'numero' => "2." . ($base+3), 'descricao' => 'Subir fotos no ISG', 'responsavel' => 'Diretora de Projetos (Ana)', 'polo_id' => null, 'data_fim' => "2025-{$m[0]}-{$m[2]}", 'status' => 'concluido']);
        }

        // Meta 3 Formando: Turma feminina
        Meta::create(['projeto_id' => $pFormando->id, 'numero' => 3, 'descricao' => 'Conseguir formar 1 turma exclusiva de futsal feminino', 'indicador' => 'Formação da turma', 'meio_verificacao' => 'Lista de Beneficiários', 'status' => 'alcancada']);

        // Meta 4 Formando: Frequência
        $fm4 = Meta::create(['projeto_id' => $pFormando->id, 'numero' => 4, 'descricao' => 'Alcançar, em média, 70% na taxa de frequência dos alunos', 'indicador' => 'Percentual de presença dos alunos', 'meio_verificacao' => 'Relatório com tabulação de frequência das turmas', 'status' => 'alcancada']);
        foreach (['Salinas','Ninheira','Taiobeiras'] as $polo) {
            foreach ([['02-28','concluido'],['03-31','concluido'],['04-30','concluido']] as $i => $d) {
                Tarefa::create(['meta_id' => $fm4->id, 'numero' => '4.' . ($i+1), 'descricao' => 'Verificar o alcance de 70% de frequência nas oficinas', 'responsavel' => 'Coordenadora Pedagógica e Supervisora de Projetos', 'polo_id' => $resolverPolo($polo), 'data_fim' => "2025-{$d[0]}", 'status' => $d[1]]);
            }
        }
        Tarefa::create(['meta_id' => $fm4->id, 'numero' => '4.10', 'descricao' => 'Relatório final de tabulação de frequência', 'responsavel' => 'Diretora de Projetos', 'polo_id' => null, 'data_fim' => '2025-05-31', 'status' => 'concluido']);

        // Meta 5 Formando: 90% matriculados
        Meta::create(['projeto_id' => $pFormando->id, 'numero' => 5, 'descricao' => 'Atender 90% dos beneficiários do projeto matriculados no sistema público de ensino', 'indicador' => 'Participação nos polos de no mínimo 81 crianças e adolescentes', 'meio_verificacao' => 'Relatório com a lista de beneficiários e as escolas deles', 'status' => 'alcancada']);

        // =====================================================================
        // 6. PROJETO: CULTURA DO ESPORTE
        // =====================================================================
        $pCultEsporte = Projeto::create([
            'nome'                   => 'Cultura do Esporte',
            'descricao'              => 'Projeto esportivo financiado pela Lei de Incentivo ao Esporte.',
            'data_inicio'            => '2025-08-05',
            'data_encerramento'      => '2026-08-05',
            'encerramento_contratos' => '2026-12-31',
            'status'                 => 'em_execucao',
        ]);
        $pCultEsporte->polos()->attach([$poloBandinha->id, $poloNinheira->id]);

        $pfCultEsporteLIE = ProjetoFinanciador::create([
            'projeto_id' => $pCultEsporte->id, 'financiador_id' => $fiLIE->id,
            'data_inicio_contrato' => '2025-08-05', 'data_fim_contrato' => '2026-08-05',
            'data_prorrogacao' => '2026-02-28',
            'pc_interna_dia' => 10, 'pc_interna_dia_fin' => 10,
            'periodicidade' => 'semestral',
        ]);
        $criarEtapa($pfCultEsporteLIE->id, 1, 'qualitativa', '2025-12-10', 'enviada');
        $criarEtapa($pfCultEsporteLIE->id, 2, 'qualitativa', '2026-11-01');
        $criarEtapa($pfCultEsporteLIE->id, 1, 'financeira', '2025-12-10', 'enviada');
        $criarEtapa($pfCultEsporteLIE->id, 2, 'financeira', '2026-11-01');

        // =====================================================================
        // 7. PROJETO: E-DUCAR - FIA SALINAS
        // =====================================================================
        $pEducarFIAS = Projeto::create([
            'nome'                   => 'e-Ducar - FIA Salinas',
            'descricao'              => 'Projeto de robótica educacional com materiais recicláveis.',
            'data_inicio'            => '2025-04-28',
            'data_encerramento'      => '2025-12-31',
            'encerramento_contratos' => '2026-09-30',
            'status'                 => 'em_execucao',
        ]);
        $pEducarFIAS->polos()->attach([$poloBandinha->id, $poloNinheira->id, $poloSalinas->id]);

        $pfEducarFIAS = ProjetoFinanciador::create([
            'projeto_id' => $pEducarFIAS->id, 'financiador_id' => $fiFIAS->id,
            'data_inicio_contrato' => '2025-04-28', 'data_fim_contrato' => '2025-12-31',
            'data_prorrogacao' => '2025-11-30', 'prorrogado_ate' => '2026-06-30',
            'pc_interna_dia' => 10, 'pc_interna_dia_fin' => 27,
            'periodicidade' => 'trimestral',
        ]);
        $criarEtapa($pfEducarFIAS->id, 1, 'qualitativa', '2025-10-29', 'enviada');
        $criarEtapa($pfEducarFIAS->id, 2, 'qualitativa', '2026-02-09');
        $criarEtapa($pfEducarFIAS->id, 3, 'qualitativa', '2026-04-10');
        $criarEtapa($pfEducarFIAS->id, 1, 'financeira', '2025-10-29', 'enviada');
        $criarEtapa($pfEducarFIAS->id, 2, 'financeira', '2026-02-09');
        $criarEtapa($pfEducarFIAS->id, 3, 'financeira', '2026-04-10');

        // =====================================================================
        // 8. PROJETO: CULTURA DO SABER - FIA SALINAS
        // =====================================================================
        $pCultSaberFIAS = Projeto::create([
            'nome'                   => 'Cultura do Saber - FIA Salinas',
            'descricao'              => 'Oficinas culturais, esportivas e educacionais.',
            'data_inicio'            => '2025-03-14',
            'data_encerramento'      => '2025-12-31',
            'encerramento_contratos' => '2026-12-31',
            'status'                 => 'em_execucao',
        ]);
        $pCultSaberFIAS->polos()->attach([$poloBandinha->id, $poloNinheira->id, $poloSalinas->id]);

        $pfCultSaberFIAS = ProjetoFinanciador::create([
            'projeto_id' => $pCultSaberFIAS->id, 'financiador_id' => $fiFIAS->id,
            'data_inicio_contrato' => '2025-03-14', 'data_fim_contrato' => '2025-12-31',
            'data_prorrogacao' => '2025-11-30', 'prorrogado_ate' => '2027-01-31',
            'pc_interna_dia' => 10, 'pc_interna_dia_fin' => 27,
            'periodicidade' => 'trimestral',
        ]);
        $criarEtapa($pfCultSaberFIAS->id, 1, 'qualitativa', '2025-10-29', 'enviada');
        $criarEtapa($pfCultSaberFIAS->id, 2, 'qualitativa', '2026-02-09');
        $criarEtapa($pfCultSaberFIAS->id, 3, 'qualitativa', '2026-04-10');
        $criarEtapa($pfCultSaberFIAS->id, 1, 'financeira', '2025-10-29', 'enviada');
        $criarEtapa($pfCultSaberFIAS->id, 2, 'financeira', '2026-02-09');
        $criarEtapa($pfCultSaberFIAS->id, 3, 'financeira', '2026-04-10');

        // =====================================================================
        // 9. PROJETO: CULTURA DO SABER - FIA NINHEIRA
        // =====================================================================
        $pCultSaberFIAN = Projeto::create([
            'nome'                   => 'Cultura do Saber - FIA Ninheira',
            'descricao'              => 'Oficinas culturais, esportivas e educacionais no polo Ninheira.',
            'data_inicio'            => '2025-04-28',
            'data_encerramento'      => '2026-04-28',
            'encerramento_contratos' => '2026-12-31',
            'status'                 => 'em_execucao',
        ]);
        $pCultSaberFIAN->polos()->attach([$poloBandinha->id, $poloNinheira->id]);

        $pfCultSaberFIAN = ProjetoFinanciador::create([
            'projeto_id' => $pCultSaberFIAN->id, 'financiador_id' => $fiFIAN->id,
            'data_inicio_contrato' => '2025-04-28', 'data_fim_contrato' => '2026-04-28',
            'data_prorrogacao' => '2026-03-28',
            'pc_interna_dia' => 10, 'pc_interna_dia_fin' => 27,
            'periodicidade' => 'trimestral',
        ]);
        $criarEtapa($pfCultSaberFIAN->id, 1, 'qualitativa', '2025-09-10', 'enviada');
        $criarEtapa($pfCultSaberFIAN->id, 2, 'qualitativa', '2026-02-28');
        $criarEtapa($pfCultSaberFIAN->id, 3, 'qualitativa', '2026-04-10');
        $criarEtapa($pfCultSaberFIAN->id, 1, 'financeira', '2025-09-10', 'enviada');
        $criarEtapa($pfCultSaberFIAN->id, 2, 'financeira', '2026-02-28');
        $criarEtapa($pfCultSaberFIAN->id, 3, 'financeira', '2026-04-10');

        // =====================================================================
        // 10. PROJETO: CULTURA DO SABER - MINC
        // =====================================================================
        $pCultSaberMinC = Projeto::create([
            'nome'                   => 'Cultura do Saber - MinC',
            'descricao'              => 'Oficinas culturais financiadas pelo Ministério da Cultura.',
            'data_inicio'            => '2026-01-01',
            'data_encerramento'      => '2026-08-05',
            'encerramento_contratos' => '2026-06-30',
            'status'                 => 'em_execucao',
        ]);
        $pCultSaberMinC->polos()->attach([$poloBandinha->id, $poloNinheira->id]);

        $pfCultSaberMinC = ProjetoFinanciador::create([
            'projeto_id' => $pCultSaberMinC->id, 'financiador_id' => $fiMINC->id,
            'data_inicio_contrato' => '2026-01-01', 'data_fim_contrato' => '2026-08-05',
            'pc_interna_dia' => 10,
            'periodicidade' => 'semestral',
        ]);
        $criarEtapa($pfCultSaberMinC->id, 1, 'qualitativa', '2026-03-30');
        $criarEtapa($pfCultSaberMinC->id, 2, 'qualitativa', '2026-08-30');

        // =====================================================================
        // 11. PROJETO: CULTURA DO SABER - PNAB
        // =====================================================================
        $pCultSaberPNAB = Projeto::create([
            'nome'                   => 'Cultura do Saber - PNAB',
            'descricao'              => 'Política Nacional Aldir Blanc.',
            'data_inicio'            => '2026-01-01',
            'data_encerramento'      => '2026-12-31',
            'encerramento_contratos' => '2026-12-31',
            'status'                 => 'planejamento',
        ]);
        $pCultSaberPNAB->polos()->attach([$poloBandinha->id, $poloNinheira->id]);

        $pfCultSaberPNAB = ProjetoFinanciador::create([
            'projeto_id' => $pCultSaberPNAB->id, 'financiador_id' => $fiPNAB->id,
            'data_inicio_contrato' => '2026-01-01', 'data_fim_contrato' => '2026-12-31',
            'periodicidade' => null, // Cadastro manual de etapas
        ]);

        // =====================================================================
        // 12. PROJETO: CULTURA DO SABER - CAPEMISA
        // =====================================================================
        $pCultSaberCPMS = Projeto::create([
            'nome'                   => 'Cultura do Saber - CAPEMISA',
            'descricao'              => 'Oficinas culturais, esportivas e educacionais em Salinas.',
            'data_inicio'            => '2025-01-01',
            'data_encerramento'      => '2025-04-30',
            'status'                 => 'em_execucao',
        ]);
        $pCultSaberCPMS->polos()->attach([$poloSalinas->id]);

        $pfCultSaberCPMS = ProjetoFinanciador::create([
            'projeto_id' => $pCultSaberCPMS->id, 'financiador_id' => $fiCPMS->id,
            'data_inicio_contrato' => '2025-01-01', 'data_fim_contrato' => '2025-04-30',
            'periodicidade' => null,
        ]);

        // Metas Cultura do Saber CAPEMISA
        $csm1 = Meta::create(['projeto_id' => $pCultSaberCPMS->id, 'numero' => 1, 'descricao' => 'Atender 234 crianças e adolescentes nas atividades do projeto', 'indicador' => 'N de alunos matriculados', 'meio_verificacao' => 'Fichas de matrícula; sistema de cadastro dos alunos', 'status' => 'alcancada']);
        Tarefa::create(['meta_id' => $csm1->id, 'numero' => '1.1', 'descricao' => 'Verificar se a meta de alunos matriculados nas oficinas esportivas foi cumprida', 'responsavel' => 'Supervisor de Projetos e Coordenador Pedagógico', 'polo_id' => $poloSalinas->id, 'data_fim' => '2025-01-31', 'status' => 'concluido']);
        Tarefa::create(['meta_id' => $csm1->id, 'numero' => '1.2', 'descricao' => 'Verificar se a meta de alunos matriculados nas oficinas de cultura e educação foi cumprida', 'responsavel' => 'Supervisor de Projetos e Coordenador Pedagógico', 'polo_id' => $poloSalinas->id, 'data_fim' => '2025-01-31', 'status' => 'concluido']);

        $csm2 = Meta::create(['projeto_id' => $pCultSaberCPMS->id, 'numero' => 2, 'descricao' => 'Realizar 3 encontros de formação socioemocional para todos os beneficiários', 'indicador' => 'N de encontros realizados', 'meio_verificacao' => 'Relatório de Projeto', 'status' => 'alcancada']);
        for ($i = 1; $i <= 3; $i++) {
            Tarefa::create(['meta_id' => $csm2->id, 'numero' => "2." . (($i-1)*2+1), 'descricao' => 'Verificar se o encontro foi marcado pela equipe psicossocial', 'responsavel' => 'Supervisora de Projetos (Emille)', 'polo_id' => $poloSalinas->id, 'status' => 'concluido']);
            Tarefa::create(['meta_id' => $csm2->id, 'numero' => "2." . (($i-1)*2+2), 'descricao' => 'Fotografar os encontros', 'responsavel' => 'Fotógrafa', 'polo_id' => $poloSalinas->id, 'status' => 'concluido']);
        }

        $csm3 = Meta::create(['projeto_id' => $pCultSaberCPMS->id, 'numero' => 3, 'descricao' => 'Realizar 3 encontros na forma de roda de conversa ou reuniões com os familiares', 'indicador' => 'N de encontros realizados', 'meio_verificacao' => 'Relatório de Projeto', 'status' => 'alcancada']);
        foreach ([['02-10','02-13'],['03-10','03-22'],['04-10','04-17']] as $i => $d) {
            Tarefa::create(['meta_id' => $csm3->id, 'numero' => '3.' . ($i*2+1), 'descricao' => 'Verificar se o encontro foi marcado pela equipe psicossocial', 'responsavel' => 'Supervisora de Projetos (Emille)', 'polo_id' => $poloSalinas->id, 'data_fim' => "2025-{$d[0]}", 'status' => 'concluido']);
            Tarefa::create(['meta_id' => $csm3->id, 'numero' => '3.' . ($i*2+2), 'descricao' => 'Fotografar os encontros', 'responsavel' => 'Fotógrafa', 'polo_id' => $poloSalinas->id, 'data_fim' => "2025-{$d[1]}", 'status' => 'concluido']);
        }

        $csm4 = Meta::create(['projeto_id' => $pCultSaberCPMS->id, 'numero' => 4, 'descricao' => 'Promover o desenvolvimento integral dos beneficiários', 'indicador' => 'Evolução nos aspectos avaliados', 'meio_verificacao' => 'Relatórios com os resultados das avaliações dos beneficiários', 'status' => 'alcancada']);
        Tarefa::create(['meta_id' => $csm4->id, 'numero' => '4.1', 'descricao' => 'Aplicar formulário de avaliação final música', 'responsavel' => 'Educador de música (Anthony)', 'polo_id' => $poloSalinas->id, 'data_fim' => '2025-02-14', 'status' => 'concluido']);
        Tarefa::create(['meta_id' => $csm4->id, 'numero' => '4.2', 'descricao' => 'Elaborar relatório final com os resultados das avaliações', 'responsavel' => 'Diretora de Projetos (Ana)', 'polo_id' => null, 'data_fim' => '2025-02-28', 'status' => 'concluido']);

        $csm5 = Meta::create(['projeto_id' => $pCultSaberCPMS->id, 'numero' => 5, 'descricao' => 'Alcançar, em média, 70% na taxa de frequência dos alunos', 'indicador' => '% de presença', 'meio_verificacao' => 'Relatório com tabulação de frequência das turmas', 'status' => 'alcancada']);
        foreach (['02-28','03-31','04-30'] as $i => $d) {
            Tarefa::create(['meta_id' => $csm5->id, 'numero' => '5.' . ($i+1), 'descricao' => 'Verificar o alcance de 70% de frequência nas oficinas', 'responsavel' => 'Supervisora de Projetos e Coordenadora Pedagógica', 'polo_id' => $poloSalinas->id, 'data_fim' => "2025-{$d}", 'status' => 'concluido']);
        }
        Tarefa::create(['meta_id' => $csm5->id, 'numero' => '5.4', 'descricao' => 'Relatório Final de Tabulação de Frequência', 'responsavel' => 'Diretora de Projetos (Ana)', 'polo_id' => null, 'data_fim' => '2025-04-30', 'status' => 'a_iniciar']);

        $csm6 = Meta::create(['projeto_id' => $pCultSaberCPMS->id, 'numero' => 6, 'descricao' => 'Documentação das atividades do projeto', 'indicador' => 'N de oficinas que tiveram as atividades registradas', 'meio_verificacao' => 'Fotos e vídeos', 'status' => 'alcancada']);
        foreach (['01-31','02-28','03-31','04-30'] as $i => $d) {
            Tarefa::create(['meta_id' => $csm6->id, 'numero' => '6.' . ($i+1), 'descricao' => 'Verificar o registro das atividades do projeto incluindo oficinas, eventos e reuniões', 'responsavel' => 'Supervisora de Projetos (Emille)', 'polo_id' => $poloSalinas->id, 'data_fim' => "2025-{$d}", 'status' => 'concluido']);
        }

        $csm7 = Meta::create(['projeto_id' => $pCultSaberCPMS->id, 'numero' => 7, 'descricao' => 'Documentar as atividades realizadas', 'indicador' => 'N de relatórios elaborados', 'meio_verificacao' => 'Relatórios mensais de projetos', 'status' => 'em_andamento']);
        foreach (['01-31','02-24','03-24'] as $i => $d) {
            Tarefa::create(['meta_id' => $csm7->id, 'numero' => '7.' . ($i+1), 'descricao' => 'Relatório de atividades elaborado conforme modelo', 'responsavel' => 'Supervisora de Projetos (Emille)', 'polo_id' => $poloSalinas->id, 'data_fim' => "2025-{$d}", 'status' => 'concluido']);
        }
        Tarefa::create(['meta_id' => $csm7->id, 'numero' => '7.4', 'descricao' => 'Relatório final de atividades', 'responsavel' => 'Diretora de Projetos (Ana)', 'polo_id' => null, 'data_fim' => '2025-04-21', 'status' => 'nao_realizado']);

        // =====================================================================
        // 13. PROJETO: E-DUCAR - CRIANÇA ESPERANÇA
        // =====================================================================
        $pEducarCE = Projeto::create([
            'nome'                   => 'e-Ducar - Criança Esperança',
            'descricao'              => 'Projeto de robótica, língua portuguesa e formação socioemocional.',
            'data_inicio'            => '2025-02-01',
            'data_encerramento'      => '2026-03-31',
            'encerramento_contratos' => '2026-02-28',
            'status'                 => 'em_execucao',
        ]);
        $pEducarCE->polos()->attach([$poloBandinha->id, $poloNinheira->id]);

        $pfEducarCE = ProjetoFinanciador::create([
            'projeto_id' => $pEducarCE->id, 'financiador_id' => $fiCE->id,
            'data_inicio_contrato' => '2025-02-01', 'data_fim_contrato' => '2026-03-31',
            'periodicidade' => null,
        ]);

        // Metas e-Ducar CE (resumo das metas principais com tarefas)
        $cem1 = Meta::create(['projeto_id' => $pEducarCE->id, 'numero' => 1, 'descricao' => 'Apresentar projeto para os beneficiários e suas famílias', 'indicador' => 'N de participantes presentes', 'meio_verificacao' => 'Ata de reunião, lista de presença e registros fotográficos', 'status' => 'em_andamento']);
        Tarefa::create(['meta_id' => $cem1->id, 'numero' => '1.1', 'descricao' => 'Registrar reunião de apresentação do projeto', 'responsavel' => 'Coordenadora de Projeto (Ana)', 'polo_id' => null, 'data_inicio' => '2025-02-10', 'data_fim' => '2025-02-21', 'status' => 'concluido']);
        Tarefa::create(['meta_id' => $cem1->id, 'numero' => '1.2', 'descricao' => 'Verificar se o registro da atividade foi feito no polo Bandinha', 'responsavel' => 'Coordenadora de Projeto (Ana)', 'polo_id' => $poloBandinha->id, 'data_inicio' => '2025-02-10', 'data_fim' => '2025-02-21', 'status' => 'concluido']);
        Tarefa::create(['meta_id' => $cem1->id, 'numero' => '1.3', 'descricao' => 'Subir registros e atas no drive', 'responsavel' => 'Coordenadora de Projeto (Ana)', 'polo_id' => $poloBandinha->id, 'data_fim' => '2025-02-28', 'status' => 'em_andamento']);
        Tarefa::create(['meta_id' => $cem1->id, 'numero' => '1.4', 'descricao' => 'Verificar se o registro da atividade foi feito no polo Ninheira', 'responsavel' => 'Coordenadora de Projeto (Ana)', 'polo_id' => $poloNinheira->id, 'data_inicio' => '2025-02-10', 'data_fim' => '2025-02-21', 'status' => 'a_iniciar']);
        Tarefa::create(['meta_id' => $cem1->id, 'numero' => '1.5', 'descricao' => 'Subir registros e atas no drive', 'responsavel' => 'Coordenadora de Projeto (Ana)', 'polo_id' => $poloNinheira->id, 'data_fim' => '2025-02-28', 'status' => 'a_iniciar']);

        $cem2 = Meta::create(['projeto_id' => $pEducarCE->id, 'numero' => 2, 'descricao' => '72 alunos matriculados no projeto', 'indicador' => 'N de alunos matriculados confirmados no projeto', 'meio_verificacao' => 'Ficha de matrícula', 'status' => 'em_andamento']);
        Tarefa::create(['meta_id' => $cem2->id, 'numero' => '2.1', 'descricao' => 'Verificar se a meta de alunos matriculados na oficina de Robótica foi alcançada', 'responsavel' => 'Coordenadora de Projeto (Ana)', 'polo_id' => null, 'data_fim' => '2025-02-28', 'status' => 'em_andamento']);
        Tarefa::create(['meta_id' => $cem2->id, 'numero' => '2.2', 'descricao' => 'Verificar se a meta de alunos matriculados na oficina de Língua Portuguesa foi alcançada', 'responsavel' => 'Diretora de Projetos (Ana)', 'polo_id' => null, 'data_fim' => '2025-02-28', 'status' => 'em_andamento']);
        Tarefa::create(['meta_id' => $cem2->id, 'numero' => '2.3', 'descricao' => 'Verificar se a meta de alunos matriculados na oficina de Formação Socioemocional foi alcançada', 'responsavel' => 'Diretora de Projetos (Ana)', 'polo_id' => null, 'data_fim' => '2025-02-28', 'status' => 'em_andamento']);

        $cem3 = Meta::create(['projeto_id' => $pEducarCE->id, 'numero' => 3, 'descricao' => '70% dos alunos com melhora nas habilidades e competências desenvolvidas nas oficinas', 'indicador' => '% de melhora', 'meio_verificacao' => 'Formulário de Avaliação e relatório final com os resultados', 'status' => 'em_andamento']);
        $cem3Tarefas = [
            ['3.1', 'Elaborar avaliação de Robótica', 'Coordenadora, pedagoga e educador (Ana, Leda, Jonas e Cris)', 'Bandinha', '2025-02-17', '2025-02-28', 'concluido'],
            ['3.2', 'Elaborar avaliação de Língua Portuguesa', 'Coordenadora, pedagoga e educador (Ana, Leda e Viviane)', 'Bandinha', '2025-02-17', '2025-02-28', 'concluido'],
            ['3.3', 'Elaborar avaliação de Formação Socioemocional', 'Coordenadora, pedagoga e Psicóloga (Ana, Leda e Katiely)', 'Bandinha', '2025-02-17', '2025-02-28', 'concluido'],
            ['3.4', 'Aplicar formulário de avaliação inicial Robótica polo Bandinha', 'Educador (Jonas e Cris)', 'Bandinha', '2025-03-03', '2025-04-30', 'concluido'],
            ['3.5', 'Aplicar formulário de avaliação inicial Língua Portuguesa polo Bandinha', 'Educador (Viviane)', 'Bandinha', '2025-03-03', '2025-04-30', 'concluido'],
            ['3.6', 'Aplicar formulário de avaliação inicial Formação Socioemocional polo Bandinha', 'Psicólogo (Katiely)', 'Bandinha', '2025-03-03', '2025-04-30', 'concluido'],
            ['3.7', 'Aplicar formulário de avaliação final Robótica polo Bandinha', 'Educador (Jonas e Cris)', 'Bandinha', '2025-11-03', '2025-11-28', 'a_iniciar'],
            ['3.8', 'Aplicar formulário de avaliação final Língua Portuguesa polo Bandinha', 'Educador (Viviane)', 'Bandinha', '2025-11-03', '2025-11-28', 'a_iniciar'],
            ['3.9', 'Aplicar formulário de avaliação final Formação Socioemocional polo Bandinha', 'Psicólogo (Katiely)', 'Bandinha', '2025-11-03', '2025-11-28', 'a_iniciar'],
            ['3.10', 'Aplicar formulário de avaliação inicial Robótica polo Ninheira', 'Educador (Jonas e Cris)', 'Ninheira', '2025-03-03', '2025-04-30', 'a_iniciar'],
            ['3.11', 'Aplicar formulário de avaliação inicial Língua Portuguesa polo Ninheira', 'Educador (Viviane)', 'Ninheira', '2025-03-03', '2025-04-30', 'a_iniciar'],
            ['3.12', 'Aplicar formulário de avaliação inicial Formação Socioemocional polo Ninheira', 'Psicólogo (Katiely)', 'Ninheira', '2025-03-03', '2025-04-30', 'a_iniciar'],
            ['3.13', 'Aplicar formulário de avaliação final Robótica polo Ninheira', 'Educador (Jonas e Cris)', 'Ninheira', '2025-11-03', '2025-11-28', 'a_iniciar'],
            ['3.14', 'Aplicar formulário de avaliação final Língua Portuguesa polo Ninheira', 'Educador (Viviane)', 'Ninheira', '2025-11-03', '2025-11-28', 'a_iniciar'],
            ['3.15', 'Aplicar formulário de avaliação final Formação Socioemocional polo Ninheira', 'Psicólogo (Katiely)', 'Ninheira', '2025-11-03', '2025-11-28', 'a_iniciar'],
            ['3.16', 'Relatório final com os resultados das avaliações', 'Diretora de Projetos (Ana)', null, '2025-12-01', '2025-12-30', 'a_iniciar'],
        ];
        foreach ($cem3Tarefas as $t) {
            Tarefa::create(['meta_id' => $cem3->id, 'numero' => $t[0], 'descricao' => $t[1], 'responsavel' => $t[2], 'polo_id' => $t[3] ? $resolverPolo($t[3]) : null, 'data_inicio' => $t[4], 'data_fim' => $t[5], 'status' => $t[6]]);
        }

        $cem4 = Meta::create(['projeto_id' => $pEducarCE->id, 'numero' => 4, 'descricao' => '70% de frequência nas oficinas', 'indicador' => '% de presença', 'status' => 'alcancada']);
        // Frequência mensal Bandinha (mar-dez) e Ninheira (mar-dez)
        $mesesFreq = ['03-04','04-08','05-06','06-10','07-08','08-05','09-09','10-07','11-04','12-09'];
        foreach (['Bandinha','Ninheira'] as $polo) {
            foreach ($mesesFreq as $i => $d) {
                Tarefa::create(['meta_id' => $cem4->id, 'numero' => '4.' . ($i+1), 'descricao' => 'Verificar o alcance de 70% de frequência nas oficinas', 'responsavel' => 'Coordenadora de Projetos (Ana)', 'polo_id' => $resolverPolo($polo), 'data_fim' => "2025-{$d}", 'status' => 'concluido']);
            }
        }
        Tarefa::create(['meta_id' => $cem4->id, 'numero' => '4.21', 'descricao' => 'Elaborar relatório final de frequência', 'responsavel' => 'Coordenadora do Projeto (Ana)', 'polo_id' => null, 'data_inicio' => '2025-12-01', 'data_fim' => '2025-12-31', 'status' => 'a_iniciar']);

        $cem5 = Meta::create(['projeto_id' => $pEducarCE->id, 'numero' => 5, 'descricao' => 'Realizar um Festival Cultural de Encerramento para a comunidade', 'indicador' => 'N de participantes presentes', 'status' => 'a_iniciar']);
        Tarefa::create(['meta_id' => $cem5->id, 'numero' => '5.1', 'descricao' => 'Realizar feira de exposição com os materiais produzidos no projeto', 'responsavel' => 'Equipe do Projeto', 'polo_id' => $poloBandinha->id, 'data_fim' => '2025-09-25', 'status' => 'a_iniciar']);
        Tarefa::create(['meta_id' => $cem5->id, 'numero' => '5.2', 'descricao' => 'Fotografar todo o evento e os projetos', 'responsavel' => 'Fotógrafa', 'polo_id' => $poloBandinha->id, 'data_fim' => '2025-09-25', 'status' => 'a_iniciar']);
        Tarefa::create(['meta_id' => $cem5->id, 'numero' => '5.3', 'descricao' => 'Lista de presença de todos os presentes', 'responsavel' => 'Coordenadora de Polo', 'polo_id' => $poloBandinha->id, 'data_fim' => '2025-09-25', 'status' => 'a_iniciar']);
        Tarefa::create(['meta_id' => $cem5->id, 'numero' => '5.4', 'descricao' => 'Relatório final do evento', 'responsavel' => 'Diretora de Projetos (Ana)', 'polo_id' => null, 'data_inicio' => '2025-10-01', 'data_fim' => '2025-10-31', 'status' => 'a_iniciar']);

        // =====================================================================
        // 14. PROJETO: LATIMPACTO
        // =====================================================================
        $pLatimpacto = Projeto::create([
            'nome'                   => 'Latimpacto',
            'descricao'              => 'Projeto institucional com apoio internacional.',
            'data_inicio'            => '2025-04-29',
            'data_encerramento'      => '2026-10-29',
            'encerramento_contratos' => '2026-12-31',
            'status'                 => 'em_execucao',
        ]);

        $pfLatimpacto = ProjetoFinanciador::create([
            'projeto_id' => $pLatimpacto->id, 'financiador_id' => $fiLATI->id,
            'data_inicio_contrato' => '2025-04-29', 'data_fim_contrato' => '2026-10-29',
            'periodicidade' => null,
        ]);
        $criarEtapa($pfLatimpacto->id, 1, 'qualitativa', '2026-04-15');
        $criarEtapa($pfLatimpacto->id, 2, 'qualitativa', '2026-10-29');

        // =====================================================================
        // 15. PROJETO: SERTÃO VERDE / SONHO DE MARIA
        // =====================================================================
        $pSertaoVerde = Projeto::create([
            'nome'                   => 'Sertão Verde / Sonho de Maria',
            'descricao'              => 'Projeto de desenvolvimento sustentável.',
            'data_inicio'            => '2025-01-01',
            'data_encerramento'      => '2026-12-31',
            'encerramento_contratos' => '2026-12-31',
            'status'                 => 'em_execucao',
        ]);

        $pfSertaoVerde = ProjetoFinanciador::create([
            'projeto_id' => $pSertaoVerde->id, 'financiador_id' => $fiSV->id,
            'data_inicio_contrato' => '2025-01-01', 'data_fim_contrato' => '2026-12-31',
            'pc_interna_dia' => 6,
            'periodicidade' => null,
        ]);

        // =====================================================================
        // RESUMO FINAL
        // =====================================================================
        $this->command->info('');
        $this->command->info('=== SEED COMPLETO ===');
        $this->command->info('Polos: ' . Polo::count());
        $this->command->info('Financiadores: ' . Financiador::count());
        $this->command->info('Projetos: ' . Projeto::count());
        $this->command->info('Contratos (projeto_financiador): ' . ProjetoFinanciador::count());
        $this->command->info('Etapas de Prestação: ' . EtapaPrestacao::count());
        $this->command->info('Metas: ' . Meta::count());
        $this->command->info('Tarefas: ' . Tarefa::count());
        $this->command->info('Usuários: ' . User::count());
    }
}
