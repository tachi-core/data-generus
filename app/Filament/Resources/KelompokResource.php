<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelompokResource\Pages;
use App\Filament\Resources\KelompokResource\RelationManagers;
use App\Models\Kelompok;
use App\Models\User;

use Filament\Resources\Resource;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class KelompokResource extends Resource
{
    protected static ?string $model = Kelompok::class;

    protected static ?string $modelLabel = 'Kelompok';
    protected static ?string $pluralModelLabel = 'Data Kelompok Daerah Sampit';
    protected static ?string $navigationGroup = 'Manajemen Data'; 
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Data Kelompok';

    public static function canViewAny(): bool
    {
        // Hanya Admin Daerah yang bisa melihat menu ini
        return Auth::user()->hasRole(User::ROLE_ADMIN);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Kelompok')
                    ->required()
                    ->unique(ignoreRecord: true) // Pastikan nama desa unik, kecuali saat edit data yang sama
                    ->maxLength(255),
                TextInput::make('district')
                    ->label('Kecamatan')
                    ->maxLength(255),
                TextInput::make('city')
                    ->label('Kota')
                    ->maxLength(255),
                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3)
                    ->columnSpanFull(), // Menggunakan seluruh lebar kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelompok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('district')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('city')
                    ->options(fn () => \App\Models\Kelompok::distinct()->pluck('city', 'city')->toArray()),
                SelectFilter::make('district')
                    ->options(fn () => \App\Models\Kelompok::distinct()->pluck('district', 'district')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListKelompoks::route('/'),
            'create' => Pages\CreateKelompok::route('/create'),
            'edit' => Pages\EditKelompok::route('/{record}/edit'),
        ];
    }
}
