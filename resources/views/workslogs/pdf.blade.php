{{--
    View: workslogs/pdf.blade.php — Template para relatório mensal em PDF.

    Gera um relatório de horas trabalhadas por funcionário com:
     - Tabela de registros por funcionário
     - Totais de horas, horas extras e dias trabalhados
     - Resumo geral no final

    Gerado via DomPDF no WorkLogController::exportPdf().

    Tecnologias: Blade, DomPDF, CSS inline (necessário para PDF)
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Ponto — {{ $monthName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #4f46e5; }
        .header h1 { font-size: 22px; color: #4f46e5; margin-bottom: 4px; }
        .header p { color: #64748b; font-size: 12px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { font-size: 14px; font-weight: bold; color: #1e293b; margin-bottom: 8px; padding: 6px 10px; background: #f1f5f9; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background: #4f46e5; color: white; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) { background: #f8fafc; }
        .summary { background: #f1f5f9; padding: 8px 10px; border-radius: 4px; font-size: 11px; }
        .summary span { font-weight: bold; color: #4f46e5; }
        .footer { margin-top: 30px; text-align: center; color: #94a3b8; font-size: 9px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .overtime { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1>HoraSmart — Relatório de Ponto</h1>
        <p>Período: {{ $monthName }} | Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @foreach($byEmployee as $data)
    <div class="section">
        <div class="section-title">
            {{ $data['employee']->user->name }} — {{ $data['employee']->position }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Entrada</th>
                    <th>Saída Almoço</th>
                    <th>Retorno</th>
                    <th>Saída Final</th>
                    <th>Total</th>
                    <th>Extras</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['logs'] as $log)
                <tr>
                    <td>{{ $log->work_date->format('d/m/Y') }}</td>
                    <td>{{ $log->clock_in?->format('H:i') ?? '—' }}</td>
                    <td>{{ $log->lunch_out?->format('H:i') ?? '—' }}</td>
                    <td>{{ $log->lunch_in?->format('H:i') ?? '—' }}</td>
                    <td>{{ $log->clock_out?->format('H:i') ?? '—' }}</td>
                    <td><strong>{{ $log->formatted_hours }}</strong></td>
                    <td class="{{ $log->overtime_minutes > 0 ? 'overtime' : '' }}">{{ $log->formatted_overtime }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $totalH = intdiv($data['totalMinutes'], 60);
            $totalM = $data['totalMinutes'] % 60;
            $overH  = intdiv($data['overtimeTotal'], 60);
            $overM  = $data['overtimeTotal'] % 60;
        @endphp
        <div class="summary">
            Dias trabalhados: <span>{{ $data['daysWorked'] }}</span> |
            Total de horas: <span>{{ sprintf('%02d:%02d', $totalH, $totalM) }}</span> |
            Horas extras: <span class="{{ $data['overtimeTotal'] > 0 ? 'overtime' : '' }}">{{ sprintf('%02d:%02d', $overH, $overM) }}</span>
        </div>
    </div>
    @endforeach

    <div class="footer">
        HoraSmart © {{ date('Y') }} — Sistema de Controle de Jornada | Documento gerado automaticamente
    </div>

</body>
</html>
