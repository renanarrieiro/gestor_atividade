<?php

namespace App\Controller;

use App\Entity\Feriado;
use App\Entity\RegistroHora;
use App\Form\RegistroHoraType;
use App\Repository\ConfiguracaoHoraMensalRepository;
use App\Repository\FeriadoRepository;
use App\Repository\RegistroHoraRepository;
use App\Service\RelatorioHoraXlsxExporter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/relatorio-horas')]
class RelatorioHoraController extends AbstractController
{
    private const HORAS_PADRAO_DIA_UTIL = 8;

    #[Route('', name: 'app_relatorio_hora_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        RegistroHoraRepository $registroHoraRepository,
        FeriadoRepository $feriadoRepository,
        ConfiguracaoHoraMensalRepository $configuracaoHoraMensalRepository,
        EntityManagerInterface $em
    ): Response {
        $mes = $this->normalizarMes((int) $request->query->get('mes', (int) date('m')));
        $ano = $this->normalizarAno((int) $request->query->get('ano', (int) date('Y')));

        $registroHora = new RegistroHora();
        $form = $this->createForm(RegistroHoraType::class, $registroHora);
        $form->handleRequest($request);
        $solicitarSobrescrita = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $registroExistente = $registroHoraRepository->findOneBy([
                'data' => $registroHora->getData(),
            ]);
            $confirmarSobrescrita = (string) $request->request->get('confirmar_sobrescrever', '0') === '1';

            try {
                if ($registroExistente && !$confirmarSobrescrita) {
                    $solicitarSobrescrita = true;
                    $this->addFlash('error', 'Esta data ja possui registro. Confirme no pop-up para sobrescrever.');
                } elseif ($registroExistente) {
                    $registroExistente->setHorasTrabalhadas($registroHora->getHorasTrabalhadas());
                    $registroExistente->setComentarios($registroHora->getComentarios());
                    $em->flush();
                    $this->addFlash('success', 'Registro sobrescrito com sucesso.');

                    return $this->redirectToRoute('app_relatorio_hora_index', [
                        'mes' => (int) $registroExistente->getData()->format('m'),
                        'ano' => (int) $registroExistente->getData()->format('Y'),
                    ]);
                } else {
                    $em->persist($registroHora);
                    $em->flush();
                    $this->addFlash('success', 'Registro de horas salvo com sucesso.');

                    return $this->redirectToRoute('app_relatorio_hora_index', [
                        'mes' => (int) $registroHora->getData()->format('m'),
                        'ano' => (int) $registroHora->getData()->format('Y'),
                    ]);
                }
            } catch (UniqueConstraintViolationException) {
                $solicitarSobrescrita = true;
                $this->addFlash('error', 'Esta data ja possui registro. Confirme no pop-up para sobrescrever.');
            }
        }

        $registros = $registroHoraRepository->findByMesAno($mes, $ano);
        $totalHoras = $registroHoraRepository->somarHoras($registros);
        $feriadosPersonalizados = $feriadoRepository->findByMesAno($mes, $ano);
        $datasFeriadosPersonalizados = array_map(
            static fn (Feriado $feriado): string => $feriado->getData()->format('Y-m-d'),
            $feriadosPersonalizados
        );

        $taxaHora = $this->obterTaxaHora($mes, $ano, $configuracaoHoraMensalRepository);
        $diasUteis = $this->contarDiasUteisNoMes($ano, $mes, $datasFeriadosPersonalizados);
        $horasPrevistasMes = $diasUteis * self::HORAS_PADRAO_DIA_UTIL;
        $simulacaoFaturamentoTotal = $horasPrevistasMes * $taxaHora;
        $simulacaoFaturamentoParcial = $totalHoras * $taxaHora;

        return $this->render('relatorio_hora/index.html.twig', [
            'form' => $form,
            'registros' => $registros,
            'totalHoras' => $totalHoras,
            'mes' => $mes,
            'ano' => $ano,
            'taxaHora' => $taxaHora,
            'diasUteis' => $diasUteis,
            'horasPrevistasMes' => $horasPrevistasMes,
            'simulacaoFaturamentoTotal' => $simulacaoFaturamentoTotal,
            'simulacaoFaturamentoParcial' => $simulacaoFaturamentoParcial,
            'meses' => $this->meses(),
            'solicitarSobrescrita' => $solicitarSobrescrita,
        ]);
    }

    #[Route('/exportar', name: 'app_relatorio_hora_exportar', methods: ['GET'])]
    public function exportar(
        Request $request,
        RegistroHoraRepository $registroHoraRepository,
        RelatorioHoraXlsxExporter $exporter
    ): Response {
        $mes = $this->normalizarMes((int) $request->query->get('mes', (int) date('m')));
        $ano = $this->normalizarAno((int) $request->query->get('ano', (int) date('Y')));

        $registros = $registroHoraRepository->findByMesAno($mes, $ano);
        if ($registros === []) {
            $this->addFlash('error', 'Nao ha registros para exportar no periodo selecionado.');

            return $this->redirectToRoute('app_relatorio_hora_index', ['mes' => $mes, 'ano' => $ano]);
        }

        $totalHoras = $registroHoraRepository->somarHoras($registros);
        try {
            $conteudo = $exporter->gerarArquivo($registros, $totalHoras);
        } catch (\RuntimeException) {
            $this->addFlash('error', 'Nao foi possivel gerar o arquivo XLSX no momento.');

            return $this->redirectToRoute('app_relatorio_hora_index', ['mes' => $mes, 'ano' => $ano]);
        }
        $nomeArquivo = sprintf('relatorio-horas-%04d-%02d.xlsx', $ano, $mes);

        return new Response($conteudo, Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $nomeArquivo),
            'Cache-Control' => 'max-age=0, must-revalidate',
        ]);
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

    private function obterTaxaHora(int $mes, int $ano, ConfiguracaoHoraMensalRepository $repository): float
    {
        $configuracao = $repository->findOneByMesAno($mes, $ano);

        return $configuracao?->getTaxaHora() ?? 0.0;
    }

    /**
     * @param array<int, string> $datasFeriadosPersonalizados
     */
    private function contarDiasUteisNoMes(int $ano, int $mes, array $datasFeriadosPersonalizados = []): int
    {
        $inicio = new \DateTimeImmutable(sprintf('%04d-%02d-01', $ano, $mes));
        $fim = $inicio->modify('last day of this month');
        $feriados = array_flip(array_merge($this->feriadosNacionais($ano), $datasFeriadosPersonalizados));
        $diasUteis = 0;

        for ($data = $inicio; $data <= $fim; $data = $data->modify('+1 day')) {
            if ((int) $data->format('N') > 5) {
                continue;
            }

            if (isset($feriados[$data->format('Y-m-d')])) {
                continue;
            }

            ++$diasUteis;
        }

        return $diasUteis;
    }

    /**
     * @return array<int, string>
     */
    private function feriadosNacionais(int $ano): array
    {
        $pascoa = (new \DateTimeImmutable())->setTimestamp(easter_date($ano));
        $sextaSanta = $pascoa->modify('-2 day');
        $corpusChristi = $pascoa->modify('+60 day');

        return [
            sprintf('%04d-01-01', $ano),
            sprintf('%04d-04-21', $ano),
            sprintf('%04d-05-01', $ano),
            sprintf('%04d-09-07', $ano),
            sprintf('%04d-10-12', $ano),
            sprintf('%04d-11-02', $ano),
            sprintf('%04d-11-15', $ano),
            sprintf('%04d-11-20', $ano),
            sprintf('%04d-12-25', $ano),
            $sextaSanta->format('Y-m-d'),
            $corpusChristi->format('Y-m-d'),
        ];
    }
}