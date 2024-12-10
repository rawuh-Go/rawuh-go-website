<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Filament\Resources\LeaveResource\RelationManagers;
use App\Models\Leave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Auth;
use Illuminate\Support\Facades\Storage;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-s-briefcase';

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\Section::make('Detail')
                ->schema([
                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->required(),
                    Forms\Components\Select::make('type_leave')
                        ->options([
                            'Sick Leave' => 'Sick Leave',
                            'Personal Leave' => 'Personal Leave',
                            'Marriage Leave' => 'Marriage Leave',
                            'Annual Leave' => 'Annual Leave',
                        ])
                        ->default('Personal Leave')
                        ->required(),
                    Forms\Components\Textarea::make('reason')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('attachment')
                        ->label('Bukti/Attachment')
                        ->required()
                        ->disk('public')
                        ->directory('attachments')
                        ->acceptedFileTypes(['image/*', 'application/pdf']),
                    Forms\Components\Hidden::make('approved_by')
                        ->visible(fn() => Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('HRD')),
                    Forms\Components\Hidden::make('approved_by_role')
                        ->visible(fn() => Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('HRD')),

                ]),

        ];
        if (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('HRD')) {
            $schema[] = Forms\Components\Section::make('Permission')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'approve' => 'Approved',
                            'Rejected' => 'Rejected',
                        ])
                        ->afterStateUpdated(function ($state, $set) {
                            $user = Auth::user();
                            $role = $user->hasRole('super_admin') ? 'super_admin' : 'HRD';
                            $set('approved_by', $user->name);
                            $set('approved_by_role', $role);
                        }),
                    Forms\Components\Textarea::make('catatan')
                        ->columnSpanFull(),
                    Forms\Components\Hidden::make('approved_by'),
                    Forms\Components\Hidden::make('approved_by_role'),
                ]);
        }
        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $is_super_admin = Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('HRD');

                if (!$is_super_admin) {
                    $query->where('user_id', Auth::user()->id);
                }
            })
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_leave')
                    ->label('Type Leave')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attachment')
                    ->label('Attachment')
                    ->formatStateUsing(fn($state) => $state ? 'View Attachment' : '-')
                    ->url(fn($record) => $record->attachment ? Storage::url($record->attachment) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approve' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                    })
                    ->description(fn(Leave $record): string => $record->catatan ? $record->catatan : '-'),
                Tables\Columns\TextColumn::make('approved_by')
                    ->label('Approved By')
                    ->description(fn(Leave $record): string => $record->approved_by_role ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
