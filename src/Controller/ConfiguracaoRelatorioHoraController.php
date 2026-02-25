<?php

namespace App\Controller;

use App\Entity\ConfiguracaoHoraMensal;
use App\Entity\Feriado;
use App\Repository\ConfiguracaoHoraMensalRepository;
use App\Repository\FeriadoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/configuracoes/relatorio-horas')]
class ConfiguracaoRelatorioHoraController extends AbstractController
{
    #[Route('', name: 'app_configuracao_relatorio_hora_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ConfiguracaoHoraMensalRepository $configuracaoHoraMensalRepository,
        FeriadoRepository $feriadoRepository,
        EntityManagerInterface $em
    ): Response {
        $mes = $this->normalizarMes((int) $request->query->get('mes', (int) date('m')));
        $ano = $this->normalizarAno((int) $request->query->get('ano', (int) date('Y')));

        if ($request->isMethod('POST')) {
            $acao = (string) $request->request->get('acao', '');

            if ($acao === 'salvar_taxa') {
                $this->salvarTaxaPorHora($request, $mes, $ano, $configuracaoHoraMensalRepository, $em);
            } elseif ($acao === 'adicionar_feriado') {
                $this->adicionarFeriado($request, $mes, $ano, $em, $feriadoRepository);
            } elseif ($acao === 'remover_feriado') {
                $this->removerFeriado($request, $em, $feriadoRepository);
            }

            return $this->redirectToRoute('app_configuracao_relatorio_hora_index', [
                'mes' => $mes,
                'ano' => $ano,
            ]);
        }

        $configuracaoMensal = $configuracaoHoraMensalRepository->findOneByMesAno($mes, $ano);
        $feriadosDoMes = $feriadoRepository->findByMesAno($mes, $ano);
        $primeiroDiaMes = sprintf('%04d-%02d-01', $ano, $mes);
        $ultimoDiaMes = (new \DateTimeImmutable($primeiroDiaMes))->modify('last day of this month')->format('Y-m-d');

        return $this->render('configuracao_relatorio_hora/index.html.twig', [
            'mes' => $mes,
            'ano' => $ano,
            'meses' => $this->meses(),
            'taxaHora' => $configuracaoMensal?->getTaxaHora() ?? 0.0,
            'feriadosDoMes' => $feriadosDoMes,
            'primeiroDiaMes' => $primeiroDiaMes,
            'ultimoDiaMes' => $ultimoDiaMes,
        ]);
    }

    private function salvarTaxaPorHora(
        Request $request,
        int $mes,
        int $ano,
        ConfiguracaoHoraMensalRepository $configuracaoHoraMensalRepository,
        EntityManagerInterface $em
    ): void {
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('salvar_taxa_hora', $token)) {
            $this->addFlash('error', 'Token invalido para salvar taxa por hora.');

            return;
        }

        $taxaInformada = str_replace(',', '.', (string) $request->request->get('taxa_hora', '0'));
        if (!is_numeric($taxaInformada)) {
            $this->addFlash('error', 'Informe um valor numerico valido para a taxa por hora.');

            return;
        }

        $taxaHora = max(0.0, round((float) $taxaInformada, 2));
        $configuracao = $configuracaoHoraMensalRepository->findOneByMesAno($mes, $ano) ?? new ConfiguracaoHoraMensal();
        $configuracao
            ->setAno($ano)
            ->setMes($mes)
            ->setTaxaHora($taxaHora);

        $em->persist($configuracao);
        $em->flush();

        $this->addFlash('success', 'Taxa por hora salva com sucesso para o mes selecionado.');
    }

    private function adicionarFeriado(
        Request $request,
        int $mes,
        int $ano,
        EntityManagerInterface $em,
        FeriadoRepository $feriadoRepository
    ): void {
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('adicionar_feriado_config', $token)) {
            $this->addFlash('error', 'Token invalido para adicionar feriado.');

            return;
        }

        $dataInformada = (string) $request->request->get('feriado_data', '');
        $descricao = (string) $request->request->get('feriado_descricao', '');
        $data = \DateTimeImmutable::createFromFormat('Y-m-d', $dataInformada);

        if (!$data || $data->format('Y-m-d') !== $dataInformada) {
            $this->addFlash('error', 'Informe uma data valida para o feriado.');

            return;
        }

        if ((int) $data->format('m') !== $mes || (int) $data->format('Y') !== $ano) {
            $this->addFlash('error', 'A data do feriado deve estar no mesmo mês e ano selecionados.');

            return;
        }

        if ($feriadoRepository->findOneBy(['data' => $data]) !== null) {
            $this->addFlash('error', 'Ja existe um feriado configurado para esta data.');

            return;
        }

        $feriado = (new Feriado())
            ->setData($data)
            ->setDescricao($descricao);
        $em->persist($feriado);
        $em->flush();

        $this->addFlash('success', 'Feriado adicionado com sucesso.');
    }

    private function removerFeriado(
        Request $request,
        EntityManagerInterface $em,
        FeriadoRepository $feriadoRepository
    ): void {
        $id = (int) $request->request->get('feriado_id', 0);
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('remover_feriado_config_'.$id, $token)) {
            $this->addFlash('error', 'Token invalido para remover feriado.');

            return;
        }

        $feriado = $feriadoRepository->find($id);
        if ($feriado === null) {
            $this->addFlash('error', 'Feriado nao encontrado.');

            return;
        }

        $em->remove($feriado);
        $em->flush();

        $this->addFlash('success', 'Feriado removido com sucesso.');
    }

    /**
     * @return array<int, string>
     */
    private function meses(): array
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Marco',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
    }

    private function normalizarMes(int $mes): int
    {
        return max(1, min(12, $mes));
    }

    private function normalizarAno(int $ano): int
    {
        if ($ano < 2000 || $ano > 2100) {
            return (int) date('Y');
        }

        return $ano;
    }
}
