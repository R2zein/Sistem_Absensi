<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeResource\Pages;
use App\Filament\Resources\OfficeResource\RelationManagers;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Humaidem\FilamentMapPicker\Fields\OSMMap;


class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static ?string $navigationIcon = 'heroicon-s-building-office-2';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Office Management';


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('address_search')
                                ->label('Cari Alamat')
                                ->placeholder('Masukkan alamat yang ingin dicari...')
                                ->suffixAction(
                                    Forms\Components\Actions\Action::make('search_address')
                                        ->icon('heroicon-m-magnifying-glass')
                                        ->action(function (Forms\Get $get, Forms\Set $set) {
                                            $address = $get('address_search');
                                            if ($address) {
                                                // Geocoding menggunakan Nominatim (OpenStreetMap) dengan cURL
                                                $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address) . '&limit=1';
                                                
                                                $ch = curl_init();
                                                curl_setopt($ch, CURLOPT_URL, $url);
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($ch, CURLOPT_USERAGENT, 'SistemPresensi/1.0');
                                                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                
                                                $response = curl_exec($ch);
                                                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                                curl_close($ch);
                                                
                                                if ($httpCode === 200 && $response) {
                                                    $data = json_decode($response, true);
                                                    
                                                    if (!empty($data)) {
                                                        $location = $data[0];
                                                        $lat = $location['lat'];
                                                        $lon = $location['lon'];
                                                        
                                                        // Set latitude dan longitude
                                                        $set('latitude', $lat);
                                                        $set('longitude', $lon);
                                                        
                                                        // Set lokasi peta
                                                        $set('location', ['lat' => (float)$lat, 'lng' => (float)$lon]);
                                                        
                                                        // Clear search field
                                                        $set('address_search', '');
                                                        
                                                        return 'Lokasi ditemukan!';
                                                    } else {
                                                        return 'Alamat tidak ditemukan.';
                                                    }
                                                } else {
                                                    return 'Gagal menghubungi layanan geocoding. Silakan coba lagi.';
                                                }
                                            }
                                            return 'Masukkan alamat terlebih dahulu.';
                                        })
                                        ->color('primary')
                                )
                                ->columnSpanFull(),
                            OSMMap::make('location')
                                ->label('Location')
                                ->showMarker()
                                ->draggable()
                                ->extraControl([
                                    'zoomDelta'           => 1,
                                    'zoomSnap'            => 0.25,
                                    'wheelPxPerZoomLevel' => 60
                                ])
                                ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, ?Office $record) {
                                    if ($record && $record->latitude && $record->longitude) {
                                        $set('location', ['lat' => $record->latitude, 'lng' => $record->longitude]);
                                    }
                                })
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    if (isset($state['lat']) && isset($state['lng'])) {
                                        $set('latitude', $state['lat']);
                                        $set('longitude', $state['lng']);
                                    }
                                })
                                ->tilesUrl('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'),
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\TextInput::make('latitude')
                                        ->required()
                                        ->numeric()
                                        ->label('Latitude'),
                                    Forms\Components\TextInput::make('longitude')
                                        ->required()
                                        ->numeric()
                                        ->label('Longitude'),
                                ])->columns(2)
                        ])
                    
            ]),
            Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('radius')
                                    ->required()
                                    ->numeric()
                                    ->label('Radius (meter)')
                                    ->helperText('Radius area kantor dalam meter'),
                            ])
                    ])  
            
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
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
                Tables\Columns\TextColumn::make('radius')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}
