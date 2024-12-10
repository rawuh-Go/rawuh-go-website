<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Models\Assignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Assignments';

    public static function form(Form $form): Form
    {
        $isAdmin = auth()->user()->hasRole(['super_admin', 'HRD']);

        return $form
            ->schema([
                // Section untuk informasi tugas
                Forms\Components\Section::make('Informasi Tugas')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->required()
                            ->maxLength(255)
                            ->disabled(!$isAdmin),
                        Forms\Components\Select::make('jenis_project')
                            ->options([
                                Assignment::TYPE_TIM => 'Tim',
                                Assignment::TYPE_PERSONAL => 'Personal',
                            ])
                            ->required()
                            ->disabled(!$isAdmin),
                        Forms\Components\Textarea::make('deskripsi')
                            ->required()
                            ->maxLength(65535)
                            ->disabled(!$isAdmin),
                        Forms\Components\DatePicker::make('tanggal_deadline')
                            ->required()
                            ->disabled(!$isAdmin),
                        Forms\Components\Select::make('users')
                            ->multiple()
                            ->relationship('users', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->disabled(!$isAdmin),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),
                    ]),

                // Section untuk feedback jika ada dan status rejected
                Forms\Components\Section::make('Feedback Perbaikan')
                    ->schema([
                        Forms\Components\View::make('forms.components.feedback-display'),
                    ])
                    ->visible(
                        fn($record) =>
                        $record &&
                        !$isAdmin &&
                        $record->status === Assignment::STATUS_REJECTED &&
                        $record->feedback
                    )
                    ->collapsed(false),

                // Section untuk laporan karyawan
                Forms\Components\Section::make('Laporan Karyawan')
                    ->schema([
                        Forms\Components\ViewField::make('reports')
                            ->view('filament.forms.components.assignment-reports'),
                    ])
                    ->visible(fn($record) => $record && $isAdmin)
                    ->collapsible(),

                // Section untuk feedback (hanya untuk admin)
                Forms\Components\Section::make('Feedback')
                    ->schema([
                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback untuk karyawan')
                            ->visible(fn($record) => $record && $isAdmin),
                    ])
                    ->visible(fn($record) => $record && $isAdmin)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (!auth()->user()->hasRole(['super_admin', 'HRD'])) {
                    $query->whereHas('users', function ($q) {
                        $q->where('user_id', auth()->id());
                    });
                }
                $query->latest();
            })
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_project')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_deadline')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'done' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('submission')
                    ->label('View Submission')
                    ->formatStateUsing(function ($record) {
                        $submission = $record->users->first()?->pivot;
                        if ($submission?->link_laporan) {
                            return '🔗 View Link';
                        } elseif ($submission?->file_laporan) {
                            return '📎 View File';
                        }
                        return '-';
                    })
                    ->url(function ($record) {
                        $submission = $record->users->first()?->pivot;
                        if ($submission?->link_laporan) {
                            return $submission->link_laporan;
                        } elseif ($submission?->file_laporan) {
                            return Storage::url($submission->file_laporan);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()->hasRole(['super_admin', 'HRD']))
                    ->icon('heroicon-m-eye')
                    ->iconPosition('before')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Action submit/revisi laporan untuk karyawan
                Tables\Actions\Action::make('submit_report')
                    ->label(
                        fn(Assignment $record) =>
                        $record->status === Assignment::STATUS_REJECTED ? 'Submit Revisi' : 'Submit Laporan'
                    )
                    ->form([
                        Forms\Components\Textarea::make('laporan')
                            ->required()
                            ->label('Laporan Pengerjaan'),
                        Forms\Components\FileUpload::make('file_laporan')
                            ->directory(function (Assignment $record) {
                                $folderName = Str::slug($record->judul);
                                return "public/assignment/{$folderName}";
                            })
                            ->preserveFilenames()
                            ->label('File Pendukung (Opsional)'),
                        Forms\Components\TextInput::make('link_laporan')
                            ->url()
                            ->label('Link Laporan (Opsional)')
                    ])
                    ->action(function (Assignment $record, array $data) {
                        // Clear previous submission data
                        $oldSubmission = auth()->user()->assignments()->find($record->id)?->pivot;

                        if ($oldSubmission?->file_laporan && Storage::exists($oldSubmission->file_laporan)) {
                            Storage::delete($oldSubmission->file_laporan);
                        }

                        auth()->user()->assignments()->updateExistingPivot(
                            $record->id,
                            [
                                'laporan' => $data['laporan'],
                                'file_laporan' => $data['file_laporan'] ?? null,
                                'link_laporan' => $data['link_laporan'] ?? null,
                                'submitted_at' => now(),
                            ]
                        );

                        $record->update([
                            'status' => Assignment::STATUS_IN_PROGRESS,
                            'feedback' => null
                        ]);
                    })
                    ->visible(
                        fn(Assignment $record) =>
                        !auth()->user()->hasRole(['super_admin', 'HRD']) &&
                        in_array($record->status, [
                            Assignment::STATUS_PENDING,
                            Assignment::STATUS_IN_PROGRESS,
                            Assignment::STATUS_REJECTED
                        ])
                    ),

                // Action approve untuk admin/HRD
                Tables\Actions\Action::make('approve')
                    ->form([
                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback (Opsional)')
                            ->placeholder('Berikan feedback positif jika ada'),
                    ])
                    ->action(function (Assignment $record, array $data) {
                        $record->update([
                            'status' => Assignment::STATUS_DONE,
                            'feedback' => $data['feedback'] ?? null,
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(
                        fn(Assignment $record) =>
                        auth()->user()->hasRole(['super_admin', 'HRD']) &&
                        $record->status === Assignment::STATUS_IN_PROGRESS
                    ),

                // Action reject untuk admin/HRD
                Tables\Actions\Action::make('reject')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('feedback')
                            ->required()
                            ->label('Feedback Perbaikan')
                            ->helperText('Jelaskan apa yang perlu diperbaiki dari tugas ini'),
                    ])
                    ->action(
                        fn(Assignment $record, array $data) =>
                        $record->update([
                            'status' => Assignment::STATUS_REJECTED,
                            'feedback' => $data['feedback']
                        ])
                    )
                    ->requiresConfirmation()
                    ->visible(
                        fn(Assignment $record) =>
                        auth()->user()->hasRole(['super_admin', 'HRD']) &&
                        $record->status === Assignment::STATUS_IN_PROGRESS
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'view' => Pages\ViewAssignment::route('/{record}'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'HRD']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['super_admin', 'HRD']);
    }
}