<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecapPresensiResource\Pages;
use App\Models\Attendance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class RecapPresensiResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-s-document-chart-bar';

    protected static ?string $navigationLabel = 'Recap Presensi';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'HRD']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form fields if needed
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_duration')
                    ->label('Durasi Kerja')
                    ->getStateUsing(fn(Attendance $record): string => $record->calculateWorkDuration()),
                Tables\Columns\TextColumn::make('user.roles.name')
                    ->label('Job Role')
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_datang')
                    ->label('Waktu Datang'),
                Tables\Columns\TextColumn::make('waktu_pulang')
                    ->label('Waktu Pulang'),
                Tables\Columns\TextColumn::make('attendance_status')
                    ->label('Status Kehadiran')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (!$record->waktu_datang) {
                            return 'Alfa';
                        }
                        if (!$record->waktu_pulang) {
                            return 'Tidak Checkout';
                        }
                        return 'Hadir';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Tidak Checkout' => 'warning',
                        'Alfa' => 'danger',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Karyawan')
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ]),
                        Forms\Components\Select::make('year')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = date('Y');
                                for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'],
                                fn(Builder $query, $month): Builder => $query->whereMonth('created_at', $month)
                            )
                            ->when(
                                $data['year'],
                                fn(Builder $query, $year): Builder => $query->whereYear('created_at', $year)
                            );
                    })
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->label('Export Excel')
                    ->exports([
                        ExcelExport::make()
                            ->withColumns([
                                Column::make('Tanggal')
                                    ->getStateUsing(fn($record) => $record->created_at->format('Y-m-d')),
                                Column::make('Nama')
                                    ->getStateUsing(fn($record) => $record->user->name),
                                Column::make('Durasi Kerja')
                                    ->getStateUsing(fn($record) => $record->calculateWorkDuration()),
                                Column::make('Job Role')
                                    ->getStateUsing(fn($record) => $record->user->roles->first() ? $record->user->roles->first()->name : '-'),
                                Column::make('Waktu Datang')
                                    ->getStateUsing(fn($record) => $record->waktu_datang),
                                Column::make('Waktu Pulang')
                                    ->getStateUsing(fn($record) => $record->waktu_pulang),
                                Column::make('Status Kehadiran')
                                    ->getStateUsing(function ($record) {
                                        if (!$record->waktu_datang) {
                                            return 'Alfa';
                                        }
                                        if (!$record->waktu_pulang) {
                                            return 'Tidak Checkout';
                                        }
                                        return 'Hadir';
                                    }),
                            ]),
                    ]),
                Tables\Actions\BulkAction::make('export_pdf')
                    ->label('Export PDF')
                    ->action(function ($records) {
                        $pdf = Pdf::loadView('pdf.attendance-recap', [
                            'records' => $records
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'attendance-recap.pdf');
                    })
                    ->deselectRecordsAfterCompletion()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecapPresensis::route('/'),
        ];
    }
}