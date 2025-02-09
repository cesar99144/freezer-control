<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Forms\Components\PtbrMoney;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Blog\Link;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Logística';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $activeNavigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $slug = 'produtos';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Produto')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('in_stock')
                            ->label('Estoque Atual')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->default(0),

                        PtbrMoney::make('cost_price')
                            ->label('Preço de Custo')
                            ->required(),

                        PtbrMoney::make('sale_price')
                            ->label('Preço de Venda')
                            ->required(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Foto do Produto')
                            ->image()
                            ->openable()
                            ->downloadable()
                            ->directory('images')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('sale_price'),
                ImageEntry::make('image'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(''),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome do Produto')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Preço de Custo')
                    ->formatStateUsing(fn (int $state): string => 'R$ ' . number_format($state, 2, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Preço de Venda')
                    ->formatStateUsing(fn (int $state): string => 'R$ ' . number_format($state, 2, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('in_stock')
                    ->label('Estoque Atual')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    DeleteAction::make()
                        ->action(fn (Product $record) => $record->delete())
                        ->requiresConfirmation()
                        ->modalHeading('Deletar '. $table->getModelLabel())
                        ->modalDescription('Tem certeza de que deseja excluir este '. $table->getModelLabel() .'? Isto não pode ser desfeito.')
                        ->modalSubmitActionLabel('Sim, deletar!'),
                ])->tooltip('Menu')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
