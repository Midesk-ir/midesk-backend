<?php

namespace App\Services;

use App\Enums\ReportDataSource;
use App\Models\CustomReport;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ReportExportService
{
    public function dataSources(): array
    {
        return collect(ReportDataSource::cases())->map(fn ($s) => [
            'key' => $s->value,
            'columns' => $this->columnsFor($s),
        ])->values()->all();
    }

    public function preview(array $config): array
    {
        return $this->runQuery($config);
    }

    public function runForReport(CustomReport $report): array
    {
        return $this->runQuery([
            'data_source' => $report->data_source->value,
            'filters' => $report->filters ?? [],
            'columns' => $report->columns ?? [],
            'group_by' => $report->group_by,
        ]);
    }

    public function export(CustomReport $report, string $format): array
    {
        $data = $this->runForReport($report);
        $filename = 'report-'.$report->id.'-'.now()->format('YmdHis');

        if ($format === 'excel' && class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $path = storage_path("app/exports/{$filename}.xlsx");
            @mkdir(dirname($path), 0755, true);
            file_put_contents($path, $this->toCsv($data));

            return ['path' => $path, 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        }

        if ($format === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $path = storage_path("app/exports/{$filename}.pdf");
            @mkdir(dirname($path), 0755, true);
            file_put_contents($path, $this->toHtml($report, $data));

            return ['path' => $path, 'mime' => 'application/pdf'];
        }

        $ext = $format === 'pdf' ? 'html' : 'csv';
        $path = storage_path("app/exports/{$filename}.{$ext}");
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, $format === 'pdf' ? $this->toHtml($report, $data) : $this->toCsv($data));

        return ['path' => $path, 'mime' => $format === 'pdf' ? 'text/html' : 'text/csv'];
    }

    private function runQuery(array $config): array
    {
        $source = ReportDataSource::from($config['data_source']);

        return match ($source) {
            ReportDataSource::Tasks => Task::query()->limit(100)->get()->toArray(),
            ReportDataSource::Projects => Project::query()->limit(100)->get()->toArray(),
            default => [],
        };
    }

    private function columnsFor(ReportDataSource $source): array
    {
        return match ($source) {
            ReportDataSource::Tasks => ['title', 'status', 'priority', 'due_date'],
            ReportDataSource::Projects => ['name', 'key', 'status', 'start_date'],
            default => ['id', 'created_at'],
        };
    }

    private function toCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }
        $rows = [implode(',', array_keys($data[0]))];
        foreach ($data as $row) {
            $rows[] = implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row));
        }

        return implode("\n", $rows);
    }

    private function toHtml(CustomReport $report, array $data): string
    {
        $rows = collect($data)->map(fn ($row) => '<tr><td>'.implode('</td><td>', array_map('htmlspecialchars', array_values($row))).'</td></tr>')->implode('');

        return "<html><head><title>{$report->name}</title></head><body><h1>{$report->name}</h1><table border='1'><tbody>{$rows}</tbody></table></body></html>";
    }
}
