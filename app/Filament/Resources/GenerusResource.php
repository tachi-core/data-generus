<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GenerusResource\Pages;
use App\Filament\Resources\GenerusResource\RelationManagers;
use App\Models\Generus;
use App\Models\User;

use Filament\Resources\Resource;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload; // Untuk upload foto
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section; // Untuk membagi form

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn; // Contoh jika ingin toggle
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn; // Untuk menampilkan foto
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login
use Illuminate\Database\Eloquent\Builder; // Untuk query builder di filter
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GenerusResource extends Resource
{
    protected static ?string $model = Generus::class;

    protected static ?string $modelLabel = 'Generus';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen Data';
    protected static ?string $navigationLabel = 'Data Generus';

    public static function getPluralModelLabel(): string
    {
        $user = Auth::user();

        // Jika pengguna adalah Admin Kota
        if ($user && $user->hasRole(User::ROLE_ADMIN)) {
            return 'Generus Daerah Sampit';
        }

        // Jika pengguna adalah Perwakilan Kelompok
        if ($user && $user->hasRole(User::ROLE_KELOMPOK) && $user->kelompok) {
            return 'Generus Kelompok ' . $user->kelompok->name;
        }

        return 'Data Generus';
    }


    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Section::make('Informasi Dasar Generus')
                    ->description('Data diri pokok generus.')
                    ->schema([
                        TextInput::make('full_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Lengkap'),
                        TextInput::make('nik')
                            ->label('NIK Generus')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule) {
                                return $rule->whereNotNull('nik');
                            })
                            ->mask('9999999999999999')
                            ->numeric()
                            ->length(16)
                            ->helperText('16 digit NIK. Kosongkan jika tidak ada.')
                            ->columnSpan(1),
                        TextInput::make('place_of_birth')
                            ->required()
                            ->label('Tempat Lahir')
                            ->maxLength(255)
                            ->columnSpan(1),
                        DatePicker::make('date_of_birth')
                            ->required()
                            ->label('Tanggal Lahir')
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->columnSpan(1),
                        Select::make('gender')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required()
                            ->label('Jenis Kelamin')
                            ->native(false)
                            ->columnSpan(1),
                        Select::make('blood_type')
                            ->options([
                                'Tidak Tahu' => 'Tidak Tahu',
                                'A' => 'A',
                                'B' => 'B',
                                'AB' => 'AB',
                                'O' => 'O',
                            ])
                            ->label('Golongan Darah')
                            ->native(false)
                            ->default('Tidak Tahu')
                            ->columnSpan(1),
                        FileUpload::make('photo_path')
                            ->label('Foto Generus')
                            ->image()
                            ->directory('child-photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->helperText('Ukuran maksimal 2MB (JPG, PNG).')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Informasi Orang Tua')
                    ->description('Data orang tua generus.')
                    ->schema([
                        TextInput::make('father_name')
                            ->label('Nama Ayah')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('mother_name')
                            ->label('Nama Ibu')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Informasi Domisili & Status')
                    ->description('Alamat dan status terkini generus.')
                    // Di sinilah kita akan menyisipkan logika kondisional untuk kolom Kelompok
                    ->schema(function () use ($user, $form): array { // Gunakan closure dan 'use' $user, $form
                        $fields = [];

                        // --- Logika Kondisional untuk Kolom Kelompok ---
                        if ($user && $user->role === 'admin') {
                            $fields[] = Select::make('kelompok_id')
                                ->relationship('kelompok', 'name')
                                ->label('Kelompok')
                                ->native(false)
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull(); // Ambil seluruh lebar di dalam section
                        } else {
                            // Jika bukan admin, tampilkan TextInput read-only untuk nama Kelompok
                            $fields[] = TextInput::make('kelompok_name') // Nama field bebas, ini hanya untuk display
                                ->label('Kelompok')
                                ->readOnly()
                                ->default(fn () => $user->kelompok->name ?? 'N/A')
                                ->dehydrated(false) // Penting: Jangan simpan field ini ke database
                                ->columnSpanFull();

                            // Untuk non-admin, tetap set kelompok_id secara tersembunyi
                            // Ini akan memastikan record generus terhubung ke kelompok user yang login
                            $fields[] = Hidden::make('kelompok_id')
                                ->default($user->kelompok_id)
                                ->dehydrated(true);
                        }
                        // --- Akhir Logika Kondisional ---

                        // Setelah logika kelompok, tambahkan field address dan lainnya
                        
                        $fields[] = Select::make('jenjang')
                            ->options([
                                'Bayi-Balita' => 'Bayi-Balita',
                                'Caberawit' => 'Caberawit',
                                'Pra-remaja' => 'Pra-remaja',
                                'Remaja' => 'Remaja',
                                'Usia Mandiri' => 'Usia Mandiri',
                                'Berkeluarga' => 'Berkeluarga',
                            ])
                            ->label('Jenjang Generus')
                            ->required()
                            ->default('Caberawit')
                            ->native(false);
                        // $fields[] = Select::make('status')
                        //     ->options([
                        //         'Belum menikah' => 'Belum menikah',
                        //         'Sudah menikah' => 'Sudah menikah',
                        //     ])
                        //     ->label('Status Generus')
                        //     ->required()
                        //     ->default('Belum menikah')
                        //     ->native(false);
                        $fields[] = TextInput::make('education_status')
                            ->label('Status Pendidikan')
                            ->maxLength(255);
                        $fields[] = TextInput::make('address')
                            ->label('Alamat Lengkap')
                            ->maxLength(255)
                            ->columnSpanFull();
                        $fields[] = Textarea::make('notes')
                            ->label('Keterangan Tambahan')
                            ->rows(3)
                            ->columnSpanFull();

                        return $fields; // Kembalikan array fields untuk section ini
                    })
                    ->columns(2), // 2 kolom untuk layout informasi domisili

                // Field untuk 'Diinput Oleh' (Created By) - Tetap di luar section utama ini
                Hidden::make('created_by_user_id')
                    ->default(fn () => $user->id)
                    ->dehydrated(fn ($state, $context) => $context === 'create'),

                // Field untuk 'Terakhir Diubah Oleh' (Updated By)
                Hidden::make('updated_by_user_id')
                    ->default(fn () => $user->id)
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path') // Menampilkan foto
                    ->label('Foto')
                    ->square() // Membuat gambar persegi
                    ->size(60), // Ukuran gambar
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('age') // Menggunakan accessor 'age'
                    ->label('Usia')     // Label kolom di tabel
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Mengurutkan berdasarkan tanggal lahir sebenarnya
                        return $query->orderBy('date_of_birth', $direction);
                    }),
                TextColumn::make('gender')
                    ->label('J. Kelamin')
                    ->sortable(),
                TextColumn::make('kelompok.name')
                    ->label('Kelompok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->badge() // Tampilkan sebagai badge
                    ->color(fn (string $state): string => match ($state) {
                        'Bayi-Balita' => 'gray',
                        'Caberawit' => 'gray',
                        'Pra-remaja' => 'info',
                        'Remaja' => 'info',
                        'Usia Mandiri' => 'primary',
                        'Berkeluarga' => 'success',
                        default => 'primary',
                    })
                    ->sortable(),
                TextColumn::make('blood_type')
                    ->label('Gol. Darah')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->label('Diinput Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('editor.name')
                    ->label('Terakhir Diubah Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Tgl. Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ])
                    ->native(false),
                SelectFilter::make('blood_type')
                    ->label('Golongan Darah')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'AB' => 'AB',
                        'O' => 'O',
                        'Tidak Tahu' => 'Tidak Tahu',
                    ])
                    ->native(false),
                SelectFilter::make('kelompok_id')
                    ->relationship('kelompok', 'name')
                    ->label('Filter Kelompok')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => auth()->user()->role === 'admin'),
                SelectFilter::make('status')
                    ->label('Status Generus')
                    ->options([
                        'Belum menikah' => 'Belum menikah',
                        'Sudah menikah' => 'Sudah menikah',
                    ])
                    ->native(false),
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
            'index' => Pages\ListGeneruses::route('/'),
            'create' => Pages\CreateGenerus::route('/create'),
            'edit' => Pages\EditGenerus::route('/{record}/edit'),
        ];
    }


    // Otomatis mengisi created_by_user_id dan updated_by_user_id
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($record) {
            $record->created_by_user_id = Auth::id();
        });

        static::updating(function ($record) {
            $record->updated_by_user_id = Auth::id();
        });
    }

    // Pembatasan akses berdasarkan role
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->hasRole(User::ROLE_KELOMPOK)) {
            $query->where('kelompok_id', Auth::user()->kelompok_id);
        }

        return $query;
    }
}
