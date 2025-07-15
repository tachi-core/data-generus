<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Kelompok; // Import Model Kelompok

use Filament\Resources\Resource;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Manajemen Data';
    protected static ?string $navigationLabel = 'Data Pengguna';

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
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Select::make('role')
                    ->options([
                        User::ROLE_ADMIN => 'Admin Daerah',
                        User::ROLE_KELOMPOK => 'Admin Kelompok',
                    ])
                    ->required()
                    ->default(User::ROLE_KELOMPOK) // Default role untuk admin kelompok
                    ->native(false), // Membuat dropdown lebih modern
                Select::make('kelompok_id')
                    ->relationship('kelompok', 'name') // Mengambil nama kelompok dari model Kelompok
                    ->label('Kelompok (Jika Admin Kelompok)')
                    ->nullable()
                    ->searchable()
                    ->preload(), // Memuat semua pilihan kelompok di awal
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge() // Menampilkan role sebagai badge
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_KELOMPOK => 'success',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kelompok.name') // Menampilkan nama kelompok
                    ->label('Kelompok')
                    ->placeholder('Admin Daerah') // Tampilkan ini jika tidak ada kelompok (admin)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        User::ROLE_ADMIN => 'Admin Daerah',
                        User::ROLE_KELOMPOK => 'Admin Kelompok',
                    ])
                    ->native(false),
                SelectFilter::make('kelompok_id')
                    ->relationship('kelompok', 'name')
                    ->label('Filter Kelompok')
                    ->native(false)
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
